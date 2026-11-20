<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Beneficiario;
use App\Models\Grupo;

class DocumentoController extends Controller
{
    // 1. Solicitud de Registro individual (Vertical)
    public function solicitud($id)
    {
        $beneficiario = Beneficiario::with('grupo.municipio', 'grupo.promotor')->findOrFail($id);

        $pdf = Pdf::loadView('pdf.solicitud', compact('beneficiario'))
                  ->setPaper('letter', 'portrait');

        return $pdf->stream("Solicitud_{$beneficiario->folio}.pdf");
    }

    // 2. Reconocimiento institucional (Horizontal / Landscape)
    public function reconocimiento($id)
    {
        $beneficiario = Beneficiario::with('grupo.municipio')->findOrFail($id);

        $pdf = Pdf::loadView('pdf.reconocimiento', compact('beneficiario'))
                  ->setPaper('letter', 'landscape');

        return $pdf->stream("Reconocimiento_{$beneficiario->folio}.pdf");
    }
}
