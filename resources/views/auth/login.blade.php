<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - Yo Puedo, Guanajuato Puede</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F6F9] min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="flex items-center justify-between mb-6 px-2">
            <img src="{{ asset('imagen/nuevo_comienzo.png') }}" alt="Secretaría del Nuevo Comienzo" class="h-14 w-auto">
            <img src="{{ asset('imagen/yo_puedo.png') }}" alt="Yo Puedo, Guanajuato Puede" class="h-14 w-auto">
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-[#002F6C] px-6 py-4 border-b-4 border-[#0072CE]">
                <h1 class="text-lg font-bold text-white uppercase tracking-wide">Iniciar sesión</h1>
                <p class="text-xs text-blue-200 mt-0.5">Sistema de gestión operativa · Yo Puedo, Gto Puede</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="p-6 space-y-5">
                @csrf

                @if($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded text-sm">{{ $errors->first() }}</div>
                @endif

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Correo</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full border border-gray-300 rounded-lg p-2.5 bg-gray-50 outline-none focus:border-[#0072CE]">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Contraseña</label>
                    <input id="password" type="password" name="password" required
                           class="w-full border border-gray-300 rounded-lg p-2.5 bg-gray-50 outline-none focus:border-[#0072CE]">
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="recordar" value="1" class="rounded"> Mantener sesión iniciada
                </label>

                <button type="submit" class="w-full bg-[#004F9F] hover:bg-[#003B77] text-white font-bold py-3 rounded-lg shadow transition">
                    Entrar
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-500 mt-4">Secretaría del Nuevo Comienzo · Gobierno del Estado de Guanajuato</p>
    </div>
</body>
</html>
