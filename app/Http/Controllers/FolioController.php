<?php

namespace App\Http\Controllers;

use App\Models\Beneficiario;
use App\Models\Grupo;
use App\Models\Promotor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FolioController extends Controller
{
    /**
     * Vista de catálogo con filtros para seleccionar solicitudes a imprimir.
     */
    public function index(Request $request)
    {
        $grupos = Grupo::with('municipio')->orderBy('identificador_grupo')->get();
        $promotores = Promotor::orderBy('nombre_completo')->get();

        return view('folios.index', compact('grupos', 'promotores'));
    }

    /**
     * Genera la solicitud de registro individual en formato PDF.
     */
    public function imprimirIndividual(Beneficiario $beneficiario)
    {
        // Carga las relaciones requeridas por la plantilla oficial
        $beneficiario->loadMissing('grupo.municipio', 'grupo.promotor');

        $pdf = Pdf::loadView('pdf.solicitud', compact('beneficiario'))
                  ->setPaper('letter', 'portrait');

        return $pdf->stream("Solicitud_{$beneficiario->folio}.pdf");
    }

    /**
     * Genera un PDF consolidado con las solicitudes filtradas por grupo o promotor.
     */
    public function imprimirMasivo(Request $request)
    {
        $query = Beneficiario::with('grupo.municipio', 'grupo.promotor');

        if ($request->filled('grupo_id')) {
            $query->where('grupo_id', $request->grupo_id);
        }

        if ($request->filled('promotor_id')) {
            $query->whereHas('grupo', function ($q) use ($request) {
                $q->where('promotor_id', $request->promotor_id);
            });
        }

        $beneficiarios = $query->orderBy('primer_apellido', 'asc')
                               ->orderBy('segundo_apellido', 'asc')
                               ->get();

        if ($beneficiarios->isEmpty()) {
            return back()->with('warning', 'No se encontraron registros para los filtros seleccionados.');
        }

        // Genera el documento con todas las solicitudes seleccionadas
        $pdf = Pdf::loadView('pdf.solicitud_masiva', compact('beneficiarios'))
                  ->setPaper('letter', 'portrait');

        return $pdf->stream('Solicitudes_Masivas_' . now()->format('Ymd_His') . '.pdf');
    }
}