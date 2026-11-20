<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante {{ $movimiento->ticket_id }}</title>
    <style>
        @page { margin: 40pt 40pt; size: letter portrait; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #000; font-size: 9pt; }
        .encabezado { width: 100%; border-collapse: collapse; margin-bottom: 14pt; }
        .encabezado td { vertical-align: middle; }
        .titulo { text-align: center; font-size: 13pt; font-weight: bold; }
        .subtitulo { text-align: center; font-size: 8.5pt; color: #333; margin-top: 2pt; }
        .datos { width: 100%; border-collapse: collapse; margin-bottom: 12pt; }
        .datos td { border: 0.75pt solid #000; padding: 4pt 6pt; }
        .datos td.etiqueta { background: #00B0F0; color: #fff; font-weight: bold; width: 28%; }
        h3 { font-size: 10pt; margin: 12pt 0 4pt 0; }
        .tabla { width: 100%; border-collapse: collapse; }
        .tabla th { background: #00B0F0; color: #fff; font-size: 8pt; border: 0.75pt solid #000; padding: 4pt; }
        .tabla td { border: 0.75pt solid #000; font-size: 8pt; padding: 3pt 4pt; }
        .tabla thead { display: table-header-group; }
        .tabla tr { page-break-inside: avoid; }
        .centro { text-align: center; }
        .pie { margin-top: 16pt; font-size: 7.5pt; color: #444; text-align: center; }
    </style>
</head>
<body>
    @php $datos = $movimiento->nuevos(); @endphp

    <table class="encabezado">
        <tr>
            <td style="width: 30%;"><img style="height: 48pt;" src="{{ public_path('imagen/nuevo_comienzo.png') }}"></td>
            <td style="width: 40%;">
                <div class="titulo">Comprobante de movimiento</div>
                <div class="subtitulo">Bitácora del sistema · Yo Puedo, Guanajuato Puede</div>
            </td>
            <td style="width: 30%; text-align: right;"><img style="height: 48pt;" src="{{ public_path('imagen/yo_puedo.png') }}"></td>
        </tr>
    </table>

    <table class="datos">
        <tr><td class="etiqueta">Ticket</td><td>{{ $movimiento->ticket_id }}</td></tr>
        <tr><td class="etiqueta">Fecha y hora</td><td>{{ $movimiento->created_at?->format('d/m/Y H:i:s') }}</td></tr>
        <tr><td class="etiqueta">Usuario</td><td>{{ $movimiento->usuario_responsable }}</td></tr>
        <tr><td class="etiqueta">Movimiento</td><td>{{ $movimiento->accion_legible }}</td></tr>
        <tr><td class="etiqueta">Descripción</td><td>{{ $movimiento->descripcion }}</td></tr>
        @foreach(($datos['filtros'] ?? []) as $clave => $valor)
            <tr><td class="etiqueta">{{ $clave }}</td><td>{{ $valor }}</td></tr>
        @endforeach
        @isset($datos['archivo'])
            <tr><td class="etiqueta">Archivo</td><td>{{ $datos['archivo'] }}</td></tr>
            <tr><td class="etiqueta">Registros procesados</td><td>{{ number_format($datos['registros_procesados'] ?? 0) }}</td></tr>
        @endisset
    </table>

    @if(!empty($datos['beneficiarios']))
        <h3>Beneficiarios incluidos ({{ $datos['total'] }})</h3>
        <table class="tabla">
            <thead><tr><th style="width:5%">#</th><th style="width:17%">Folio</th><th style="width:40%">Nombre</th><th style="width:10%">Grupo</th><th style="width:28%">Municipio</th></tr></thead>
            <tbody>
                @foreach($datos['beneficiarios'] as $i => $b)
                    <tr>
                        <td class="centro">{{ $i + 1 }}</td><td class="centro">{{ $b['folio'] }}</td><td>{{ $b['nombre'] }}</td>
                        <td class="centro">{{ $b['grupo'] }}</td><td>{{ $b['municipio'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(!empty($datos['rangos']))
        <h3>Rangos impresos ({{ $datos['total'] }})</h3>
        <table class="tabla">
            <thead><tr><th style="width:60%">Coordinador / Promotor</th><th>Gpo Inicial</th><th>Gpo Final</th></tr></thead>
            <tbody>
                @foreach($datos['rangos'] as $r)
                    <tr><td>{{ $r['promotor'] }}</td><td class="centro">{{ $r['gpo_inicial'] }}</td><td class="centro">{{ $r['gpo_final'] }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(!empty($datos['avisos']))
        <h3>Avisos de la importación</h3>
        <ul>@foreach($datos['avisos'] as $a)<li>{{ $a }}</li>@endforeach</ul>
    @endif

    <div class="pie">Documento generado automáticamente por el sistema a partir de la bitácora de auditoría. El registro original no puede modificarse.</div>
</body>
</html>
