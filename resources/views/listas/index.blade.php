@extends('layouts.app')

@section('titulo', 'Lista de reconocimientos')

@section('contenido')
<div class="w-full max-w-3xl mx-auto" x-data="listasApp()">

    <div class="mb-8 bg-white p-6 rounded-lg shadow-sm border-l-4 border-[#0072CE]">
        <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-wide">Lista de Reconocimientos</h1>
        <p class="text-gray-600 mt-1">Acta de entrega por grupo: incluye a todos los beneficiarios del grupo (graduados y bajas) con espacio para firma y fecha de entrega del reconocimiento.</p>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-green-700 px-6 py-4">
            <h2 class="text-xl font-bold text-white">Generar listas</h2>
        </div>
        <form action="{{ route('listas.generar') }}" method="GET" target="_blank" class="p-6 space-y-5">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Región</label>
                <select name="region_id" required x-model="regionSeleccionada" @change="cargarPromotores()" class="w-full border-gray-300 rounded-lg shadow-sm p-2.5 border bg-gray-50 outline-none">
                    <option value="">-- Selecciona una región --</option>
                    @foreach($regiones as $region)
                        <option value="{{ $region->id }}">{{ $region->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Promotor</label>
                <select name="promotor_id" x-model="promotorSeleccionado" @change="cargarGrupos()" :disabled="!regionSeleccionada" class="w-full border-gray-300 rounded-lg shadow-sm p-2.5 border bg-gray-50 outline-none disabled:opacity-50">
                    <option value="">-- Todos los promotores de la región --</option>
                    <template x-for="promotor in promotores" :key="promotor.id">
                        <option :value="promotor.id" x-text="promotor.nombre_completo"></option>
                    </template>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Grupo</label>
                <select name="grupo_id" x-model="grupoSeleccionado" :disabled="!regionSeleccionada" class="w-full border-gray-300 rounded-lg shadow-sm p-2.5 border bg-gray-50 outline-none disabled:opacity-50">
                    <option value="">-- Todos los grupos --</option>
                    <template x-for="grupo in grupos" :key="grupo.id">
                        <option :value="grupo.id" x-text="grupo.nombre_mostrar"></option>
                    </template>
                </select>
                <span class="text-xs text-gray-500 mt-1 block">Cada grupo se imprime en su propia hoja.</span>
            </div>

            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition">
                Generar Lista de Reconocimientos
            </button>
        </form>
    </div>
</div>

@if(session('warning') || $errors->any())
<div class="fixed bottom-4 right-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded shadow-lg max-w-md z-50"
     x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)">
    <p class="font-bold text-sm uppercase">Aviso del Sistema</p>
    <p class="text-sm mt-1">{{ session('warning') ?? $errors->first() }}</p>
</div>
@endif

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('listasApp', () => ({
            regionSeleccionada: '', promotorSeleccionado: '', grupoSeleccionado: '',
            promotores: [], grupos: [],

            cargarPromotores() {
                this.promotorSeleccionado = ''; this.grupoSeleccionado = '';
                this.promotores = []; this.grupos = [];
                if (!this.regionSeleccionada) return;
                fetch('/reconocimientos/api/promotores/' + this.regionSeleccionada).then(r => r.json()).then(d => this.promotores = d);
                fetch('/reconocimientos/api/grupos/region/' + this.regionSeleccionada).then(r => r.json()).then(d => this.grupos = d);
            },

            cargarGrupos() {
                this.grupoSeleccionado = ''; this.grupos = [];
                const url = this.promotorSeleccionado
                    ? '/reconocimientos/api/grupos/promotor/' + this.promotorSeleccionado
                    : '/reconocimientos/api/grupos/region/' + this.regionSeleccionada;
                fetch(url).then(r => r.json()).then(d => this.grupos = d);
            },
        }));
    });
</script>
@endsection
