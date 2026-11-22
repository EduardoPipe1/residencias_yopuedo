@extends('layouts.app')
@section('titulo', 'Importación de Padrón')
@section('contenido')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-[#002F6C]">Carga y Migración del Padrón</h1>
        <p class="text-sm text-gray-600">Módulo de transferencia masiva de registros hacia la base de datos relacional MySQL.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-600 text-emerald-800 p-4 rounded mb-6 text-sm shadow-sm flex items-center">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded mb-6 text-sm shadow-sm">
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-600 text-red-800 p-4 rounded mb-6 text-sm shadow-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 max-w-2xl">
        <h2 class="text-base font-semibold text-[#002F6C] mb-4">Seleccionar archivo de datos</h2>
        
        <form action="{{ route('importaciones.store') }}" method="POST" enctype="multipart/form-data"
              x-data="{ procesando: false }" @submit="procesando = true">
            @csrf
            <div class="mb-5">
                <label for="archivo" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    Archivo del padrón: Excel (.xlsx) o CSV
                </label>
                <input type="file" name="archivo" id="archivo" accept=".xlsx,.csv" required
                       class="block w-full text-sm text-gray-700 border border-gray-300 rounded-md p-2 bg-gray-50
                              file:mr-4 file:py-2 file:px-4 file:rounded file:border-0
                              file:bg-[#004F9F] file:text-white file:text-xs file:font-semibold file:uppercase hover:file:bg-[#003B77] file:cursor-pointer">
            </div>

            <button type="submit" :disabled="procesando"
                    class="px-5 py-2.5 bg-[#004F9F] hover:bg-[#003B77] text-white text-xs font-bold uppercase rounded shadow transition disabled:opacity-60 disabled:cursor-wait">
                <span x-show="!procesando">Procesar e Insertar en Base de Datos</span>
                <span x-show="procesando" x-cloak>Procesando…</span>
            </button>

            <div x-show="procesando" x-cloak class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-xl p-8 max-w-sm text-center">
                    <div class="mx-auto mb-4 h-10 w-10 rounded-full border-4 border-blue-200 border-t-[#004F9F] animate-spin"></div>
                    <p class="font-bold text-gray-900">Importando padrón…</p>
                    <p class="text-sm text-gray-600 mt-1">Esto puede tardar unos minutos con archivos grandes. No cierres ni recargues esta página.</p>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-sm font-bold text-[#002F6C] uppercase tracking-wide">Archivos procesados en almacenamiento</h2>
        </div>
        <table class="w-full text-left text-sm text-gray-700">
            <thead class="bg-gray-100 text-xs font-semibold uppercase text-gray-600 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3">Nombre del Archivo</th>
                    <th class="px-6 py-3">Tamaño</th>
                    <th class="px-6 py-3">Fecha de Carga</th>
                    <th class="px-6 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($archivos as $a)
                    <tr class="hover:bg-blue-50/50 transition">
                        <td class="px-6 py-3.5 font-medium text-gray-900">{{ $a['nombre'] }}</td>
                        <td class="px-6 py-3.5">{{ number_format($a['tamano'] / 1024, 1) }} KB</td>
                        <td class="px-6 py-3.5">{{ \Carbon\Carbon::createFromTimestamp($a['fecha'])->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-3.5 text-right">
                            <a href="{{ route('importaciones.show', $a['nombre']) }}" class="text-[#004F9F] hover:text-[#002F6C] font-semibold text-xs uppercase underline">
                                Descargar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-6 text-center text-gray-400 text-sm">No hay registros procesados actualmente.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection