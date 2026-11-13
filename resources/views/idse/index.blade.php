@extends('layouts.app')
@section('titulo', 'Padrón de Beneficiarios')
@section('contenido')

    <!-- Encabezado de la Seccion -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#002F6C] uppercase tracking-tight">Buscador de Beneficiarios</h1>
            <p class="text-sm text-gray-600">Padrón consolidado del programa "Yo Puedo, Guanajuato Puede".</p>
        </div>
        <div>
            <a href="{{ url('/importaciones') }}" 
               class="inline-flex items-center px-4 py-2.5 bg-[#004F9F] hover:bg-[#002F6C] text-white text-xs font-black uppercase tracking-wider rounded-md shadow-sm transition">
                Importar Más Folios
            </a>
        </div>
    </div>

    <!-- Filtros de Busqueda Multicriterio -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 mb-6">
        <form method="GET" action="{{ url('/idse') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            
            <!-- Campo 1: Folio o CURP -->
            <div>
                <label for="buscar" class="block text-xs font-bold text-gray-700 uppercase mb-1">Folio o CURP</label>
                <input type="text" name="buscar" id="buscar" value="{{ request('buscar') }}" 
                       placeholder="Ej. 202610001 o CURP" 
                       class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-[#004F9F] focus:ring-1 focus:ring-[#004F9F]">
            </div>

            <!-- Campo 2: Nombre o Apellidos -->
            <div>
                <label for="nombre" class="block text-xs font-bold text-gray-700 uppercase mb-1">Nombre o Apellidos</label>
                <input type="text" name="nombre" id="nombre" value="{{ request('nombre') }}" 
                       placeholder="Nombre del participante" 
                       class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-[#004F9F] focus:ring-1 focus:ring-[#004F9F]">
            </div>

            <!-- Campo 3: Estatus Binario -->
            <div>
                <label for="estatus" class="block text-xs font-bold text-gray-700 uppercase mb-1">Estatus</label>
                <select name="estatus" id="estatus" 
                        class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-[#004F9F] focus:ring-1 focus:ring-[#004F9F] bg-white">
                    <option value="">Todos los estatus</option>
                    <option value="Acreditado" {{ request('estatus') === 'Acreditado' ? 'selected' : '' }}>Acreditado</option>
                    <option value="No Acreditado" {{ request('estatus') === 'No Acreditado' ? 'selected' : '' }}>No Acreditado</option>
                </select>
            </div>

            <!-- Botones de Accion -->
            <div class="flex items-end space-x-2">
                <button type="submit" 
                        class="w-full py-2 bg-[#002F6C] hover:bg-[#004F9F] text-white text-xs font-bold uppercase tracking-wider rounded shadow transition">
                    Filtrar
                </button>
                <a href="{{ url('/idse') }}" 
                   class="py-2 px-3 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold uppercase tracking-wider rounded transition text-center">
                    Limpiar
                </a>
            </div>

        </form>
    </div>

    <!-- Tabla del Padron -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-700">
                <thead class="bg-[#002F6C] text-xs font-semibold uppercase text-white tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Folio IDSE</th>
                        <th class="px-5 py-3.5">CURP</th>
                        <th class="px-5 py-3.5">Beneficiario</th>
                        <th class="px-5 py-3.5 text-center">Grupo</th>
                        <th class="px-5 py-3.5 text-center">Avance</th>
                        <th class="px-5 py-3.5 text-center">Estatus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($beneficiarios as $b)
                        <tr class="hover:bg-blue-50/50 transition">
                            <td class="px-5 py-3.5 font-mono font-bold text-[#004F9F]">
                                IDSE{{ $b->folio }}
                            </td>
                            <td class="px-5 py-3.5 text-xs font-mono text-gray-600">
                                {{ $b->curp }}
                            </td>
                            <td class="px-5 py-3.5 font-bold text-gray-900 uppercase">
                                {{ $b->nombre }} {{ $b->primer_apellido }} {{ $b->segundo_apellido }}
                            </td>
                            <td class="px-5 py-3.5 text-center font-semibold text-xs text-gray-600">
                                {{ $b->grupo->identificador_grupo ?? 'G1001' }}
                            </td>
                            <td class="px-5 py-3.5 text-center font-bold text-xs">
                                {{ number_format($b->porc_avance, 2) }}%
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($b->estatus === 'Acreditado')
                                    <span class="inline-block px-3 py-0.5 text-[11px] font-black uppercase tracking-wider rounded bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        ACREDITADO
                                    </span>
                                @else
                                    <span class="inline-block px-3 py-0.5 text-[11px] font-black uppercase tracking-wider rounded bg-blue-100 text-[#004F9F] border border-blue-200">
                                        NO ACREDITADO
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                No se encontraron registros de beneficiarios en el padrón.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($beneficiarios, 'links'))
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $beneficiarios->links() }}
            </div>
        @endif
    </div>

@endsection