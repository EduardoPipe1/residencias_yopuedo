<?php

namespace App\Http\Controllers;

use App\Models\Beneficiario;
use App\Models\Grupo;
use App\Models\Region;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/**
 * Módulo LISTAS: "Lista de reconocimientos" oficial (formato LISTA (1).pdf).
 * Acta de entrega por grupo con todos sus beneficiarios (graduados y bajas) y
 * casillas para firma y fecha de entrega del reconocimiento.
 */
class ListaController extends Controller
{
    public function index()
    {
        $regiones = Region::orderBy('id')->get();

        return view('listas.index', compact('regiones'));
    }

    /**
     * Genera el PDF filtrando por región -> promotor -> grupo.
     * Cada grupo se imprime en su propia hoja (o varias, si no cabe en una).
     */
    public function generar(Request $request)
    {
        $request->validate(
            ['region_id' => 'required|exists:regiones,id'],
            ['region_id.required' => 'Selecciona una región para generar las listas.']
        );

        $query = Grupo::with(['municipio', 'promotor'])
            ->whereHas('municipio', fn ($q) => $q->where('region_id', $request->region_id));

        if ($request->filled('promotor_id')) {
            $query->where('promotor_id', $request->promotor_id);
        }
        if ($request->filled('grupo_id')) {
            $query->where('id', $request->grupo_id);
        }

        $grupos = $query->whereHas('beneficiarios')
            ->with(['beneficiarios' => fn ($q) => $q->orderBy('primer_apellido')->orderBy('segundo_apellido')->orderBy('nombre')])
            ->get()
            ->sortBy(fn ($g) => [(int) $g->identificador_grupo, $g->identificador_grupo])
            ->values();

        if ($grupos->isEmpty()) {
            return back()->with('warning', 'No se encontraron grupos con beneficiarios para los filtros seleccionados.');
        }

        $pdf = Pdf::loadView('pdf.lista_reconocimientos', compact('grupos'))
                  ->setPaper('letter', 'portrait');

        $nombre = $grupos->count() === 1
            ? "Lista_Reconocimientos_Grupo_{$grupos->first()->identificador_grupo}.pdf"
            : 'Listas_Reconocimientos_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->stream($nombre);
    }
}
