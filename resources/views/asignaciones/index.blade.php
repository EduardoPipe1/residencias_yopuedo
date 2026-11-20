@extends('layouts.app')

@section('titulo', 'Promotores y Grupos')

@section('contenido')
<div class="w-full">
    <div class="mb-8 bg-white p-6 rounded-lg shadow-sm border-l-4 border-[#0072CE]">
        <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-wide">Promotores y Grupos</h1>
        <p class="text-gray-600 mt-1">Asignación de grupos por coordinador / promotor, tomada del padrón importado. Los grupos consecutivos se muestran como un rango.</p>
    </div>

    <form method="GET" action="{{ route('asignaciones.index') }}" class="bg-white rounded-xl shadow-md p-6 mb-6 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Región</label>
            <select name="region_id" onchange="this.form.promotor_id.value=''; this.form.submit()" class="w-full border-gray-300 rounded-lg p-2.5 border bg-gray-50 outline-none">
                <option value="">-- Selecciona una región --</option>
                @foreach($regiones as $region)
                    <option value="{{ $region->id }}" @selected(request('region_id') == $region->id)>{{ $region->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Promotor</label>
            <select name="promotor_id" onchange="this.form.submit()" @disabled(!request('region_id')) class="w-full border-gray-300 rounded-lg p-2.5 border bg-gray-50 outline-none disabled:opacity-50">
                <option value="">-- Todos los promotores --</option>
                @foreach($promotores as $promotor)
                    <option value="{{ $promotor->id }}" @selected(request('promotor_id') == $promotor->id)>{{ $promotor->nombre_completo }}</option>
                @endforeach
            </select>
        </div>
        <div>
            @if(request('region_id') && $asignaciones->isNotEmpty())
                <a href="{{ route('asignaciones.pdf', request()->only('region_id', 'promotor_id')) }}" target="_blank"
                   class="block w-full text-center bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-md transition">
                    Imprimir asignación
                </a>
            @endif
        </div>
    </form>

    @if(request('region_id'))
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[#002F6C] text-white uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Coordinador / Promotor</th>
                        <th class="px-4 py-3 text-center">Gpo Inicial</th>
                        <th class="px-4 py-3 text-center">Gpo Final</th>
                        <th class="px-4 py-3 text-center">Grupos</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($asignaciones as $fila)
                        <tr class="hover:bg-blue-50">
                            <td class="px-4 py-2.5 font-medium text-gray-900">{{ $fila['promotor'] }}</td>
                            <td class="px-4 py-2.5 text-center">{{ $fila['gpo_inicial'] }}</td>
                            <td class="px-4 py-2.5 text-center">{{ $fila['gpo_final'] }}</td>
                            <td class="px-4 py-2.5 text-center text-gray-500">{{ $fila['total'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No hay grupos registrados para esta selección.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
