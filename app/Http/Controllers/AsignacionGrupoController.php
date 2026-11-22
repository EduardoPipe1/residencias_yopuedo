<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Promotor;
use App\Models\Region;
use App\Services\AuditoriaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AsignacionGrupoController extends Controller
{
    public function index(Request $request)
    {
        $regiones = Region::orderBy('id')->get();
        $promotores = $request->filled('region_id') ? $this->promotoresDeRegion((int) $request->region_id) : collect();
        $asignaciones = $request->filled('region_id') ? $this->asignaciones($request) : collect();

        return view('asignaciones.index', compact('regiones', 'promotores', 'asignaciones'));
    }

    public function pdf(Request $request)
    {
        if (!Region::whereKey($request->region_id)->exists()) {
            return redirect()->route('asignaciones.index');
        }

        $asignaciones = $this->asignaciones($request);
        $region = Region::find($request->region_id);

        $filtros = ReconocimientoController::describirFiltros($request);
        AuditoriaService::registrar('grupos', null, 'emision_asignacion_grupos', null, [
            'filtros' => ['Región' => $filtros['Región'], 'Promotor' => $filtros['Promotor']],
            'total'   => $asignaciones->count(),
            'rangos'  => $asignaciones->map(fn ($f) => ['promotor' => $f['promotor'], 'gpo_inicial' => $f['gpo_inicial'], 'gpo_final' => $f['gpo_final']])->values()->all(),
        ], "Asignación de grupos - {$filtros['Región']} - Promotor: {$filtros['Promotor']}");

        return Pdf::loadView('pdf.asignacion_grupos', compact('asignaciones', 'region'))
                  ->setPaper('letter', 'portrait')
                  ->stream('Asignacion_Grupos_' . now()->format('Ymd_His') . '.pdf');
    }

    private function promotoresDeRegion(int $regionId): Collection
    {
        return Promotor::where('region_id', $regionId)
            ->orWhereHas('grupos.municipio', fn ($q) => $q->where('region_id', $regionId))
            ->orderBy('nombre_completo')
            ->get(['id', 'nombre_completo']);
    }

    // junta grupos seguidos del mismo promotor
    private function asignaciones(Request $request): Collection
    {
        $grupos = Grupo::with('promotor:id,nombre_completo')
            ->whereHas('municipio', fn ($q) => $q->where('region_id', $request->region_id))
            ->get(['id', 'identificador_grupo', 'promotor_id'])
            ->sortBy(fn ($g) => [ctype_digit($g->identificador_grupo) ? (int) $g->identificador_grupo : PHP_INT_MAX, $g->identificador_grupo])
            ->values();

        $filas = [];
        $actual = null;

        foreach ($grupos as $g) {
            $numero = ctype_digit($g->identificador_grupo) ? (int) $g->identificador_grupo : null;

            $continua = $actual
                && $numero !== null
                && $actual['promotor_id'] === $g->promotor_id
                && $actual['ultimo'] !== null
                && $numero === $actual['ultimo'] + 1;

            if ($continua) {
                $actual['gpo_final'] = $g->identificador_grupo;
                $actual['ultimo'] = $numero;
                $actual['total']++;
                continue;
            }

            if ($actual) $filas[] = $actual;
            $actual = [
                'promotor_id' => $g->promotor_id,
                'promotor'    => $g->promotor->nombre_completo ?? 'SIN PROMOTOR',
                'gpo_inicial' => $g->identificador_grupo,
                'gpo_final'   => $g->identificador_grupo,
                'ultimo'      => $numero,
                'total'       => 1,
            ];
        }
        if ($actual) $filas[] = $actual;

        $filas = collect($filas);

        if ($request->filled('promotor_id')) {
            $filas = $filas->where('promotor_id', (int) $request->promotor_id)->values();
        }

        return $filas;
    }
}
