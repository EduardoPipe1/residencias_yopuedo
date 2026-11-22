<?php

namespace App\Http\Controllers;

use App\Models\BitacoraAuditoria;
use App\Services\AuditoriaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    // buscador de la bitacora
    public function index(Request $request)
    {
        $query = BitacoraAuditoria::query()->orderByDesc('created_at')->orderByDesc('id');

        if ($request->filled('desde')) {
            $query->where('created_at', '>=', $request->date('desde')->startOfDay());
        }
        if ($request->filled('hasta')) {
            $query->where('created_at', '<=', $request->date('hasta')->endOfDay());
        }
        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }
        if ($request->filled('buscar')) {
            $termino = trim($request->buscar);
            $folio = str_ireplace('IDSE', '', $termino);
            $query->where(function ($q) use ($termino, $folio) {
                $q->where('ticket_id', 'like', "%{$termino}%")
                  ->orWhere('descripcion', 'like', "%{$termino}%")
                  ->orWhere('datos_nuevos', 'like', "%{$termino}%")
                  ->orWhere('datos_nuevos', 'like', "%{$folio}%");
            });
        }

        $movimientos = $query->paginate(20)->withQueryString();
        $acciones = AuditoriaService::ACCIONES;

        return view('auditoria.index', compact('movimientos', 'acciones'));
    }

    public function pdf(string $ticket)
    {
        $movimiento = BitacoraAuditoria::where('ticket_id', $ticket)->firstOrFail();

        return Pdf::loadView('pdf.ticket_auditoria', compact('movimiento'))
                  ->setPaper('letter', 'portrait')
                  ->stream("Comprobante_{$movimiento->ticket_id}.pdf");
    }
}
