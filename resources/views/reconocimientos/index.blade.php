@extends('layouts.app')

@section('contenido')
<div class="w-full" x-data="reconocimientosApp()">
    
    <div class="mb-8 bg-white p-6 rounded-lg shadow-sm border-l-4 border-[#0072CE]">
        <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-wide">Módulo de Reconocimientos Oficiales</h1>
        <p class="text-gray-600 mt-1">Generación de diplomas en formato PDF de manera individual o mediante filtrado operativo masivo.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <div class="bg-white rounded-xl shadow-md overflow-hidden flex flex-col">
            <div class="bg-[#002F6C] px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Impresión Individual (Folio IDSE)
                </h2>
            </div>
            <div class="p-6 grow flex flex-col">
                <p class="text-gray-500 mb-6 text-sm">Ingresa el Folio IDSE del beneficiario (ej. IDSE202313633) para emitir su reconocimiento único.</p>
                
                <div class="mb-6 grow">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Folio IDSE</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="folioBeneficiario" placeholder="Ej. IDSE202313633" 
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#0072CE] focus:border-[#0072CE] p-3 border bg-gray-50 uppercase outline-none transition-colors">
                        
                        <a :href="folioBeneficiario && folioBeneficiario.trim() !== '' ? '/reconocimientos/' + folioBeneficiario.trim() + '/individual' : '#'" 
                           :class="folioBeneficiario && folioBeneficiario.trim() !== '' ? 'bg-[#002F6C] hover:bg-[#001d42] shadow cursor-pointer' : 'bg-gray-300 cursor-not-allowed text-gray-500 pointer-events-none'"
                           class="px-5 py-3 rounded-lg font-bold text-white transition-all shrink-0 flex items-center gap-1 text-center justify-center"
                           target="_blank">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Imprimir
                        </a>
                    </div>
                    <span class="text-xs text-gray-500 mt-1 block">Escribe el folio y haz clic en el botón para abrir el PDF.</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-green-700 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Impresión Masiva (Filtros)
                </h2>
            </div>
            <div class="p-6">
                <p class="text-gray-500 mb-6 text-sm">Emite un bloque completo de reconocimientos aplicando los siguientes criterios operativos.</p>
                
                <form action="/reconocimientos/masivo" method="GET" target="_blank" class="space-y-5">
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Región</label>
                        <select name="region_id" x-model="regionSeleccionada" @change="cargarPromotores()" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-600 focus:border-green-600 p-2.5 border bg-gray-50 outline-none">
                            <option value="">-- Todas las Regiones --</option>
                            @foreach($regiones as $region)
                                <option value="{{ $region->id }}">{{ $region->nombre }}</option>
                            @endforeach
                        </select>
                        <span class="text-xs text-gray-500 mt-1 block">Al elegir una región se cargan automáticamente sus promotores y grupos.</span>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Promotor Asignado</label>
                        <select name="promotor_id" x-model="promotorSeleccionado" @change="cargarGrupos()" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-600 focus:border-green-600 p-2.5 border bg-gray-50 outline-none">
                            <option value="">-- Todos los Promotores --</option>
                            <template x-for="promotor in promotoresFiltrados" :key="promotor.id">
                                <option :value="promotor.id" x-text="promotor.nombre_completo"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Grupo Formativo</label>
                        <select name="grupo_id" x-model="grupoSeleccionado" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-600 focus:border-green-600 p-2.5 border bg-gray-50 outline-none">
                            <option value="">-- Todos los Grupos --</option>
                            <template x-for="grupo in gruposFiltrados" :key="grupo.id">
                                <option :value="grupo.id" x-text="grupo.nombre_mostrar"></option>
                            </template>
                        </select>
                    </div>

                    <input type="hidden" name="estatus" value="Acreditado">
                    <p class="text-xs text-gray-500">Se imprimen únicamente los beneficiarios con estatus <span class="font-semibold">Acreditado</span>.</p>
                    
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition-all duration-200">
                            Generar Lote Masivo
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@if(session('warning'))
<div class="fixed bottom-4 right-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded shadow-lg max-w-md z-50" 
     role="alert" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <p class="font-bold text-sm uppercase">Aviso del Sistema</p>
    <p class="text-sm mt-1">{{ session('warning') }}</p>
</div>
@endif

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('reconocimientosApp', () => ({
            folioBeneficiario: '',
            regionSeleccionada: '', 
            promotorSeleccionado: '',
            grupoSeleccionado: '',
            
            promotoresFiltrados: @json($promotores),
            gruposFiltrados: @json($grupos->map(function($g){ return ['id'=>$g->id, 'nombre_mostrar'=>$g->identificador_grupo . ' - ' . ($g->municipio->nombre ?? '')]; })),

            cargarPromotores() {
                this.promotorSeleccionado = '';
                this.grupoSeleccionado = '';
                this.promotoresFiltrados = [];
                this.gruposFiltrados = [];
                
                if (this.regionSeleccionada) {
                    fetch('/reconocimientos/api/promotores/' + this.regionSeleccionada)
                        .then(res => res.json())
                        .then(data => {
                            this.promotoresFiltrados = data;
                        });
                    
                    fetch('/reconocimientos/api/grupos/region/' + this.regionSeleccionada)
                        .then(res => res.json())
                        .then(data => {
                            this.gruposFiltrados = data;
                        });
                } else {
                    this.promotoresFiltrados = @json($promotores);
                    this.gruposFiltrados = @json($grupos->map(function($g){ return ['id'=>$g->id, 'nombre_mostrar'=>$g->identificador_grupo . ' - ' . ($g->municipio->nombre ?? '')]; }));
                }
            },

            cargarGrupos() {
                this.grupoSeleccionado = '';
                this.gruposFiltrados = [];
                
                if (this.promotorSeleccionado) {
                    fetch('/reconocimientos/api/grupos/promotor/' + this.promotorSeleccionado)
                        .then(res => res.json())
                        .then(data => {
                            this.gruposFiltrados = data;
                        });
                } else if (this.regionSeleccionada) {
                    fetch('/reconocimientos/api/grupos/region/' + this.regionSeleccionada)
                        .then(res => res.json())
                        .then(data => {
                            this.gruposFiltrados = data;
                        });
                } else {
                     this.gruposFiltrados = @json($grupos->map(function($g){ return ['id'=>$g->id, 'nombre_mostrar'=>$g->identificador_grupo . ' - ' . ($g->municipio->nombre ?? '')]; }));
                }
            }
        }))
    })
</script>
@endsection