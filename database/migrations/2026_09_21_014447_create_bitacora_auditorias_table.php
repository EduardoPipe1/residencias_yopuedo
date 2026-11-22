<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
      Schema::create('bitacora_auditoria', function (Blueprint $table) {
    $table->id();
    $table->string('ticket_id', 50)->unique();
    $table->string('usuario_responsable', 100);
    $table->string('tabla_afectada', 50);
    $table->unsignedBigInteger('registro_id');
    $table->enum('accion', ['INSERT', 'UPDATE', 'DELETE']);
    $table->json('datos_anteriores')->nullable();
    $table->json('datos_nuevos')->nullable();
    $table->timestamp('created_at')->useCurrent();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora_auditorias');
    }
};
