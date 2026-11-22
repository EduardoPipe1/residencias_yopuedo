<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Panel Principal') - Yo Puedo, Guanajuato Puede</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F6F9] text-gray-800 font-sans min-h-screen flex flex-col">

    <header class="bg-[#002F6C] border-b-4 border-[#0072CE] text-white shadow-md sticky top-0 z-50">
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex flex-wrap items-center justify-between gap-4">
            
            <div class="flex items-center space-x-3 sm:space-x-4">
                
                <div class="flex items-center space-x-2 shrink-0">
                    <img src="{{ asset('imagen/secnuevocomienzo.png') }}" 
                         alt="Secretaría del Nuevo Comienzo" 
                         style="height: 38px; max-height: 38px; width: auto; object-fit: contain;" 
                         class="block">

                    <span class="text-white/30 text-base font-light select-none">|</span>

                    <img src="{{ asset('imagen/gobdelagente.png') }}" 
                         alt="Gobierno del Estado de Guanajuato" 
                         style="height: 38px; max-height: 38px; width: auto; object-fit: contain;" 
                         class="block">
                </div>

                <div class="flex flex-col border-l border-white/20 pl-3">
                    <a href="{{ url('/idse') }}" class="text-base sm:text-lg font-black tracking-wide uppercase text-white hover:text-blue-200 transition leading-tight">
                        Yo Puedo, Gto Puede
                    </a>
                    <span class="text-[11px] text-blue-200 font-medium leading-none mt-0.5">
                        Secretaría del Nuevo Comienzo
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden md:inline text-[11px] bg-[#004F9F] border border-blue-400/30 text-white px-3 py-1 rounded-full font-bold uppercase tracking-wider shadow-sm">
                    Gobierno de la Gente
                </span>
                @auth
                    <span class="text-xs text-blue-100">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-[11px] font-bold uppercase tracking-wider border border-white/40 text-white px-3 py-1 rounded hover:bg-white/10 transition">Salir</button>
                    </form>
                @endauth
            </div>
        </div>

        <div class="bg-[#002454] border-t border-[#004F9F]/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @php
                    $menuConsulta = [
                        ['url' => '/idse',              'activo' => 'idse*',              'texto' => 'Padrón IDSE'],
                        ['url' => '/importaciones',     'activo' => 'importaciones*',     'texto' => 'Importación'],
                        ['url' => '/promotores-grupos', 'activo' => 'promotores-grupos*', 'texto' => 'Promotores y Grupos'],
                        ['url' => '/auditoria',         'activo' => 'auditoria*',         'texto' => 'Bitácora'],
                    ];
                    $menuImpresion = [
                        ['url' => '/folios',          'activo' => 'folios*',          'texto' => 'Solicitudes'],
                        ['url' => '/listas',          'activo' => 'listas*',          'texto' => 'Listas'],
                        ['url' => '/reconocimientos', 'activo' => 'reconocimientos*', 'texto' => 'Reconocimientos'],
                    ];
                @endphp
                {{-- menu --}}
                <nav class="flex items-center gap-1 py-1.5 overflow-x-auto whitespace-nowrap text-xs font-bold uppercase tracking-wider scrollbar-none">
                    @foreach($menuConsulta as $item)
                        <a href="{{ url($item['url']) }}"
                           class="px-3 py-1.5 rounded transition {{ request()->is($item['activo']) ? 'bg-[#004F9F] text-white shadow-sm' : 'text-blue-200 hover:bg-[#003B77] hover:text-white' }}">
                            {{ $item['texto'] }}
                        </a>
                    @endforeach

                    <span class="mx-2 h-5 w-px bg-blue-300/40 shrink-0"></span>
                    <span class="text-[10px] text-blue-300/80 mr-1 shrink-0">Impresión</span>

                    @foreach($menuImpresion as $item)
                        <a href="{{ url($item['url']) }}"
                           class="px-3 py-1.5 rounded border transition {{ request()->is($item['activo']) ? 'bg-emerald-600 border-emerald-400 text-white shadow-sm' : 'border-emerald-500/50 text-emerald-200 hover:bg-emerald-700 hover:text-white' }}">
                            {{ $item['texto'] }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full">
        @yield('contenido')
    </main>

    <footer class="bg-[#002F6C] border-t-2 border-[#0072CE] text-blue-100 text-xs py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center">
            Secretaría del Nuevo Comienzo &bull; Dirección de Fortalecimiento de Capacidades para el Desarrollo Humano &bull; Gobierno del Estado de Guanajuato
        </div>
    </footer>

</body>
</html>