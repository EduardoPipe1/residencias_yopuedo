<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Beneficiario;
use App\Models\Grupo;
use App\Models\Promotor;
use App\Models\Municipio;
use App\Models\Region;
use App\Services\AuditoriaService;
use App\Services\CatalogoService;
use App\Services\ExcelACsvService;

class ImportacionController extends Controller
{
    private string $disco = 'local';
    private string $carpeta = 'importaciones';

    public function index()
    {
        $archivos = collect(Storage::disk($this->disco)->files($this->carpeta))
            ->map(fn ($ruta) => [
                'nombre' => basename($ruta),
                'tamano' => Storage::disk($this->disco)->size($ruta),
                'fecha'  => Storage::disk($this->disco)->lastModified($ruta),
            ])
            ->sortByDesc('fecha')
            ->values();

        return view('importaciones.index', compact('archivos'));
    }

    // importar el padron desde excel o csv
    public function store(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $request->validate([
            'archivo' => ['required', 'file', 'mimes:csv,txt,xlsx', 'extensions:csv,xlsx', 'max:102400'],
        ], [
            'archivo.required' => 'Selecciona el archivo del padrón para importar.',
            'archivo.mimes'      => 'El archivo debe ser Excel (.xlsx) o delimitado por comas (.csv).',
            'archivo.extensions' => 'El archivo debe ser Excel (.xlsx) o delimitado por comas (.csv).',
            'archivo.max'      => 'El tamaño máximo permitido es de 100 MB.',
        ]);

        $archivo = $request->file('archivo');
        $nombre = now()->format('Ymd_His') . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $archivo->getClientOriginalName());
        $ruta = $archivo->storeAs($this->carpeta, $nombre, $this->disco);
        $pathReal = Storage::disk($this->disco)->path($ruta);

        $hojaExcel = null;
        $csvTemporal = null;
        // si es excel se pasa a csv
        if (strtolower($archivo->getClientOriginalExtension()) === 'xlsx') {
            try {
                [$csvTemporal, $hojaExcel] = ExcelACsvService::convertir($pathReal);
            } catch (\Throwable $e) {
                return redirect()->route('importaciones.index')
                    ->withErrors(['archivo' => 'No se pudo leer el Excel: ' . $e->getMessage()]);
            }
            $pathReal = $csvTemporal;
        }

        $contadorRegistros = 0;
        $catalogo = new CatalogoService();
        $avisos = [];
        $gruposReasignados = [];

        $cacheRespaldo = [];
        $municipioRespaldo = function (?int $regionId) use (&$cacheRespaldo) {
            $clave = $regionId ?? 0;
            if (isset($cacheRespaldo[$clave])) return $cacheRespaldo[$clave];
            $m = $regionId ? Municipio::where('region_id', $regionId)->orderBy('id')->first() : null;
            return $cacheRespaldo[$clave] = $m
                ? ['id' => (int) $m->id, 'region_id' => (int) $m->region_id]
                : ['id' => 1, 'region_id' => (int) (Municipio::find(1)?->region_id ?? 1)];
        };

        if (($handle = fopen($pathReal, "r")) !== false) {
            $muestra   = (string) fgets($handle);
            $separador = substr_count($muestra, ';') > substr_count($muestra, ',') ? ';' : ',';
            $esUtf8    = mb_check_encoding(file_get_contents($pathReal, false, null, 0, 2_000_000), 'UTF-8');
            rewind($handle);

            $leerFila = function () use ($handle, $separador, $esUtf8) {
                $fila = fgetcsv($handle, 0, $separador);
                if ($fila === false) return false;
                return $esUtf8 ? $fila : array_map(fn ($v) => mb_convert_encoding((string) $v, 'UTF-8', 'Windows-1252'), $fila);
            };

            $cabecerasRaw = $leerFila();
            
            $cabeceras = array_map(function($h) {
                return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
            }, $cabecerasRaw ?: []);

            $col = array_flip($cabeceras);

            $esArchivoGrupos = isset($col['Coordinador_Promotor']) || isset($col['Gpo_Inicial']);

            if (!$esArchivoGrupos && !isset($col['P_FolioBeneficiario']) && !isset($col['Folio'])) {
                fclose($handle);
                if ($csvTemporal) @unlink($csvTemporal);
                return redirect()->route('importaciones.index')->withErrors([
                    'archivo' => 'El archivo no tiene el formato del padrón IDSE (no se encontró la columna P_FolioBeneficiario). Verifica que sea el archivo correcto.',
                ]);
            }

            if ($esArchivoGrupos) {
                while (($fila = $leerFila()) !== false) {
                    $gpoInicial = isset($col['Gpo_Inicial']) && isset($fila[$col['Gpo_Inicial']]) ? trim($fila[$col['Gpo_Inicial']]) : null;
                    if (empty($gpoInicial)) continue;

                    $nombrePromotor = isset($col['Coordinador_Promotor']) && isset($fila[$col['Coordinador_Promotor']]) ? trim($fila[$col['Coordinador_Promotor']]) : 'PROMOTOR GENERAL';
                    $nombreRegion   = isset($col['Region']) && isset($fila[$col['Region']]) ? trim($fila[$col['Region']]) : '';
                    $nombreMuni     = isset($col['Municipio']) && isset($fila[$col['Municipio']]) ? trim($fila[$col['Municipio']]) : '';
                    $lugarFecha     = isset($col['Lugar_Fecha_Evento']) && isset($fila[$col['Lugar_Fecha_Evento']]) ? trim($fila[$col['Lugar_Fecha_Evento']]) : null;

                    $regionId  = $catalogo->region($nombreRegion);
                    $municipio = $catalogo->municipio($nombreMuni, $regionId);
                    if (!$municipio) {
                        $avisos["Municipio no identificado: '{$nombreMuni}'"] = true;
                        $municipio = $municipioRespaldo($regionId);
                    }
                    $municipioId = $municipio['id'];
                    $regionId = $municipio['region_id'] ?? $regionId ?? 1;

                    $promotor = Promotor::firstOrCreate(
                        ['nombre_completo' => $nombrePromotor],
                        ['region_id' => $regionId, 'estatus' => 'Activo']
                    );

                    Grupo::updateOrCreate(
                        ['identificador_grupo' => $gpoInicial],
                        [
                            'promotor_id'        => $promotor->id,
                            'municipio_id'       => $municipioId,
                            'lugar_fecha_evento' => $lugarFecha,
                        ]
                    );
                    $contadorRegistros++;
                }
            } else {
                $cachePromotores = [];
                $cacheGrupos = [];
                $lote = [];
                $tamanoLote = 1000;
                // guarda los beneficiarios de 1000 en 1000
                $guardarLote = function () use (&$lote) {
                    if (!$lote) return;
                    DB::transaction(function () use ($lote) {
                        Beneficiario::upsert(array_values($lote), ['folio'], [
                            'curp', 'nombre', 'primer_apellido', 'segundo_apellido', 'grupo_id',
                            'tot_asistencias', 'porc_avance', 'estatus', 'anio_padron',
                            'fecha_termino', 'respuesta_aprendizajes_cambio', 'observaciones',
                        ]);
                    });
                    $lote = [];
                };

                while (($fila = $leerFila()) !== false) {
                    
                    $rawFolio = null;
                    if (isset($col['P_FolioBeneficiario']) && isset($fila[$col['P_FolioBeneficiario']])) {
                        $rawFolio = $fila[$col['P_FolioBeneficiario']];
                    } elseif (isset($col['Folio']) && isset($fila[$col['Folio']])) {
                        $rawFolio = $fila[$col['Folio']];
                    } elseif (isset($fila[2]) && !empty(trim($fila[2]))) {
                        $rawFolio = $fila[2];
                    }

                    $folioLimpio = trim(str_ireplace('IDSE', '', (string)$rawFolio));
                    
                    if (empty($folioLimpio)) {
                        continue;
                    }

                    $curp = null;
                    if (isset($col['P_CURP']) && isset($fila[$col['P_CURP']])) {
                        $curp = trim($fila[$col['P_CURP']]);
                    } elseif (isset($col['CURP']) && isset($fila[$col['CURP']])) {
                        $curp = trim($fila[$col['CURP']]);
                    }
                    if (empty($curp)) {
                        $curp = 'SP_' . $folioLimpio;
                    }

                    $nombre = isset($col['P_Nombre']) && isset($fila[$col['P_Nombre']]) 
                        ? trim($fila[$col['P_Nombre']]) 
                        : (isset($col['Nombre']) && isset($fila[$col['Nombre']]) ? trim($fila[$col['Nombre']]) : '');

                    $primerApellido = isset($col['P_PrimerApellido']) && isset($fila[$col['P_PrimerApellido']]) 
                        ? trim($fila[$col['P_PrimerApellido']]) 
                        : '';

                    $segundoApellido = isset($col['P_SegundoApellido']) && isset($fila[$col['P_SegundoApellido']]) && !empty(trim($fila[$col['P_SegundoApellido']])) 
                        ? trim($fila[$col['P_SegundoApellido']]) 
                        : null;

                    $grupoIdTexto = isset($col['G_IdentificadorGrupo']) && isset($fila[$col['G_IdentificadorGrupo']]) 
                        ? trim($fila[$col['G_IdentificadorGrupo']]) 
                        : (isset($col['Grupo']) && isset($fila[$col['Grupo']]) ? trim($fila[$col['Grupo']]) : '7171');

                    $muniTexto = isset($col['G_Municipio']) && isset($fila[$col['G_Municipio']]) 
                        ? trim($fila[$col['G_Municipio']]) 
                        : (isset($col['P_Municipio']) && isset($fila[$col['P_Municipio']]) ? trim($fila[$col['P_Municipio']]) : '');

                    $anioPadron = isset($col['AnioDePadron']) && isset($fila[$col['AnioDePadron']]) && is_numeric(trim($fila[$col['AnioDePadron']])) 
                        ? (int)trim($fila[$col['AnioDePadron']]) 
                        : 2026;

                    $respAprendizaje = isset($col['respuestaAprendizajesCambio']) && isset($fila[$col['respuestaAprendizajesCambio']]) 
                        ? trim($fila[$col['respuestaAprendizajesCambio']]) 
                        : null;

                    $observaciones = isset($col['Observaciones']) && isset($fila[$col['Observaciones']]) 
                        ? trim($fila[$col['Observaciones']]) 
                        : null;

                    $rawEstatus = isset($col['Estatus']) && isset($fila[$col['Estatus']]) ? trim($fila[$col['Estatus']]) : '';
                    $rawAvance  = isset($col['PorcAvance']) && isset($fila[$col['PorcAvance']]) ? str_replace('%', '', trim($fila[$col['PorcAvance']])) : '0';
                    $avanceNum  = (float)$rawAvance;

                    $esAcreditado = (
                        strcasecmp($rawEstatus, 'Acreditado') === 0 || 
                        strcasecmp($rawEstatus, 'GRADUADO') === 0 || 
                        $avanceNum >= 100
                    );

                    $estatusFinal    = $esAcreditado ? 'Acreditado' : 'No Acreditado';
                    $porcAvanceFinal = $esAcreditado ? 100.00 : 0.00;
                    $totAsistencias  = isset($col['TotAsistencias']) && isset($fila[$col['TotAsistencias']]) && is_numeric(trim($fila[$col['TotAsistencias']])) 
                        ? (int)trim($fila[$col['TotAsistencias']]) 
                        : ($esAcreditado ? 10 : 0);

                    $rawFecha = isset($col['FechaTermino']) && isset($fila[$col['FechaTermino']]) ? trim($fila[$col['FechaTermino']]) : '';
                    $fechaTermino = null;

                    if (!empty($rawFecha) && !str_contains($rawFecha, '#')) {
                        try {
                            if (str_contains($rawFecha, '/')) {
                                $partesFecha = explode(' ', $rawFecha)[0];
                                $fechaTermino = Carbon::createFromFormat('d/m/Y', $partesFecha)->format('Y-m-d');
                            } else {
                                $fechaTermino = Carbon::parse($rawFecha)->format('Y-m-d');
                            }
                        } catch (\Exception $e) {
                            $fechaTermino = null;
                        }
                    }

                    $rawZona  = isset($col['G_IdZona']) && isset($fila[$col['G_IdZona']]) ? trim($fila[$col['G_IdZona']]) : '';
                    $regionId = is_numeric($rawZona) && (int)$rawZona >= 1 && (int)$rawZona <= 7 ? (int)$rawZona : null;

                    $municipio = $catalogo->municipio($muniTexto, $regionId);
                    if (!$municipio) {
                        $avisos["Municipio no identificado: '{$muniTexto}'"] = true;
                        $municipio = $municipioRespaldo($regionId);
                    } elseif ($regionId && $municipio['region_id'] !== $regionId) {
                        $avisos["'{$muniTexto}' pertenece a la región {$municipio['region_id']} en el catálogo, pero el CSV dice zona {$regionId}"] = true;
                    }
                    $municipioId = $municipio['id'];
                    $regionId = $municipio['region_id'] ?? $regionId ?? 1;

                    $nombrePromotorReal = isset($col['G_NomPromotor']) && isset($fila[$col['G_NomPromotor']]) && !empty(trim($fila[$col['G_NomPromotor']])) 
                        ? trim($fila[$col['G_NomPromotor']]) 
                        : 'COORDINACIÓN REGIONAL ' . $regionId;

                    $promotorId = $cachePromotores[$nombrePromotorReal] ??= Promotor::firstOrCreate(
                        ['nombre_completo' => $nombrePromotorReal],
                        ['region_id' => $regionId, 'estatus' => 'Activo']
                    )->id;

                    if (!isset($cacheGrupos[$grupoIdTexto])) {
                        $grupo = Grupo::firstOrCreate(
                            ['identificador_grupo' => $grupoIdTexto],
                            ['promotor_id' => $promotorId, 'municipio_id' => $municipioId]
                        );
                        if ((int) $grupo->promotor_id !== (int) $promotorId) {
                            $gruposReasignados[] = "{$grupoIdTexto}: {$nombrePromotorReal}";
                            $grupo->update(['promotor_id' => $promotorId]);
                        }
                        $cacheGrupos[$grupoIdTexto] = $grupo->id;
                    }
                    $grupoId = $cacheGrupos[$grupoIdTexto];

                    $lote[$folioLimpio] = [
                            'folio'                        => $folioLimpio,
                            'curp'                         => $curp,
                            'nombre'                       => $nombre,
                            'primer_apellido'              => $primerApellido,
                            'segundo_apellido'             => $segundoApellido,
                            'grupo_id'                     => $grupoId,
                            'tot_asistencias'              => $totAsistencias,
                            'porc_avance'                  => $porcAvanceFinal,
                            'estatus'                      => $estatusFinal,
                            'anio_padron'                  => $anioPadron,
                            'fecha_termino'                => $fechaTermino,
                            'respuesta_aprendizajes_cambio' => $respAprendizaje,
                            'observaciones'                => $observaciones,
                    ];

                    $contadorRegistros++;
                    if (count($lote) >= $tamanoLote) $guardarLote();
                }
                $guardarLote();
            }
            fclose($handle);
        }
        if ($csvTemporal) {
            @unlink($csvTemporal);
        }

        // region de cada promotor segun sus grupos
        $promotoresCorregidos = CatalogoService::sincronizarRegionesPromotores();

        AuditoriaService::registrar('beneficiarios', null, 'importacion_padron', null, [
            'archivo'               => $archivo->getClientOriginalName(),
            'archivo_guardado'      => $nombre,
            'hoja_excel'            => $hojaExcel,
            'registros_procesados'  => $contadorRegistros,
            'promotores_corregidos' => $promotoresCorregidos,
            'grupos_reasignados'    => $gruposReasignados,
            'avisos'                => array_keys($avisos),
        ], "Importación del archivo {$archivo->getClientOriginalName()}: " . number_format($contadorRegistros) . ' registros procesados');

        $respuesta = redirect()
            ->route('importaciones.index')
            ->with('success', "Padrón migrado con éxito. Se procesaron {$contadorRegistros} registros aplicando reglas oficiales."
                . ($promotoresCorregidos ? " Se ajustó la región de {$promotoresCorregidos} promotor(es)." : '')
                . ($gruposReasignados ? ' ' . count($gruposReasignados) . ' grupo(s) cambiaron de promotor.' : ''));

        if ($avisos) {
            $lista = array_keys($avisos);
            $respuesta->with('warning', count($lista) . ' valor(es) no se identificaron en el catálogo y se asignaron a un municipio de respaldo: '
                . implode('; ', array_slice($lista, 0, 10)) . (count($lista) > 10 ? '…' : ''));
        }

        return $respuesta;
    }

    public function show(string $archivo)
    {
        $ruta = $this->carpeta . '/' . basename($archivo);
        abort_unless(Storage::disk($this->disco)->exists($ruta), 404);
        return Storage::disk($this->disco)->download($ruta);
    }
}