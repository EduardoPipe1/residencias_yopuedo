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

        if (class_exists(AuditoriaService::class)) {
            AuditoriaService::registrar(
                'beneficiarios',
                $beneficiario->id,
                'emision_reconocimiento_individual'
            );
        }

        $beneficiario->loadMissing('grupo.municipio');

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

        $beneficiarios = $query->orderBy('primer_apellido', 'asc')
                               ->orderBy('segundo_apellido', 'asc')
                               ->get();

        if ($beneficiarios->isEmpty()) {
            return back()->with('warning', 'No se encontraron registros acreditados con los filtros seleccionados.');
        }

        if (class_exists(AuditoriaService::class)) {
            foreach ($beneficiarios as $beneficiario) {
                AuditoriaService::registrar(
                    'beneficiarios',
                    $beneficiario->id,
                    'emision_reconocimiento_masivo'
                );
            }
        }

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