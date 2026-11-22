<?php

namespace App\Http\Controllers;

use App\Models\Beneficiario;
use App\Models\Grupo;
use App\Models\Region;
use App\Services\AuditoriaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ListaController extends Controller
{
    public const MAXIMO_POR_IMPRESION = 1500;

    public function index()
    {
        $regiones = Region::orderBy('id')->get();

        return view('listas.index', compact('regiones'));
    }

    // lista de reconocimientos, una hoja por grupo
    public function generar(Request $request)
    {
        if (!Region::whereKey($request->region_id)->exists()) {
            return redirect()->route('listas.index')->with('warning', 'Selecciona una región para generar las listas.');
        }

        $query = Grupo::with(['municipio', 'promotor'])
            ->whereHas('municipio', fn ($q) => $q->where('region_id', $request->region_id));

        if ($request->filled('promotor_id')) {
            $query->where('promotor_id', $request->promotor_id);
        }
        if ($request->filled('grupo_id')) {
            $query->where('id', $request->grupo_id);
        }

        $total = \App\Models\Beneficiario::whereIn('grupo_id', (clone $query)->select('id'))->count();
        if ($total > self::MAXIMO_POR_IMPRESION) {
            return redirect()->route('listas.index')->with('warning', 'Tu selección tiene ' . number_format($total) . ' beneficiarios. '
                . 'Se pueden imprimir hasta ' . number_format(self::MAXIMO_POR_IMPRESION) . ' a la vez: elige un promotor o un grupo para dividir la impresión.');
        }

        $grupos = $query->whereHas('beneficiarios')
            ->with(['beneficiarios' => fn ($q) => $q->orderBy('primer_apellido')->orderBy('segundo_apellido')->orderBy('nombre')])
            ->get()
            ->sortBy(fn ($g) => [(int) $g->identificador_grupo, $g->identificador_grupo])
            ->values();

        if ($grupos->isEmpty()) {
            return redirect()->route('listas.index')->with('warning', 'No se encontraron grupos con beneficiarios para los filtros seleccionados.');
        }

        $filtros = ReconocimientoController::describirFiltros($request);
        unset($filtros['Estatus']);
        $beneficiarios = $grupos->flatMap(fn ($g) => $g->beneficiarios->each(fn ($b) => $b->setRelation('grupo', $g)));
        AuditoriaService::registrarImpresion(
            'emision_lista_reconocimientos',
            "Lista de reconocimientos de {$grupos->count()} grupo(s): " . $grupos->pluck('identificador_grupo')->implode(', ')
                . " ({$beneficiarios->count()} beneficiarios)",
            $filtros,
            $beneficiarios
        );

        $pdf = Pdf::loadView('pdf.lista_reconocimientos', compact('grupos'))
                  ->setPaper('letter', 'portrait');

        $nombre = $grupos->count() === 1
            ? "Lista_Reconocimientos_Grupo_{$grupos->first()->identificador_grupo}.pdf"
            : 'Listas_Reconocimientos_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->stream($nombre);
    }
}
