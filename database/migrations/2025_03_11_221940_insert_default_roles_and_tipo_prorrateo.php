<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
     /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        // Insertar datos por defecto en la tabla roles
        DB::table('roles')->insertOrIgnore([
            [
                'id_rol'     => 1,
                'nombre'     => 'SuperAdmin',
                'descripcion'=> 'Super Administrador, crea Administradores y condominios',
            ],
            [
                'id_rol'     => 2,
                'nombre'     => 'Administrador',
                'descripcion'=> 'Gestiona Condominios',
            ],
            [
                'id_rol'     => 3,
                'nombre'     => 'Arrendador',
                'descripcion'=> 'Quien Alquila el departamento',
            ],
            [
                'id_rol'     => 4,
                'nombre'     => 'Propietario',
                'descripcion'=> 'Dueño del departamento',
            ],
            [
                'id_rol'     => 5,
                'nombre'     => 'Vigilancia',
                'descripcion'=> 'Personal a cargo de visualizar estados de Departamentos',
            ],
            [
                'id_rol'     => 6,
                'nombre'     => 'Tecnico de Medicion',
                'descripcion'=> 'Captura de Mediciones de Agua, Luz, gas, etc',
            ]
        ]);

        // Insertar datos por defecto en la tabla tipo_prorrateo
        DB::table('tipo_prorrateo')->insertOrIgnore([
            [
                'id'            => 1,
                'descripcion'   => 'FACT-MT2',
                'id_condominio' => null, // Aquí en vez de 1
            ],
            [
                'id'            => 2,
                'descripcion'   => 'FACT-EQUITATIVO',
                'id_condominio' => null, // Aquí en vez de 1
            ],
        ]);

    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        // Se eliminan los registros insertados por esta migración.
        DB::table('roles')->whereIn('id_rol', [1, 2, 3, 4, 5])->delete();
        DB::table('tipo_prorrateo')->whereIn('id', [1, 2])->delete();
    }
};
