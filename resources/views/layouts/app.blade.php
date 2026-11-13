<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Panel Principal') - Yo Puedo, Guanajuato Puede</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F6F9] text-gray-800 font-sans min-h-screen flex flex-col">

    <!-- Encabezado Institucional -->
    <header class="bg-[#002F6C] border-b-4 border-[#0072CE] text-white shadow-md sticky top-0 z-50">
        
        <!-- Fila Superior: Identidad Oficial y Títulos -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex flex-wrap items-center justify-between gap-4">
            
            <!-- Logos + Título del Programa -->
            <div class="flex items-center space-x-3 sm:space-x-4">
                
                <!-- Contenedor estricto de Logos -->
                <div class="flex items-center space-x-2 shrink-0">
                    <!-- Logo Secretaría del Nuevo Comienzo -->
                    <img src="{{ asset('imagen/secnuevocomienzo.png') }}" 
                         alt="Secretaría del Nuevo Comienzo" 
                         style="height: 38px; max-height: 38px; width: auto; object-fit: contain;" 
                         class="block">

                    <!-- Línea divisoria vertical -->
                    <span class="text-white/30 text-base font-light select-none">|</span>

                    <!-- Logo Gobierno del Estado de Guanajuato -->
                    <img src="{{ asset('imagen/gobdelagente.png') }}" 
                         alt="Gobierno del Estado de Guanajuato" 
                         style="height: 38px; max-height: 38px; width: auto; object-fit: contain;" 
                         class="block">
                </div>

                <!-- Título del Programa -->
                <div class="flex flex-col border-l border-white/20 pl-3">
                    <a href="{{ url('/idse') }}" class="text-base sm:text-lg font-black tracking-wide uppercase text-white hover:text-blue-200 transition leading-tight">
                        Yo Puedo, Gto Puede
                    </a>
                    <span class="text-[11px] text-blue-200 font-medium leading-none mt-0.5">
                        Secretaría del Nuevo Comienzo
                    </span>
                </div>
            </div>

            <!-- Distintivo de Gobierno de la Gente -->
            <div class="hidden md:flex items-center">
                <span class="text-[11px] bg-[#004F9F] border border-blue-400/30 text-white px-3 py-1 rounded-full font-bold uppercase tracking-wider shadow-sm">
                    Gobierno de la Gente
                </span>
            </div>
        </div>

        <!-- Fila Inferior: Barra de Navegacion de Modulos -->
        <div class="bg-[#002454] border-t border-[#004F9F]/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="flex items-center space-x-1 py-1.5 overflow-x-auto whitespace-nowrap text-xs font-bold uppercase tracking-wider scrollbar-none">
                    <a href="{{ url('/idse') }}" 
                       class="px-3 py-1.5 rounded transition {{ request()->is('idse*') ? 'bg-[#004F9F] text-white shadow-sm' : 'text-blue-200 hover:bg-[#003B77] hover:text-white' }}">
                        Padrón IDSE
                    </a>
                    <a href="{{ url('/importaciones') }}" 
                       class="px-3 py-1.5 rounded transition {{ request()->is('importaciones*') ? 'bg-[#004F9F] text-white shadow-sm' : 'text-blue-200 hover:bg-[#003B77] hover:text-white' }}">
                        Importación
                    </a>
                    <a href="{{ url('/folios') }}" 
                       class="px-3 py-1.5 rounded transition {{ request()->is('folios*') ? 'bg-[#004F9F] text-white shadow-sm' : 'text-blue-200 hover:bg-[#003B77] hover:text-white' }}">
                        Folios
                    </a>
                    <a href="{{ url('/grupos') }}" 
                       class="px-3 py-1.5 rounded transition {{ request()->is('grupos*') ? 'bg-[#004F9F] text-white shadow-sm' : 'text-blue-200 hover:bg-[#003B77] hover:text-white' }}">
                        Grupos
                    </a>
                    <a href="{{ url('/promotores') }}" 
                       class="px-3 py-1.5 rounded transition {{ request()->is('promotores*') ? 'bg-[#004F9F] text-white shadow-sm' : 'text-blue-200 hover:bg-[#003B77] hover:text-white' }}">
                        Promotores
                    </a>
                    <a href="{{ url('/municipios') }}" 
                       class="px-3 py-1.5 rounded transition {{ request()->is('municipios*') ? 'bg-[#004F9F] text-white shadow-inner' : 'text-blue-200 hover:bg-[#003B77] hover:text-white' }}">
                        Municipios
                    </a>
                    <a href="{{ url('/regiones') }}" 
                       class="px-3 py-1.5 rounded transition {{ request()->is('regiones*') ? 'bg-[#004F9F] text-white shadow-inner' : 'text-blue-200 hover:bg-[#003B77] hover:text-white' }}">
                        Regiones
                    </a>
                    <a href="{{ url('/listas') }}" 
                       class="px-3 py-1.5 rounded transition {{ request()->is('listas*') ? 'bg-[#004F9F] text-white shadow-inner' : 'text-blue-200 hover:bg-[#003B77] hover:text-white' }}">
                        Listas
                    </a>
                    <a href="{{ url('/reconocimientos') }}" 
                       class="px-3 py-1.5 rounded transition {{ request()->is('reconocimientos*') ? 'bg-[#004F9F] text-white shadow-inner' : 'text-blue-200 hover:bg-[#003B77] hover:text-white' }}">
                        Reconocimientos
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Contenido Dinamico Principal -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full">
        @yield('contenido')
    </main>

    <!-- Pie de Pagina Institucional -->
    <footer class="bg-[#002F6C] border-t-2 border-[#0072CE] text-blue-100 text-xs py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center">
            Secretaría del Nuevo Comienzo &bull; Dirección de Fortalecimiento de Capacidades para el Desarrollo Humano &bull; Gobierno del Estado de Guanajuato
        </div>
    </footer>

</body>
</html>