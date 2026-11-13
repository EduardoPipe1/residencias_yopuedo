<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reconocimientos Masivos</title>
    <style>
        @page { 
            margin: 0cm; 
            size: letter landscape; 
        }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            color: #000000; 
            margin: 0; 
            padding: 0; 
        }
        .page-break { 
            page-break-after: always; 
        }
        .container { 
            position: relative; 
            width: 792pt; 
            height: 612pt; 
        }
        
        .nombre-container {
            position: absolute;
            top: 235pt;
            left: 400pt;
            right: 40pt;
            text-align: left;
        }
        .beneficiario-nombre {
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .fecha-container {
            position: absolute;
            top: 365pt;
            left: 400pt;
            right: 40pt;
            text-align: left;
        }
        .lugar-fecha {
            font-size: 12pt;
        }

        .footer-ids {
            position: absolute;
            bottom: 35pt;
            right: 50pt;
            font-size: 10pt;
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>
<body>
    @php
        use Carbon\Carbon;
        Carbon::setLocale('es');
        $fechaActual = Carbon::now();
        $mesActual = $fechaActual->translatedFormat('F');
        $anioActual = $fechaActual->format('Y');
    @endphp

    @foreach($beneficiarios as $beneficiario)
        @php
            $municipio = strtoupper($beneficiario->grupo->municipio->nombre ?? 'Irapuato');
            $lugarFechaTexto = $beneficiario->grupo->lugar_fecha_evento ?? "{$municipio}, Gto., a " . ucfirst($mesActual) . " de {$anioActual}";
        @endphp
        <div class="container {{ !$loop->last ? 'page-break' : '' }}">
            <div class="nombre-container">
                <div class="beneficiario-nombre">
                    {{ $beneficiario->nombre }} {{ $beneficiario->primer_apellido }} {{ $beneficiario->segundo_apellido }}
                </div>
            </div>

            <div class="fecha-container">
                <div class="lugar-fecha">
                    {{ $lugarFechaTexto }}
                </div>
            </div>

            <div class="footer-ids">
                {{ $beneficiario->grupo->identificador_grupo ?? '' }} &nbsp;&nbsp;&nbsp;&nbsp; IDSE{{ $beneficiario->folio }}
            </div>
        </div>
    @endforeach
</body>
</html>