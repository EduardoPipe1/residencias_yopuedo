<?php

namespace App\Services;

use App\Models\Municipio;
use App\Models\Promotor;
use App\Models\Region;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogoService
{
    private array $regiones = [];
    private array $municipios = [];

    public function __construct()
    {
        foreach (Region::all(['id', 'nombre']) as $r) {
            $this->regiones[self::normalizar($r->nombre)] = (int) $r->id;
        }
        foreach (Municipio::all(['id', 'region_id', 'nombre']) as $m) {
            $this->municipios[] = ['id' => (int) $m->id, 'region_id' => (int) $m->region_id, 'nombre' => self::normalizar($m->nombre)];
        }
    }

    public static function normalizar(?string $texto): string
    {
        $texto = Str::upper(Str::ascii(trim((string) $texto)));
        return preg_replace('/\s+/', ' ', $texto);
    }

    public function region(?string $nombre): ?int
    {
        $n = self::normalizar($nombre);
        if ($n === '') return null;

        if (is_numeric($n)) {
            return in_array((int) $n, $this->regiones, true) ? (int) $n : null;
        }
        if (isset($this->regiones[$n])) return $this->regiones[$n];

        $hits = array_filter($this->regiones, fn ($id, $nom) => str_contains($nom, $n) || str_contains($n, $nom), ARRAY_FILTER_USE_BOTH);
        return count($hits) === 1 ? reset($hits) : null;
    }

    // busca el municipio sin importar acentos
    public function municipio(?string $nombre, ?int $regionId = null): ?array
    {
        $n = self::normalizar($nombre);
        if ($n === '') return null;

        $buscar = function (callable $match) use ($regionId) {
            $hits = array_values(array_filter($this->municipios, $match));
            if ($regionId) {
                $enRegion = array_values(array_filter($hits, fn ($m) => $m['region_id'] === $regionId));
                if ($enRegion) $hits = $enRegion;
            }
            return count($hits) === 1 ? $hits[0] : null;
        };

        $m = $buscar(fn ($m) => $m['nombre'] === $n)
          ?? $buscar(fn ($m) => str_contains($m['nombre'], $n) || str_contains($n, $m['nombre']));

        return $m ? ['id' => $m['id'], 'region_id' => $m['region_id']] : null;
    }

    // pone al promotor en la region donde tiene mas grupos
    public static function sincronizarRegionesPromotores(): int
    {
        $conteos = DB::table('grupos')
            ->join('municipios', 'municipios.id', '=', 'grupos.municipio_id')
            ->select('grupos.promotor_id', 'municipios.region_id', DB::raw('COUNT(*) as total'))
            ->groupBy('grupos.promotor_id', 'municipios.region_id')
            ->orderByDesc('total')
            ->get()
            ->groupBy('promotor_id');

        $corregidos = 0;
        foreach ($conteos as $promotorId => $filas) {
            $regionDominante = $filas->first()->region_id;
            $corregidos += Promotor::where('id', $promotorId)
                ->where('region_id', '!=', $regionDominante)
                ->update(['region_id' => $regionDominante]);
        }
        return $corregidos;
    }
}
