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
        Schema::table('tipo_gasto_comun', function (Blueprint $table) {
            // Agrega la columna "consumo" con valor por defecto 1
            $table->integer('consumo')->default(1)->comment('0: Inactivo, 1: Activo');
        });

        // Agrega el check constraint para que "consumo" solo admita 0 o 1 (para PostgreSQL)
        DB::statement("ALTER TABLE tipo_gasto_comun ADD CONSTRAINT consumo_check CHECK (consumo IN (0, 1));");
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('tipo_gasto_comun', function (Blueprint $table) {
            $table->dropColumn('consumo');
        });
    }
};
