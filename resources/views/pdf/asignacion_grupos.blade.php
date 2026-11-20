<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignación de grupos</title>
    <style>
        @page { margin: 40pt 40pt; size: letter portrait; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #000; }
        .encabezado { width: 100%; border-collapse: collapse; margin-bottom: 12pt; }
        .encabezado td { vertical-align: middle; }
        .titulo { text-align: center; font-size: 13pt; font-weight: bold; }
        .subtitulo { text-align: center; font-size: 9pt; color: #333; }
        .tabla { width: 100%; border-collapse: collapse; }
        .tabla th { background: #00B0F0; color: #fff; font-size: 9pt; border: 0.75pt solid #000; padding: 5pt; }
        .tabla td { border: 0.75pt solid #000; font-size: 9pt; padding: 4pt 6pt; }
        .tabla thead { display: table-header-group; }
        .centro { text-align: center; }
    </style>
</head>
<body>
    <table class="encabezado">
        <tr>
            <td style="width: 30%;"><img style="height: 48pt;" src="{{ public_path('imagen/nuevo_comienzo.png') }}"></td>
            <td style="width: 40%;">
                <div class="titulo">Asignación de grupos</div>
                <div class="subtitulo">{{ $region->nombre }}</div>
            </td>
            <td style="width: 30%; text-align: right;"><img style="height: 48pt;" src="{{ public_path('imagen/yo_puedo.png') }}"></td>
        </tr>
    </table>

    <table class="tabla">
        <thead>
            <tr><th style="width: 60%;">Coordinador / Promotor</th><th>Gpo Inicial</th><th>Gpo Final</th></tr>
        </thead>
        <tbody>
            @foreach($asignaciones as $fila)
                <tr>
                    <td>{{ $fila['promotor'] }}</td>
                    <td class="centro">{{ $fila['gpo_inicial'] }}</td>
                    <td class="centro">{{ $fila['gpo_final'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
