<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
