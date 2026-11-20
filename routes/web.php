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

// Página de bienvenida (pública): botón para iniciar sesión o entrar al sistema
Route::view('/', 'dashboard')->name('home');

// Inicio de sesión
Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:10,1']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Todo el sistema requiere sesión iniciada
Route::middleware('auth')->group(function () {


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

// Módulo LISTAS (Lista de reconocimientos oficial: acta de entrega por grupo)
Route::prefix('listas')->controller(ListaController::class)->name('listas.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/generar', 'generar')->name('generar');
});

// Módulo PROMOTORES Y GRUPOS (consulta de asignación de grupos por promotor)
Route::prefix('promotores-grupos')->controller(AsignacionGrupoController::class)->name('asignaciones.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/pdf', 'pdf')->name('pdf');
});

// Módulo RECONOCIMIENTOS (Diplomas institucionales)
Route::prefix('reconocimientos')->controller(ReconocimientoController::class)->name('reconocimientos.')->group(function () {
    Route::get('/', 'index')->name('index');
    
    // --- RUTAS API PARA SELECTS DEPENDIENTES (Deben ir antes de las rutas con parámetros dinámicos) ---
    Route::get('/api/promotores/{regionId}', 'obtenerPromotoresPorRegion')->name('api.promotores');
    Route::get('/api/grupos/region/{regionId}', 'obtenerGruposPorRegion')->name('api.grupos.region');
    Route::get('/api/grupos/promotor/{promotorId}', 'obtenerGruposPorPromotor')->name('api.grupos.promotor');
    
    // Rutas de emisión
    Route::get('/{beneficiario}/individual', 'individual')->name('individual');
    
    // Se cambia a GET porque el <form> de HTML hace la petición por GET para los filtros en la URL
    Route::get('/masivo', 'masivo')->name('masivo'); 
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

// Módulo BITÁCORA (comprobantes de cada movimiento)
Route::prefix('auditoria')->controller(AuditoriaController::class)->name('auditoria.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{ticket}/pdf', 'pdf')->name('pdf');
});

});
