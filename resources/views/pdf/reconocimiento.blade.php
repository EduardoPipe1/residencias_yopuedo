<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reconocimiento - IDSE{{ $beneficiario->folio }}</title>
    @include('pdf.partials.reconocimiento_estilos')
</head>
<body>
    @include('pdf.partials.reconocimiento_hoja', ['beneficiario' => $beneficiario])
</body>
</html>
