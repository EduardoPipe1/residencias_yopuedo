<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de reconocimientos</title>
    {{-- Formato oficial "LISTA (1).pdf": hoja carta vertical, logos, encabezado azul y casillas de firma/fecha --}}
    <style>
        @page { margin: 40pt 36pt 36pt 36pt; size: letter portrait; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #000; margin: 0; }
        .page-break { page-break-after: always; }

        .encabezado { width: 100%; border-collapse: collapse; margin-bottom: 10pt; }
        .encabezado td { vertical-align: middle; border: none; }
        .logo-izq { height: 48pt; }
        .logo-der { height: 48pt; }
        .titulo { text-align: center; font-size: 13pt; font-weight: bold; }

        .lista { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .lista th {
            background: #00B0F0; color: #fff; font-size: 6.5pt; font-weight: bold;
            border: 0.75pt solid #000; padding: 4pt 2pt; text-align: center;
        }
        .lista td { border: 0.75pt solid #000; font-size: 6.5pt; padding: 2pt 2pt; height: 20pt; vertical-align: middle; }
        .lista thead { display: table-header-group; }   /* repite encabezado si el grupo ocupa varias hojas */
        .lista tr { page-break-inside: avoid; }
        .centro { text-align: center; }
    </style>
</head>
<body>
    @foreach($grupos as $grupo)
        <div class="{{ !$loop->last ? 'page-break' : '' }}">
            <table class="encabezado">
                <tr>
                    <td style="width: 30%;"><img class="logo-izq" src="{{ public_path('imagen/nuevo_comienzo.png') }}"></td>
                    <td class="titulo" style="width: 40%;">Lista de reconocimientos</td>
                    <td style="width: 30%; text-align: right;"><img class="logo-der" src="{{ public_path('imagen/yo_puedo.png') }}"></td>
                </tr>
            </table>

            <table class="lista">
                <thead>
                    <tr>
                        <th style="width: 13%">Folio</th><th style="width: 18%">CURP</th><th style="width: 22%">Nombre</th>
                        <th style="width: 10%">Estatus</th><th style="width: 6%">Grupo</th>
                        <th style="width: 16%">Firma de recepción de reconocimiento</th><th style="width: 15%">Fecha de entrega de reconocimiento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grupo->beneficiarios as $b)
                        <tr>
                            <td class="centro">IDSE{{ $b->folio }}</td>
                            <td class="centro">{{ $b->curp }}</td>
                            <td>{{ $b->nombre }} {{ $b->primer_apellido }} {{ $b->segundo_apellido }}</td>
                            <td class="centro">{{ $b->estatus }}</td>
                            <td class="centro">{{ $grupo->identificador_grupo }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>
