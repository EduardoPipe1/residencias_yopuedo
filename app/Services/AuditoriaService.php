<?php

namespace App\Services;

use App\Models\BitacoraAuditoria;
use Illuminate\Support\Str;

class AuditoriaService
{
    public const ACCIONES = [
        'inicio_sesion'                     => 'Inicio de sesión',
        'cierre_sesion'                     => 'Cierre de sesión',
        'importacion_padron'                => 'Importación de padrón',
        'emision_reconocimiento_individual' => 'Reconocimiento individual',
        'emision_reconocimiento_masivo'     => 'Reconocimientos masivos',
        'emision_lista_reconocimientos'     => 'Lista de reconocimientos',
        'emision_asignacion_grupos'         => 'Asignación de grupos',
        'creacion'                          => 'Alta de registro',
        'actualizacion'                     => 'Actualización de registro',
        'eliminacion'                       => 'Eliminación de registro',
    ];

    // guarda un movimiento en la bitacora
    public static function registrar($tabla, $registroId, $accion, $anteriores = null, $nuevos = null, ?string $descripcion = null): BitacoraAuditoria
    {
        return BitacoraAuditoria::create([
            'ticket_id'           => 'TCK-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(4)),
            'usuario_responsable' => auth()->user()->name ?? 'Sistema',
            'tabla_afectada'      => $tabla,
            'registro_id'         => $registroId,
            'accion'              => $accion,
            'descripcion'         => $descripcion ? Str::limit($descripcion, 495) : null,
            'datos_anteriores'    => $anteriores === null ? null : json_encode($anteriores, JSON_UNESCAPED_UNICODE),
            'datos_nuevos'        => $nuevos === null ? null : json_encode($nuevos, JSON_UNESCAPED_UNICODE),
        ]);
    }

    // guarda una impresion con la lista de beneficiarios
    public static function registrarImpresion(string $accion, string $descripcion, array $filtros, $beneficiarios): BitacoraAuditoria
    {
        $detalle = $beneficiarios->map(fn ($b) => [
            'folio'     => 'IDSE' . $b->folio,
            'nombre'    => trim("{$b->nombre} {$b->primer_apellido} {$b->segundo_apellido}"),
            'grupo'     => $b->grupo->identificador_grupo ?? '',
            'municipio' => $b->grupo->municipio->nombre ?? '',
            'estatus'   => $b->estatus,
        ])->values()->all();

        return self::registrar('beneficiarios', $beneficiarios->count() === 1 ? $beneficiarios->first()->id : null, $accion, null, [
            'filtros'        => $filtros,
            'total'          => count($detalle),
            'beneficiarios'  => $detalle,
        ], $descripcion);
    }
}
