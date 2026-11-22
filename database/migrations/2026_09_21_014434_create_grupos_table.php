<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
      Schema::create('grupos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('promotor_id')->constrained('promotores')->onDelete('restrict');
    $table->foreignId('municipio_id')->constrained('municipios')->onDelete('restrict');
    $table->string('identificador_grupo', 100)->unique();
    $table->integer('grupo_inicial')->nullable();
    $table->integer('grupo_final')->nullable();
    $table->string('lugar_fecha_evento', 255)->nullable();
    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
