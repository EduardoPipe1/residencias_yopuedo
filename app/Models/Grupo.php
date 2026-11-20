<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $fillable = [
        'promotor_id',
        'municipio_id',
        'identificador_grupo',
        'grupo_inicial',
        'grupo_final',
        'lugar_fecha_evento',
    ];

    public function promotor() { return $this->belongsTo(Promotor::class); }
    public function municipio() { return $this->belongsTo(Municipio::class); }
    public function beneficiarios() { return $this->hasMany(Beneficiario::class); }

    /**
     * Texto de lugar y fecha del reconocimiento, generado automáticamente a partir del
     * municipio del grupo y la fecha de impresión. Ej.: "San Miguel de Allende, Gto., a Noviembre de 2026".
     */
    public function lugarFechaReconocimiento(): string
    {
        $municipio = $this->municipio?->nombre_formal ?? 'Irapuato';
        $mes = ucfirst(now()->locale('es')->translatedFormat('F'));

        return "{$municipio}, Gto., a {$mes} de " . now()->year;
    }
}
