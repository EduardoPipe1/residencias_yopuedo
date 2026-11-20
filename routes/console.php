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

// Crea o actualiza el usuario del sistema: php artisan usuario:crear
Artisan::command('usuario:crear', function () {
    $nombre = $this->ask('Nombre completo (aparecerá en la bitácora)');
    $correo = $this->ask('Correo para iniciar sesión');
    $clave  = $this->secret('Contraseña (mínimo 8 caracteres)');

    if (!$nombre || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen((string) $clave) < 8) {
        $this->error('Datos inválidos: revisa el nombre, el correo y que la contraseña tenga al menos 8 caracteres.');
        return 1;
    }

    \App\Models\User::updateOrCreate(
        ['email' => $correo],
        ['name' => $nombre, 'password' => \Illuminate\Support\Facades\Hash::make($clave)]
    );
    $this->info("Usuario {$correo} listo. Ya puedes iniciar sesión.");
})->purpose('Crea o actualiza el usuario que inicia sesión en el sistema');
