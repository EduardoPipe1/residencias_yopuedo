<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La bitácora ahora también registra movimientos que no afectan a un solo registro
 * (importaciones, impresiones masivas, listas), por lo que registro_id pasa a ser opcional
 * y se agrega una descripción legible para el buscador y el comprobante.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bitacora_auditoria', function (Blueprint $table) {
            $table->unsignedBigInteger('registro_id')->nullable()->change();
            $table->string('descripcion', 500)->nullable()->after('accion');
            $table->index('created_at');
            $table->index('accion');
        });
    }

    public function down(): void
    {
        Schema::table('bitacora_auditoria', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['accion']);
            $table->dropColumn('descripcion');
        });
    }
};
