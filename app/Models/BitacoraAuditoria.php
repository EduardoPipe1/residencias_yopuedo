<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BitacoraAuditoria extends Model
{
    protected $table = 'bitacora_auditoria';

    // Desactiva únicamente updated_at conservando created_at de forma automática
    const UPDATED_AT = null;

    protected $fillable = [
        'ticket_id',
        'usuario_responsable',
        'tabla_afectada',
        'registro_id',
        'accion',
        'descripcion',
        'datos_anteriores',
        'datos_nuevos',
    ];

    // Los JSON se guardan como texto con acentos legibles (JSON_UNESCAPED_UNICODE) para que el
    // buscador encuentre nombres; por eso se decodifican aquí en lugar de usar el cast 'array'.
    protected $casts = ['created_at' => 'datetime'];

    public function nuevos(): array
    {
        return json_decode($this->datos_nuevos ?? 'null', true) ?? [];
    }

    public function anteriores(): array
    {
        return json_decode($this->datos_anteriores ?? 'null', true) ?? [];
    }

    public function getAccionLegibleAttribute(): string
    {
        return \App\Services\AuditoriaService::ACCIONES[$this->accion] ?? $this->accion;
    }
}