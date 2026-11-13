<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Registro - {{ $beneficiario->folio }}</title>
    <style>
        @page { margin: 2.5cm 2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11pt; color: #1a202c; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #003366; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 14pt; margin: 0; color: #003366; text-transform: uppercase; }
        .header h2 { font-size: 11pt; margin: 5px 0 0; color: #4a5568; }
        .folio-badge { text-align: right; margin-bottom: 15px; font-weight: bold; color: #003366; font-size: 12pt; }
        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        table.datos td { padding: 6px 8px; border: 1px solid #cbd5e0; font-size: 10pt; }
        table.datos td.label { background-color: #f7fafc; font-weight: bold; width: 30%; color: #2d3748; }
        .section-title { font-size: 11pt; font-weight: bold; background-color: #003366; color: #ffffff; padding: 4px 8px; margin-bottom: 8px; }
        .firmas { width: 100%; margin-top: 50px; }
        .firmas td { width: 50%; text-align: center; vertical-align: bottom; }
        .linea-firma { border-top: 1px solid #000; width: 75%; margin: 0 auto 5px auto; }
        .footer { position: fixed; bottom: -1.5cm; left: 0; right: 0; text-align: center; font-size: 8pt; color: #718096; border-top: 1px solid #e2e8f0; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Secretaría del Nuevo Comienzo</h1>
        <h2>Programa "Yo Puedo, Guanajuato Puede"</h2>
        <div style="font-size: 10pt; margin-top: 3px; font-weight: bold;">Cédula Oficial de Solicitud de Registro</div>
    </div>

    <div class="folio-badge">
        Folio: <span>{{ $beneficiario->folio }}</span>
    </div>

    <div class="section-title">I. Datos Personales del Solicitante</div>
    <table class="datos">
        <tr>
            <td class="label">Nombre(s):</td>
            <td>{{ $beneficiario->nombre }}</td>
        </tr>
        <tr>
            <td class="label">Primer Apellido:</td>
            <td>{{ $beneficiario->primer_apellido }}</td>
        </tr>
        <tr>
            <td class="label">Segundo Apellido:</td>
            <td>{{ $beneficiario->segundo_apellido ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">CURP:</td>
            <td><strong>{{ $beneficiario->curp }}</strong></td>
        </tr>
    </table>

    <div class="section-title">II. Ubicación Operativa y Grupo</div>
    <table class="datos">
        <tr>
            <td class="label">Identificador de Grupo:</td>
            <td>{{ $beneficiario->grupo->identificador_grupo ?? 'Sin Asignar' }}</td>
        </tr>
        <tr>
            <td class="label">Municipio:</td>
            <td>{{ $beneficiario->grupo->municipio->nombre ?? 'Guanajuato' }}</td>
        </tr>
        <tr>
            <td class="label">Promotor Responsable:</td>
            <td>{{ $beneficiario->grupo->promotor->nombre_completo ?? 'Coordinación General' }}</td>
        </tr>
        <tr>
            <td class="label">Ciclo del Padrón:</td>
            <td>{{ $beneficiario->anio_padron ?? '2026' }}</td>
        </tr>
    </table>

    <table class="firmas">
        <tr>
            <td>
                <div class="linea-firma"></div>
                <strong>{{ $beneficiario->nombre }} {{ $beneficiario->primer_apellido }}</strong><br>
                Firma del Beneficiario
            </td>
            <td>
                <div class="linea-firma"></div>
                <strong>{{ $beneficiario->grupo->promotor->nombre_completo ?? 'Promotor Operativo' }}</strong><br>
                Sello y Firma de Recepción
            </td>
        </tr>
    </table>

    <div class="footer">
        Dirección de Fortalecimiento de Capacidades para el Desarrollo Humano — Gobierno del Estado de Guanajuato
    </div>
</body>
</html>