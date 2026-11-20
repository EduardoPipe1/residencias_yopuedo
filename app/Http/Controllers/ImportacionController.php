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

    public function store(Request $request)
    {
        // Sin límite de tiempo: el padrón crece en cada carga (+60 mil folios).
        // La velocidad se resuelve abajo con lotes y transacciones.
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $request->validate([
            'archivo' => ['required', 'file', 'mimes:csv,txt', 'max:102400'],
        ], [
            'archivo.required' => 'Selecciona un archivo CSV para importar.',
            'archivo.mimes'    => 'El archivo debe ser de tipo delimitado por comas (.csv).',
            'archivo.max'      => 'El tamaño máximo permitido es de 100 MB.',
        ]);

        $archivo = $request->file('archivo');
        $nombre = now()->format('Ymd_His') . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $archivo->getClientOriginalName());
        $ruta = $archivo->storeAs($this->carpeta, $nombre, $this->disco);
        $pathReal = Storage::disk($this->disco)->path($ruta);

        $contadorRegistros = 0;
        $catalogo = new CatalogoService();
        $avisos = [];   // municipios/regiones que no se pudieron identificar
        $gruposReasignados = [];   // grupos cuyo promotor cambió en este archivo

        // Si el municipio no se identifica, se usa el primero de la región (o el id 1) y se avisa.
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
            // El archivo se acepta tal como lo exporta Excel, sin editarlo:
            //  - Separador "," o ";" (Excel en español suele usar ";")
            //  - Codificación UTF-8 (con o sin BOM) o ANSI/Windows-1252 ("CSV delimitado por comas")
            $muestra   = (string) fgets($handle);
            $separador = substr_count($muestra, ';') > substr_count($muestra, ',') ? ';' : ',';
            $esUtf8    = mb_check_encoding(file_get_contents($pathReal, false, null, 0, 2_000_000), 'UTF-8');
            rewind($handle);

            $leerFila = function () use ($handle, $separador, $esUtf8) {
                $fila = fgetcsv($handle, 0, $separador);   // 0 = sin límite de longitud por línea
                if ($fila === false) return false;
                return $esUtf8 ? $fila : array_map(fn ($v) => mb_convert_encoding((string) $v, 'UTF-8', 'Windows-1252'), $fila);
            };

            // Lectura de la primera fila con encabezados
            $cabecerasRaw = $leerFila();
            
            // Limpieza de espacios, comillas y caracteres ocultos 
            $cabeceras = array_map(function($h) {
                return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
            }, $cabecerasRaw ?: []);

            $col = array_flip($cabeceras);

            // Detección del tipo de archivo
            $esArchivoGrupos = isset($col['Coordinador_Promotor']) || isset($col['Gpo_Inicial']);

            if ($esArchivoGrupos) {
                // FASE 1: Archivo de Grupos y Promotores
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
                    // La región del municipio es la fuente de verdad
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
                // FASE 2: Padron IDSE
                // Rendimiento: promotores y grupos se recuerdan en memoria (una consulta por
                // promotor/grupo distinto, no por fila) y los beneficiarios se guardan en lotes
                // de 1,000 con un solo INSERT ... ON DUPLICATE KEY UPDATE por lote, dentro de
                // una transacción. Si algo falla, sólo se pierde el lote en curso.
                $cachePromotores = [];   // nombre => id
                $cacheGrupos = [];       // identificador => id
                $lote = [];              // folio => datos del beneficiario
                $tamanoLote = 1000;
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
                    
                    // 1 Extraccion de Folio buscando por encabezado
                    $rawFolio = null;
                    if (isset($col['P_FolioBeneficiario']) && isset($fila[$col['P_FolioBeneficiario']])) {
                        $rawFolio = $fila[$col['P_FolioBeneficiario']];
                    } elseif (isset($col['Folio']) && isset($fila[$col['Folio']])) {
                        $rawFolio = $fila[$col['Folio']];
                    } elseif (isset($fila[2]) && !empty(trim($fila[2]))) {
                        $rawFolio = $fila[2]; // Columna C
                    }

                    $folioLimpio = trim(str_ireplace('IDSE', '', (string)$rawFolio));
                    
                    // Si no hay folio en esta fila, se omite (para evitar filas en blanco)
                    if (empty($folioLimpio)) {
                        continue;
                    }

                    // 2 CURP 
                    $curp = null;
                    if (isset($col['P_CURP']) && isset($fila[$col['P_CURP']])) {
                        $curp = trim($fila[$col['P_CURP']]);
                    } elseif (isset($col['CURP']) && isset($fila[$col['CURP']])) {
                        $curp = trim($fila[$col['CURP']]);
                    }
                    if (empty($curp)) {
                        $curp = 'SP_' . $folioLimpio;
                    }

                    // 3 Nombres y Apellidos
                    $nombre = isset($col['P_Nombre']) && isset($fila[$col['P_Nombre']]) 
                        ? trim($fila[$col['P_Nombre']]) 
                        : (isset($col['Nombre']) && isset($fila[$col['Nombre']]) ? trim($fila[$col['Nombre']]) : '');

                    $primerApellido = isset($col['P_PrimerApellido']) && isset($fila[$col['P_PrimerApellido']]) 
                        ? trim($fila[$col['P_PrimerApellido']]) 
                        : '';

                    $segundoApellido = isset($col['P_SegundoApellido']) && isset($fila[$col['P_SegundoApellido']]) && !empty(trim($fila[$col['P_SegundoApellido']])) 
                        ? trim($fila[$col['P_SegundoApellido']]) 
                        : null;

                    // 4 Grupo y Municipio
                    $grupoIdTexto = isset($col['G_IdentificadorGrupo']) && isset($fila[$col['G_IdentificadorGrupo']]) 
                        ? trim($fila[$col['G_IdentificadorGrupo']]) 
                        : (isset($col['Grupo']) && isset($fila[$col['Grupo']]) ? trim($fila[$col['Grupo']]) : '7171');

                    $muniTexto = isset($col['G_Municipio']) && isset($fila[$col['G_Municipio']]) 
                        ? trim($fila[$col['G_Municipio']]) 
                        : (isset($col['P_Municipio']) && isset($fila[$col['P_Municipio']]) ? trim($fila[$col['P_Municipio']]) : '');

                    // 5 Año y Textos complementarios
                    $anioPadron = isset($col['AnioDePadron']) && isset($fila[$col['AnioDePadron']]) && is_numeric(trim($fila[$col['AnioDePadron']])) 
                        ? (int)trim($fila[$col['AnioDePadron']]) 
                        : 2026;

                    $respAprendizaje = isset($col['respuestaAprendizajesCambio']) && isset($fila[$col['respuestaAprendizajesCambio']]) 
                        ? trim($fila[$col['respuestaAprendizajesCambio']]) 
                        : null;

                    $observaciones = isset($col['Observaciones']) && isset($fila[$col['Observaciones']]) 
                        ? trim($fila[$col['Observaciones']]) 
                        : null;

                    // 6 Acreditado / No Acreditado y 100% / 0%
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

                    // 7 Fecha de Término
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

                    // 8 Región oficial: la columna G_IdZona (1-7). Antes se tomaba el 5.º dígito
                    //   del folio, pero eso es el año (IDSE2023... => "3"), no la región.
                    $rawZona  = isset($col['G_IdZona']) && isset($fila[$col['G_IdZona']]) ? trim($fila[$col['G_IdZona']]) : '';
                    $regionId = is_numeric($rawZona) && (int)$rawZona >= 1 && (int)$rawZona <= 7 ? (int)$rawZona : null;

                    // 9 Municipio (se busca primero dentro de la región indicada)
                    $municipio = $catalogo->municipio($muniTexto, $regionId);
                    if (!$municipio) {
                        $avisos["Municipio no identificado: '{$muniTexto}'"] = true;
                        $municipio = $municipioRespaldo($regionId);
                    } elseif ($regionId && $municipio['region_id'] !== $regionId) {
                        $avisos["'{$muniTexto}' pertenece a la región {$municipio['region_id']} en el catálogo, pero el CSV dice zona {$regionId}"] = true;
                    }
                    $municipioId = $municipio['id'];
                    // La región del municipio manda; G_IdZona sólo si no hay municipio identificado
                    $regionId = $municipio['region_id'] ?? $regionId ?? 1;

                    // 10 Promotor Real
                    $nombrePromotorReal = isset($col['G_NomPromotor']) && isset($fila[$col['G_NomPromotor']]) && !empty(trim($fila[$col['G_NomPromotor']])) 
                        ? trim($fila[$col['G_NomPromotor']]) 
                        : 'COORDINACIÓN REGIONAL ' . $regionId;

                    $promotorId = $cachePromotores[$nombrePromotorReal] ??= Promotor::firstOrCreate(
                        ['nombre_completo' => $nombrePromotorReal],
                        ['region_id' => $regionId, 'estatus' => 'Activo']
                    )->id;

                    // 11 Grupo Formativo
                    //    La primera vez que aparece el grupo en el archivo se toma su promotor del CSV:
                    //    si cambió (p. ej. el promotor anterior dejó de laborar y se reasignó el grupo),
                    //    se actualiza. Las demás filas del mismo grupo usan la caché.
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

                    // 12 Beneficiario por folio (se acumula en el lote; si el folio se repite
                    //    en el archivo, gana la última fila, igual que antes)
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

        // Un promotor puede aparecer en varias filas/regiones: su región final es
        // la región donde tiene más grupos.
        $promotoresCorregidos = CatalogoService::sincronizarRegionesPromotores();

        AuditoriaService::registrar('beneficiarios', null, 'importacion_padron', null, [
            'archivo'               => $archivo->getClientOriginalName(),
            'archivo_guardado'      => $nombre,
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