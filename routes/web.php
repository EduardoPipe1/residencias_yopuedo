<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IdseController;
use App\Http\Controllers\FolioController;
use App\Http\Controllers\ListaController;
use App\Http\Controllers\ReconocimientoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\PromotorController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ImportacionController;

Route::get('/', function () {
    return view('dashboard');
})->name('home');

// Módulo IDSE (Padrón, buscador con filtros y persistencia)
Route::prefix('idse')->controller(IdseController::class)->name('idse.')->group(function () {
    Route::get('/', 'index')->name('index');               // Buscador con filtros
    Route::get('/{beneficiario}', 'show')->name('show');   // Ficha individual
    Route::put('/{beneficiario}', 'update')->name('update'); // Actualizar estatus y emitir auditoría
});

// Módulo FOLIOS (Solicitudes de registro en PDF)
Route::prefix('folios')->controller(FolioController::class)->name('folios.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{beneficiario}/imprimir', 'imprimirIndividual')->name('imprimir.individual');
    Route::post('/imprimir-masivo', 'imprimirMasivo')->name('imprimir.masivo');
});

// Módulo LISTAS (Listas de asistencia oficiales con espacio para firmas)
Route::prefix('listas')->controller(ListaController::class)->name('listas.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/grupo/{grupo}/pdf', 'generarPdfGrupo')->name('pdf.grupo'); // Descarga directa del PDF por grupo
    Route::post('/generar', 'generar')->name('generar');
});

// Módulo RECONOCIMIENTOS (Diplomas institucionales)
Route::prefix('reconocimientos')->controller(ReconocimientoController::class)->name('reconocimientos.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{beneficiario}/individual', 'individual')->name('individual');
    Route::post('/masivo', 'masivo')->name('masivo');
});

// Módulo IMPORTACIONES (Carga masiva de CSV 113 columnas)
Route::prefix('importaciones')->controller(ImportacionController::class)->name('importaciones.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::get('/{archivo}', 'show')->name('show');
});

// MÓDULO CATÁLOGOS Y ESTRUCTURA (Grupos, Promotores, Municipios, Regiones)
Route::resource('grupos', GrupoController::class);
Route::resource('promotores', PromotorController::class);
Route::resource('municipios', MunicipioController::class);
Route::resource('regiones', RegionController::class);