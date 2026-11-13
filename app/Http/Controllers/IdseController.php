<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Beneficiario;

class IdseController extends Controller
{
    public function index(Request $request)
    {
        $query = Beneficiario::with('grupo');

        if ($request->filled('buscar')) {
            $termino = trim($request->buscar);
            $query->where(function($q) use ($termino) {
                $q->where('folio', 'like', "%{$termino}%")
                  ->orWhere('curp', 'like', "%{$termino}%");
            });
        }

        if ($request->filled('nombre')) {
            $nombre = trim($request->nombre);
            $query->where(function($q) use ($nombre) {
                $q->where('nombre', 'like', "%{$nombre}%")
                  ->orWhere('primer_apellido', 'like', "%{$nombre}%")
                  ->orWhere('segundo_apellido', 'like', "%{$nombre}%");
            });
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        $beneficiarios = $query->orderBy('id', 'desc')
                       ->paginate(15)
                       ->withQueryString();

        return view('idse.index', compact('beneficiarios'));
    }
}