<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IdseController;
use App\Http\Controllers\FolioController;
use App\Http\Controllers\ListaController;
use App\Http\Controllers\AsignacionGrupoController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReconocimientoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\PromotorController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ImportacionController;

Route::view('/', 'dashboard')->name('home');

// login
Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:10,1']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// rutas que piden sesion iniciada
Route::middleware('auth')->group(function () {
Route::prefix('idse')->controller(IdseController::class)->name('idse.')->group(function () {
    Route::get('/', 'index')->name('index');
});

Route::prefix('folios')->controller(FolioController::class)->name('folios.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{beneficiario}/imprimir', 'imprimirIndividual')->name('imprimir.individual');
    Route::post('/imprimir-masivo', 'imprimirMasivo')->name('imprimir.masivo');
});

Route::prefix('listas')->controller(ListaController::class)->name('listas.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/generar', 'generar')->name('generar');
});

Route::prefix('promotores-grupos')->controller(AsignacionGrupoController::class)->name('asignaciones.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/pdf', 'pdf')->name('pdf');
});

Route::prefix('reconocimientos')->controller(ReconocimientoController::class)->name('reconocimientos.')->group(function () {
    Route::get('/', 'index')->name('index');
    
    // datos para los select de region, promotor y grupo
    Route::get('/api/promotores/{regionId}', 'obtenerPromotoresPorRegion')->name('api.promotores');
    Route::get('/api/grupos/region/{regionId}', 'obtenerGruposPorRegion')->name('api.grupos.region');
    Route::get('/api/grupos/promotor/{promotorId}', 'obtenerGruposPorPromotor')->name('api.grupos.promotor');
    
    Route::get('/{beneficiario}/individual', 'individual')->name('individual');
    
    Route::get('/masivo', 'masivo')->name('masivo'); 
});

Route::prefix('importaciones')->controller(ImportacionController::class)->name('importaciones.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::get('/{archivo}', 'show')->name('show');
});

// catalogos (no salen en el menu)
Route::resource('grupos', GrupoController::class);
Route::resource('promotores', PromotorController::class);
Route::resource('municipios', MunicipioController::class);
Route::resource('regiones', RegionController::class);

Route::prefix('auditoria')->controller(AuditoriaController::class)->name('auditoria.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{ticket}/pdf', 'pdf')->name('pdf');
});
});
