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
        'datos_anteriores',
        'datos_nuevos',
    ];
}