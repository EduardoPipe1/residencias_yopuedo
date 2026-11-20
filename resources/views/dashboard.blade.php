<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Yo Puedo, Guanajuato Puede</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-[#F4F6F9]">

    <main class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-xl bg-white rounded-2xl shadow-lg overflow-hidden text-center">
            <div class="h-2 bg-[#0072CE]"></div>

            <div class="px-8 pt-10 pb-12">
                <div class="flex items-center justify-center gap-8 mb-10">
                    <img src="{{ asset('imagen/nuevo_comienzo.png') }}" alt="Secretaría del Nuevo Comienzo" class="h-16 w-auto">
                    <span class="h-12 w-px bg-gray-200"></span>
                    <img src="{{ asset('imagen/yo_puedo.png') }}" alt="Yo Puedo, Guanajuato Puede" class="h-16 w-auto">
                </div>

                <h1 class="text-3xl font-black text-[#002F6C] tracking-tight">Bienvenido al sistema</h1>
                <p class="mt-3 text-gray-600">Gestión operativa, control de folios y documentación oficial del programa
                    <span class="font-semibold text-[#004F9F]">Yo Puedo, Guanajuato Puede</span>.</p>

                @auth
                    <a href="{{ url('/idse') }}"
                       class="mt-10 inline-block bg-[#004F9F] hover:bg-[#003B77] text-white font-bold uppercase tracking-wider text-sm px-10 py-3.5 rounded-lg shadow transition">
                        Entrar al sistema
                    </a>
                    <p class="mt-3 text-xs text-gray-500">Sesión iniciada como {{ auth()->user()->name }}</p>
                @else
                    <a href="{{ route('login') }}"
                       class="mt-10 inline-block bg-[#004F9F] hover:bg-[#003B77] text-white font-bold uppercase tracking-wider text-sm px-10 py-3.5 rounded-lg shadow transition">
                        Acceder
                    </a>
                @endauth
            </div>
        </div>
    </main>

    <footer class="text-center text-xs text-gray-500 pb-6 px-4">
        Secretaría del Nuevo Comienzo · Dirección de Fortalecimiento de Capacidades para el Desarrollo Humano · Gobierno del Estado de Guanajuato
    </footer>
</body>
</html>
