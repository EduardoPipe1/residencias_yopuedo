<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Asistencia - Grupo {{ $grupo->identificador_grupo }}</title>
    <style>
        @page { margin: 1.5cm 1cm; size: letter landscape; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 8.5pt; color: #1a202c; }
        .header { width: 100%; border-bottom: 2px solid #003366; padding-bottom: 5px; margin-bottom: 10px; }
        .header td { vertical-align: middle; }
        .title { font-size: 12pt; font-weight: bold; color: #003366; margin: 0; }
        .subtitle { font-size: 9pt; color: #4a5568; margin: 2px 0 0 0; }
        .meta-table { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
        .meta-table td { padding: 3px 6px; font-size: 8pt; border: 1px solid #e2e8f0; }
        .meta-table .label { background-color: #edf2f7; font-weight: bold; width: 12%; }
        table.asistencia { width: 100%; border-collapse: collapse; margin-top: 5px; }
        table.asistencia th { background-color: #003366; color: #ffffff; padding: 4px 3px; font-size: 8pt; border: 1px solid #002244; text-transform: uppercase; }
        table.asistencia td { padding: 4px 3px; border: 1px solid #cbd5e0; font-size: 7.5pt; vertical-align: middle; }
        .text-center { text-align: center; }
        .firma-box { height: 24px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <div class="title">Secretaría del Nuevo Comienzo — Lista Oficial de Asistencia y Acreditación</div>
                <div class="subtitle">Programa Social: "Yo Puedo, Guanajuato Puede"</div>
            </td>
            <td style="text-align: right; font-weight: bold; color: #003366;">
                Total Convocados: {{ $grupo->beneficiarios->count() }}
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="label">Grupo:</td>
            <td><strong>{{ $grupo->identificador_grupo }}</strong></td>
            <td class="label">Municipio:</td>
            <td>{{ $grupo->municipio->nombre ?? 'N/A' }}</td>
            <td class="label">Promotor:</td>
            <td>{{ $grupo->promotor->nombre_completo ?? 'Sin Promotor' }}</td>
            <td class="label">Sede / Evento:</td>
            <td>{{ $grupo->lugar_fecha_evento ?? 'Pendiente' }}</td>
        </tr>
    </table>

    <table class="asistencia">
        <thead>
            <tr>
                <th style="width: 3%;">No.</th>
                <th style="width: 12%;">Folio</th>
                <th style="width: 17%;">CURP</th>
                <th style="width: 33%;">Nombre Completo del Beneficiario</th>
                <th style="width: 8%;">Asist.</th>
                <th style="width: 11%;">Estatus</th>
                <th style="width: 16%;">Firma de Acuse</th>
            </tr>
        </thead>
        <tbody>
            @forelse($grupo->beneficiarios as $index => $b)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ $b->folio }}</td>
                    <td class="text-center">{{ $b->curp }}</td>
                    <td>{{ $b->primer_apellido }} {{ $b->segundo_apellido }} {{ $b->nombre }}</td>
                    <td class="text-center">{{ $b->tot_asistencias ?? 0 }}</td>
                    <td class="text-center" style="font-weight: bold; color: {{ $b->estatus === 'Acreditado' ? '#047857' : '#b91c1c' }};">
                        {{ $b->estatus }}
                    </td>
                    <td class="firma-box"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px;">No se encontraron beneficiarios asignados a este grupo.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>