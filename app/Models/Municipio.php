<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    protected $fillable = ['region_id', 'nombre'];

    public function region() { return $this->belongsTo(Region::class); }
    public function grupos() { return $this->hasMany(Grupo::class); }

    public function getNombreFormalAttribute(): string
    {
        $menores = ['de', 'del', 'la', 'las', 'los', 'y', 'el'];
        $palabras = explode(' ', mb_convert_case(trim($this->nombre), MB_CASE_LOWER, 'UTF-8'));

        foreach ($palabras as $i => $palabra) {
            if ($i === 0 || !in_array($palabra, $menores, true)) {
                $palabras[$i] = mb_convert_case($palabra, MB_CASE_TITLE, 'UTF-8');
            }
        }
        return implode(' ', $palabras);
    }
}
