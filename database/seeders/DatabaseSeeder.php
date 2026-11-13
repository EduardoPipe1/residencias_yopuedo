<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Precarga de las 7 Regiones Oficiales
        $regiones = [
            ['id' => 1, 'nombre' => 'REGIÓN I'],
            ['id' => 2, 'nombre' => 'REGIÓN II'],
            ['id' => 3, 'nombre' => 'REGIÓN III'],
            ['id' => 4, 'nombre' => 'REGIÓN IV'],
            ['id' => 5, 'nombre' => 'REGIÓN V'],
            ['id' => 6, 'nombre' => 'REGIÓN VI'],
            ['id' => 7, 'nombre' => 'REGIÓN VII'],
        ];

        foreach ($regiones as $region) {
            DB::table('regiones')->updateOrInsert(['id' => $region['id']], $region);
        }

        // 2. Precarga de los 46 Municipios por Región
        $municipios = [
            // REGIÓN I
            ['region_id' => 1, 'nombre' => 'ATARJEA'],
            ['region_id' => 1, 'nombre' => 'DOCTOR MORA'],
            ['region_id' => 1, 'nombre' => 'SAN JOSÉ ITURBIDE'],
            ['region_id' => 1, 'nombre' => 'SAN LUIS DE LA PAZ'],
            ['region_id' => 1, 'nombre' => 'SANTA CATARINA'],
            ['region_id' => 1, 'nombre' => 'TIERRA BLANCA'],
            ['region_id' => 1, 'nombre' => 'VICTORIA'],
            ['region_id' => 1, 'nombre' => 'XICHÚ'],

            // REGIÓN II
            ['region_id' => 2, 'nombre' => 'DOLORES HIDALGO'],
            ['region_id' => 2, 'nombre' => 'OCAMPO'],
            ['region_id' => 2, 'nombre' => 'SAN DIEGO DE LA UNIÓN'],
            ['region_id' => 2, 'nombre' => 'SAN FELIPE'],
            ['region_id' => 2, 'nombre' => 'SAN MIGUEL DE ALLENDE'],

            // REGIÓN III
            ['region_id' => 3, 'nombre' => 'GUANAJUATO'],
            ['region_id' => 3, 'nombre' => 'IRAPUATO'],
            ['region_id' => 3, 'nombre' => 'PURÍSIMA DEL RINCÓN'],
            ['region_id' => 3, 'nombre' => 'ROMITA'],
            ['region_id' => 3, 'nombre' => 'SAN FRANCISCO DEL RINCÓN'],
            ['region_id' => 3, 'nombre' => 'SALAMANCA'],
            ['region_id' => 3, 'nombre' => 'SILAO DE LA VICTORIA'],

            // REGIÓN IV
            ['region_id' => 4, 'nombre' => 'ABASOLO'],
            ['region_id' => 4, 'nombre' => 'CUERÁMARO'],
            ['region_id' => 4, 'nombre' => 'HUANÍMARO'],
            ['region_id' => 4, 'nombre' => 'MANUEL DOBLADO'],
            ['region_id' => 4, 'nombre' => 'PÉNJAMO'],
            ['region_id' => 4, 'nombre' => 'PUEBLO NUEVO'],
            ['region_id' => 4, 'nombre' => 'VALLE DE SANTIAGO'],

            // REGIÓN V
            ['region_id' => 5, 'nombre' => 'ACÁMBARO'],
            ['region_id' => 5, 'nombre' => 'CORONEO'],
            ['region_id' => 5, 'nombre' => 'JARAL DEL PROGRESO'],
            ['region_id' => 5, 'nombre' => 'JERÉCUARO'],
            ['region_id' => 5, 'nombre' => 'MOROLEÓN'],
            ['region_id' => 5, 'nombre' => 'SALVATIERRA'],
            ['region_id' => 5, 'nombre' => 'SANTIAGO MARAVATÍO'],
            ['region_id' => 5, 'nombre' => 'TARANDACUAO'],
            ['region_id' => 5, 'nombre' => 'URIANGATO'],
            ['region_id' => 5, 'nombre' => 'YURIRIA'],

            // REGIÓN VI
            ['region_id' => 6, 'nombre' => 'APASEO EL ALTO'],
            ['region_id' => 6, 'nombre' => 'APASEO EL GRANDE'],
            ['region_id' => 6, 'nombre' => 'CELAYA'],
            ['region_id' => 6, 'nombre' => 'COMONFORT'],
            ['region_id' => 6, 'nombre' => 'CORTAZAR'],
            ['region_id' => 6, 'nombre' => 'SANTA CRUZ DE JUVENTINO ROSAS'],
            ['region_id' => 6, 'nombre' => 'TARIMORO'],
            ['region_id' => 6, 'nombre' => 'VILLAGRÁN'],

            // REGIÓN VII
            ['region_id' => 7, 'nombre' => 'LEÓN'],
        ];

        foreach ($municipios as $muni) {
            DB::table('municipios')->updateOrInsert(
                ['region_id' => $muni['region_id'], 'nombre' => $muni['nombre']],
                $muni
            );
        }
    }
}