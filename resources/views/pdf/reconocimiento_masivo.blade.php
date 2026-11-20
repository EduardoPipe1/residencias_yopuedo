<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reconocimientos Masivos</title>
    @include('pdf.partials.reconocimiento_estilos')
</head>
<body>
    @foreach($beneficiarios as $beneficiario)
        @include('pdf.partials.reconocimiento_hoja', ['beneficiario' => $beneficiario, 'saltoPagina' => !$loop->last])
    @endforeach
</body>
</html>
