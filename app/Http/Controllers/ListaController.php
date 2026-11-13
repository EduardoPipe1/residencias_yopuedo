<?php

namespace App\Http\Controllers;

use App\Models\Beneficiario;
use App\Models\Grupo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ListaController extends Controller
{
    /**
     * Muestra la vista de configuración y filtros para generar listas de asistencia.
     */
    public function index(Request $request)
    {
        $grupos = Grupo::with('municipio')->orderBy('identificador_grupo')->get();

        return view('listas.index', compact('grupos'));
    }

    /**
     * Genera la lista de asistencia en PDF filtrada dinámicamente con casillas de firma.
     */
    public function generar(Request $request)
    {
        $query = Beneficiario::with(['grupo.municipio', 'grupo.promotor']);

        if ($request->filled('curp')) {
            $query->where('curp', 'like', '%' . $request->curp . '%');
        }

        if ($request->filled('nombre')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->nombre . '%')
                  ->orWhere('primer_apellido', 'like', '%' . $request->nombre . '%')
                  ->orWhere('segundo_apellido', 'like', '%' . $request->nombre . '%');
            });
        }

        if ($request->filled('folio_inicio') && $request->filled('folio_fin')) {
            $query->whereBetween('folio', [$request->folio_inicio, $request->folio_fin]);
        }

        if ($request->filled('grupo_id')) {
            $query->where('grupo_id', $request->grupo_id);
        }

        if ($request->filled('promotor_id')) {
            $query->whereHas('grupo', function ($q) use ($request) {
                $q->where('promotor_id', $request->promotor_id);
            });
        }

        // Clasificación por estatus formativo
        if ($request->filled('clasificacion')) {
            if ($request->clasificacion === 'graduados') {
                $query->where(function ($q) {
                    $q->where('estatus', 'Acreditado')
                      ->orWhere('porc_avance', '>=', 100);
                });
            } elseif ($request->clasificacion === 'pendientes') {
                $query->where(function ($q) {
                    $q->where('estatus', '!=', 'Acreditado')
                      ->orWhere('porc_avance', '<', 100);
                });
            }
        }

        $beneficiarios = $query->orderBy('primer_apellido', 'asc')
                               ->orderBy('segundo_apellido', 'asc')
                               ->get();

        if ($beneficiarios->isEmpty()) {
            return back()->with('warning', 'No se encontraron beneficiarios con los criterios especificados.');
        }

        // Obtener el contexto del grupo seleccionado si aplica
        $grupo = $request->filled('grupo_id') 
            ? Grupo::with(['municipio', 'promotor'])->find($request->grupo_id)
            : $beneficiarios->first()->grupo;

        // Inyectar la colección filtrada al objeto grupo para el renderizado
        $grupo->setRelation('beneficiarios', $beneficiarios);

        $pdf = Pdf::loadView('pdf.lista_asistencia', compact('grupo'))
                  ->setPaper('letter', 'landscape');

        return $pdf->stream('Lista_Asistencia_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Descarga directa de la lista oficial de un grupo específico por URL.
     */
    public function generarPdfGrupo(Grupo $grupo)
    {
        $grupo->load(['municipio', 'promotor', 'beneficiarios' => function ($q) {
            $q->orderBy('primer_apellido', 'asc')
              ->orderBy('segundo_apellido', 'asc');
        }]);

        $pdf = Pdf::loadView('pdf.lista_asistencia', compact('grupo'))
                  ->setPaper('letter', 'landscape');

        return $pdf->stream("Lista_Asistencia_Grupo_{$grupo->identificador_grupo}.pdf");
    }
}