<?php

namespace App\Http\Controllers;

use App\Models\Beneficiario;
use App\Models\Grupo;
use App\Models\Promotor;
use App\Models\Region;
use App\Services\AuditoriaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReconocimientoController extends Controller
{
    /** Máximo de diplomas en un solo PDF. */
    public const MAXIMO_POR_IMPRESION = 300;

    /** Filtros de la impresión en texto legible, para la bitácora. */
    public static function describirFiltros(Request $request): array
    {
        return [
            'Región'   => $request->filled('region_id') ? (Region::find($request->region_id)->nombre ?? $request->region_id) : 'Todas',
            'Promotor' => $request->filled('promotor_id') ? (Promotor::find($request->promotor_id)->nombre_completo ?? $request->promotor_id) : 'Todos',
            'Grupo'    => $request->filled('grupo_id') ? (Grupo::find($request->grupo_id)->identificador_grupo ?? $request->grupo_id) : 'Todos',
            'Estatus'  => $request->input('estatus') ?: 'Acreditado',
        ];
    }

    public function index(Request $request)
    {
        $grupos = Grupo::with('municipio')->orderBy('identificador_grupo')->get();
        $promotores = Promotor::orderBy('nombre_completo')->get();
        $regiones = Region::orderBy('id')->get();

        return view('reconocimientos.index', compact('grupos', 'promotores', 'regiones'));
    }

    public function individual($folio)
    {
        // En BD el folio se guarda sin el prefijo "IDSE" (el importador lo quita),
        // pero el usuario lo escribe como aparece en el padrón: IDSE202313633.
        $folioLimpio = trim(str_ireplace('IDSE', '', $folio));
        $beneficiario = Beneficiario::where('folio', $folioLimpio)->first();

        if (!$beneficiario) {
            return redirect()->route('reconocimientos.index')
                ->with('warning', "No se encontró ningún beneficiario con el folio {$folio}.");
        }

        $beneficiario->loadMissing('grupo.municipio');

        AuditoriaService::registrarImpresion(
            'emision_reconocimiento_individual',
            "Reconocimiento individual IDSE{$beneficiario->folio} - {$beneficiario->nombre} {$beneficiario->primer_apellido} {$beneficiario->segundo_apellido} (grupo " . ($beneficiario->grupo->identificador_grupo ?? '-') . ')',
            ['folio' => 'IDSE' . $beneficiario->folio],
            collect([$beneficiario])
        );

        $pdf = Pdf::loadView('pdf.reconocimiento', compact('beneficiario'))
                  ->setPaper('letter', 'landscape');

        return $pdf->stream("Reconocimiento_{$beneficiario->folio}.pdf");
    }

    public function masivo(Request $request)
    {
        $query = Beneficiario::with('grupo.promotor', 'grupo.municipio.region');

        if ($request->filled('grupo_id')) {
            $query->where('grupo_id', $request->grupo_id);
        }

        if ($request->filled('promotor_id')) {
            $query->whereHas('grupo', fn($q) => $q->where('promotor_id', $request->promotor_id));
        }

        if ($request->filled('region_id')) {
            $query->whereHas('grupo.municipio', fn($q) => $q->where('region_id', $request->region_id));
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        } else {
            $query->where('estatus', 'Acreditado');
        }

        // Límite por impresión: un PDF con miles de diplomas agota la memoria del servidor y
        // tampoco es práctico de mandar a la impresora. Se pide afinar el filtro.
        $total = (clone $query)->count();
        if ($total > self::MAXIMO_POR_IMPRESION) {
            return redirect()->route('reconocimientos.index')->with('warning', 'Tu selección tiene ' . number_format($total) . ' reconocimientos. '
                . 'Se pueden imprimir hasta ' . self::MAXIMO_POR_IMPRESION . ' a la vez: elige un promotor o un grupo para dividir la impresión.');
        }

        $beneficiarios = $query->orderBy('primer_apellido', 'asc')
                               ->orderBy('segundo_apellido', 'asc')
                               ->get();

        if ($beneficiarios->isEmpty()) {
            return redirect()->route('reconocimientos.index')->with('warning', 'No se encontraron registros acreditados con los filtros seleccionados.');
        }

        // Un solo ticket con los filtros usados y la lista exacta de diplomas impresos
        $filtros = self::describirFiltros($request);
        AuditoriaService::registrarImpresion(
            'emision_reconocimiento_masivo',
            "{$beneficiarios->count()} reconocimientos - " . implode(' | ', array_map(fn ($k, $v) => "{$k}: {$v}", array_keys($filtros), $filtros)),
            $filtros,
            $beneficiarios
        );

        $pdf = Pdf::loadView('pdf.reconocimiento_masivo', compact('beneficiarios'))
                  ->setPaper('letter', 'landscape');

        return $pdf->stream('Reconocimientos_Masivos_' . now()->format('Ymd_His') . '.pdf');
    }

    // ==============================================================================
    // ENDPOINTS API PARA LOS SELECTS DEPENDIENTES
    // ==============================================================================

    public function obtenerPromotoresPorRegion($regionId)
    {
        $regionId = (int) $regionId;

        // Un promotor pertenece a la región si así está registrado O si tiene
        // grupos en municipios de esa región (el importador puede dejar region_id
        // desfasado cuando un mismo promotor aparece en varias regiones).
        $promotores = Promotor::where('region_id', $regionId)
                              ->orWhereHas('grupos.municipio', fn($q) => $q->where('region_id', $regionId))
                              ->orderBy('nombre_completo', 'asc')
                              ->get(['id', 'nombre_completo']); 
                              
        return response()->json($promotores);
    }

    public function obtenerGruposPorRegion($regionId)
    {
        $grupos = Grupo::with('municipio')
            ->whereHas('municipio', function($q) use ($regionId) {
                $q->where('region_id', (int) $regionId);
            })
            ->orderBy('identificador_grupo')
            ->get();
        
        $gruposMapeados = $grupos->map(function ($grupo) {
            return [
                'id' => $grupo->id,
                'nombre_mostrar' => $grupo->identificador_grupo . ' - ' . ($grupo->municipio->nombre ?? 'Sin Municipio')
            ];
        });

        return response()->json($gruposMapeados);
    }

    public function obtenerGruposPorPromotor($promotorId)
    {
        $grupos = Grupo::with('municipio')
            ->where('promotor_id', (int) $promotorId)
            ->orderBy('identificador_grupo')
            ->get();
        
        $gruposMapeados = $grupos->map(function ($grupo) {
            return [
                'id' => $grupo->id,
                'nombre_mostrar' => $grupo->identificador_grupo . ' - ' . ($grupo->municipio->nombre ?? 'Sin Municipio')
            ];
        });

        return response()->json($gruposMapeados);
    }
}