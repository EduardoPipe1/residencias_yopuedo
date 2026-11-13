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
    /**
     * Muestra la vista de configuración y filtros para emitir reconocimientos.
     */
    public function index(Request $request)
    {
        $grupos = Grupo::with('municipio')->orderBy('identificador_grupo')->get();
        $promotores = Promotor::orderBy('nombre_completo')->get();
        $regiones = Region::orderBy('id')->get();

        return view('reconocimientos.index', compact('grupos', 'promotores', 'regiones'));
    }

    /**
     * Emite el reconocimiento individual en formato PDF y registra la auditoría.
     */
    public function individual(Beneficiario $beneficiario)
    {
        // Registrar evento en la bitácora inmutable
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

    /**
     * Emite reconocimientos de forma masiva en PDF aplicando filtros operativos.
     */
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
            // Por regla de negocio, los reconocimientos corresponden a los acreditados
            $query->where('estatus', 'Acreditado');
        }

        $beneficiarios = $query->orderBy('primer_apellido', 'asc')
                               ->orderBy('segundo_apellido', 'asc')
                               ->get();

        if ($beneficiarios->isEmpty()) {
            return back()->with('warning', 'No se encontraron registros acreditados con los filtros seleccionados.');
        }

        // Registrar auditoría para cada beneficiario emitido
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
}