<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Corrige promotores.region_id en datos ya importados: php artisan promotores:sincronizar-regiones
Artisan::command('promotores:sincronizar-regiones', function () {
    $n = \App\Services\CatalogoService::sincronizarRegionesPromotores();
    $this->info("Promotores con región corregida: {$n}");
})->purpose('Asigna a cada promotor la región donde tiene más grupos');
