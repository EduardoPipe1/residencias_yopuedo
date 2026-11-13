@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-50 font-sans text-gray-800" x-data>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <h1 class="text-3xl font-extrabold text-gray-900 mb-8 text-center uppercase tracking-wide">
            Emisión de Reconocimientos Oficiales
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- TARJETA 1: IMPRESIÓN INDIVIDUAL -->
            <div class="bg-white p-8 rounded-xl shadow-lg border-t-4 border-blue-600" x-data="{ idBeneficiario: '' }">
                <h2 class="text-2xl font-bold mb-2 text-blue-700">Impresión Individual</h2>
                <p class="text-gray-500 mb-6 text-sm">Genera el reconocimiento en PDF para un solo beneficiario.</p>
                
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">ID del Beneficiario</label>
                    <input type="number" x-model="idBeneficiario" placeholder="Ej. 1" 
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-3 border bg-gray-50">
                </div>
                
                <a :href="idBeneficiario ? '/reconocimientos/' + idBeneficiario + '/individual' : '#'" 
                   :class="idBeneficiario ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed text-gray-500'"
                   class="block w-full text-center text-white font-bold py-3 px-4 rounded-lg transition-all"
                   target="_blank">
                    Generar PDF Individual
                </a>
            </div>

            <!-- TARJETA 2: IMPRESIÓN MASIVA -->
            <div class="bg-white p-8 rounded-xl shadow-lg border-t-4 border-green-600">
                <h2 class="text-2xl font-bold mb-2 text-green-700">Impresión Masiva (Filtros)</h2>
                <p class="text-gray-500 mb-6 text-sm">Genera un solo archivo PDF con múltiples reconocimientos.</p>
                
                <form action="/reconocimientos/masivo" method="GET" target="_blank" class="space-y-4">
                    
                    <!-- Filtro: Región -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Región</label>
                        <select name="region_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 p-2.5 border bg-gray-50">
                            <option value="">-- Todas las Regiones --</option>
                            @foreach($regiones as $region)
                                <option value="{{ $region->id }}">{{ $region->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro: Promotor -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Promotor</label>
                        <select name="promotor_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 p-2.5 border bg-gray-50">
                            <option value="">-- Todos los Promotores --</option>
                            @foreach($promotores as $promotor)
                                <option value="{{ $promotor->id }}">{{ $promotor->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro: Grupo -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Grupo Formativo</label>
                        <select name="grupo_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 p-2.5 border bg-gray-50">
                            <option value="">-- Todos los Grupos --</option>
                            @foreach($grupos as $grupo)
                                <option value="{{ $grupo->id }}">{{ $grupo->identificador_grupo }} - {{ $grupo->municipio->nombre ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro: Estatus -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Estatus</label>
                        <select name="estatus" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 p-2.5 border bg-gray-50">
                            <option value="Acreditado" selected>Acreditado (Por defecto)</option>
                            <option value="Registrado">Registrado</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg mt-4 transition-all">
                        Generar PDF Masivo
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection