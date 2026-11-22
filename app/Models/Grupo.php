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

    // lugar y fecha que sale en el reconocimiento
    public function lugarFechaReconocimiento(): string
    {
        $municipio = $this->municipio?->nombre_formal ?? 'Irapuato';
        $mes = ucfirst(now()->locale('es')->translatedFormat('F'));

        return "{$municipio}, Gto., a {$mes} de " . now()->year;
    }
}
