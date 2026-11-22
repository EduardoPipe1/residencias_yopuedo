<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
      Schema::create('beneficiarios', function (Blueprint $table) {
    $table->id();
    $table->foreignId('grupo_id')->constrained('grupos')->onDelete('restrict');
    $table->string('folio', 50)->unique(); 
    $table->string('curp', 18)->index(); 
    $table->string('nombre', 100);
    $table->string('primer_apellido', 100);
    $table->string('segundo_apellido', 100)->nullable();
    $table->string('estatus', 50)->default('No Acreditado'); 
    $table->year('anio_padron')->nullable();
    $table->date('fecha_termino')->nullable();
    $table->date('fecha_asignacion')->nullable();
    $table->integer('tot_asistencias')->default(0);
    $table->decimal('porc_avance', 5, 2)->default(0.00); 
    $table->text('respuesta_aprendizajes_cambio')->nullable();
    $table->text('observaciones')->nullable();
    $table->timestamps();

    $table->index(['nombre', 'primer_apellido', 'segundo_apellido'], 'idx_busqueda_nombre');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficiarios');
    }
};
