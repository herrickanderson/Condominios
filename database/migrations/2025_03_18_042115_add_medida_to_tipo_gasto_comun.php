<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('tipo_gasto_comun', function (Blueprint $table) {
            $table->string('medida', 50)
                  ->nullable()
                  ->after('consumo')
                  ->comment('Unidad de medida (ej: Litros, KWh, m3)');
        });
    }

    /**
     * Revierte la migración.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('tipo_gasto_comun', function (Blueprint $table) {
            $table->dropColumn('medida');
        });
    }
};
