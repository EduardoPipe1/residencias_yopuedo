<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('promotores', function (Blueprint $table) {
    $table->id();
    $table->foreignId('region_id')->constrained('regiones')->onDelete('restrict');
    $table->string('nombre_completo', 200);
    $table->enum('estatus', ['Activo', 'Inactivo'])->default('Activo');
    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('promotores');
    }
};
