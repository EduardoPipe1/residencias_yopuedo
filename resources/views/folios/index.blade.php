@extends('layouts.app')

@section('titulo', 'Solicitudes')

@section('contenido')
<div class="w-full max-w-3xl mx-auto">
    <div class="mb-8 bg-white p-6 rounded-lg shadow-sm border-l-4 border-[#0072CE]">
        <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-wide">Solicitudes de Registro</h1>
        <p class="text-gray-600 mt-1">Impresión de solicitudes de registro de beneficiarios.</p>
    </div>

    {{-- Pendiente: la Dirección entregará el formato oficial de la solicitud --}}
    <div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 p-6 rounded-lg shadow-sm">
        <p class="font-bold uppercase text-sm">Formato pendiente</p>
        <p class="mt-1 text-sm">Este módulo se habilitará cuando se cuente con el formato oficial de la solicitud de registro.</p>
    </div>
</div>
@endsection
