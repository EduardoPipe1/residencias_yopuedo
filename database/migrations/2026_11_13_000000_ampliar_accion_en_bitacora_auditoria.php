<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La columna `accion` era ENUM('INSERT','UPDATE','DELETE'), pero el sistema registra
 * acciones como 'emision_reconocimiento_individual', 'creacion', 'actualizacion'...
 * MySQL las rechazaba y la petición fallaba (p.ej. el PDF del reconocimiento no abría).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bitacora_auditoria', function (Blueprint $table) {
            $table->string('accion', 60)->change();
        });
    }

    public function down(): void
    {
        Schema::table('bitacora_auditoria', function (Blueprint $table) {
            $table->enum('accion', ['INSERT', 'UPDATE', 'DELETE'])->change();
        });
    }
};
