@extends('layouts.app')

@section('titulo', 'Bitácora')

@section('contenido')
<div class="w-full" x-data="{ detalle: null }">
    <div class="mb-6 bg-white p-6 rounded-lg shadow-sm border-l-4 border-[#0072CE]">
        <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-wide">Bitácora de movimientos</h1>
        <p class="text-gray-600 mt-1">Registro de cada importación e impresión realizada en el sistema. Cada movimiento tiene un ticket con su comprobante en PDF.</p>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow-md p-5 mb-6 grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Buscar</label>
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Folio, nombre, grupo, municipio o ticket"
                   class="w-full border border-gray-300 rounded-lg p-2.5 bg-gray-50 outline-none">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Movimiento</label>
            <select name="accion" class="w-full border border-gray-300 rounded-lg p-2.5 bg-gray-50 outline-none">
                <option value="">-- Todos --</option>
                @foreach($acciones as $clave => $texto)
                    <option value="{{ $clave }}" @selected(request('accion') === $clave)>{{ $texto }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Desde</label>
            <input type="date" name="desde" value="{{ request('desde') }}" class="w-full border border-gray-300 rounded-lg p-2.5 bg-gray-50 outline-none">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Hasta</label>
            <input type="date" name="hasta" value="{{ request('hasta') }}" class="w-full border border-gray-300 rounded-lg p-2.5 bg-gray-50 outline-none">
        </div>
        <div class="md:col-span-5 flex gap-2 justify-end">
            <a href="{{ route('auditoria.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 text-sm font-semibold hover:bg-gray-50">Limpiar</a>
            <button class="px-5 py-2 rounded-lg bg-[#004F9F] hover:bg-[#003B77] text-white text-sm font-bold">Buscar</button>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-md overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-[#002F6C] text-white uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Fecha y hora</th>
                    <th class="px-4 py-3 text-left">Movimiento</th>
                    <th class="px-4 py-3 text-left">Descripción</th>
                    <th class="px-4 py-3 text-left">Ticket</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($movimientos as $m)
                    <tr class="hover:bg-blue-50 align-top">
                        <td class="px-4 py-3 whitespace-nowrap">{{ $m->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap font-medium">{{ $m->accion_legible }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $m->descripcion }}</td>
                        <td class="px-4 py-3 whitespace-nowrap font-mono text-xs text-gray-500">{{ $m->ticket_id }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right space-x-1">
                            <button type="button" @click="detalle = {{ Js::from(['ticket' => $m->ticket_id, 'accion' => $m->accion_legible, 'fecha' => $m->created_at?->format('d/m/Y H:i:s'), 'usuario' => $m->usuario_responsable, 'datos' => $m->nuevos(), 'anteriores' => $m->anteriores()]) }}"
                                    class="px-3 py-1.5 rounded border border-gray-300 text-xs font-semibold hover:bg-gray-100">Ver</button>
                            <a href="{{ route('auditoria.pdf', $m->ticket_id) }}" target="_blank"
                               class="px-3 py-1.5 rounded bg-green-600 hover:bg-green-700 text-white text-xs font-semibold">Comprobante</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No hay movimientos con esos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $movimientos->links() }}</div>

    {{-- Ventana de detalle --}}
    <div x-show="detalle" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="detalle = null" @keydown.escape.window="detalle = null">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-[85vh] overflow-y-auto" x-show="detalle">
            <template x-if="detalle">
                <div>
                    <div class="bg-[#002F6C] text-white px-6 py-4 flex justify-between items-center sticky top-0">
                        <div>
                            <p class="font-bold" x-text="detalle.accion"></p>
                            <p class="text-xs text-blue-200" x-text="detalle.ticket + ' · ' + detalle.fecha + ' · ' + detalle.usuario"></p>
                        </div>
                        <button @click="detalle = null" class="text-2xl leading-none">&times;</button>
                    </div>
                    <div class="p-6 space-y-4 text-sm">
                        <template x-if="detalle.datos.filtros">
                            <div>
                                <p class="font-semibold text-gray-700 mb-1">Filtros utilizados</p>
                                <ul class="list-disc list-inside text-gray-600">
                                    <template x-for="(valor, clave) in detalle.datos.filtros"><li><span x-text="clave"></span>: <span x-text="valor"></span></li></template>
                                </ul>
                            </div>
                        </template>
                        <template x-if="detalle.datos.archivo">
                            <p class="text-gray-700">Archivo: <b x-text="detalle.datos.archivo"></b> · Registros procesados: <b x-text="detalle.datos.registros_procesados"></b></p>
                        </template>
                        <template x-if="detalle.datos.beneficiarios">
                            <div>
                                <p class="font-semibold text-gray-700 mb-1">Beneficiarios (<span x-text="detalle.datos.total"></span>)</p>
                                <table class="w-full text-xs border">
                                    <thead class="bg-gray-100"><tr><th class="p-1.5 text-left">Folio</th><th class="p-1.5 text-left">Nombre</th><th class="p-1.5">Grupo</th><th class="p-1.5 text-left">Municipio</th></tr></thead>
                                    <tbody>
                                        <template x-for="b in detalle.datos.beneficiarios">
                                            <tr class="border-t"><td class="p-1.5" x-text="b.folio"></td><td class="p-1.5" x-text="b.nombre"></td><td class="p-1.5 text-center" x-text="b.grupo"></td><td class="p-1.5" x-text="b.municipio"></td></tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                        <template x-if="detalle.datos.rangos">
                            <p class="text-gray-700">Rangos impresos: <b x-text="detalle.datos.total"></b></p>
                        </template>
                        <template x-if="Object.keys(detalle.anteriores).length">
                            <div>
                                <p class="font-semibold text-gray-700 mb-1">Datos anteriores</p>
                                <pre class="bg-gray-50 p-3 rounded text-xs overflow-x-auto" x-text="JSON.stringify(detalle.anteriores, null, 2)"></pre>
                                <p class="font-semibold text-gray-700 mb-1 mt-3">Datos nuevos</p>
                                <pre class="bg-gray-50 p-3 rounded text-xs overflow-x-auto" x-text="JSON.stringify(detalle.datos, null, 2)"></pre>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
@endsection
