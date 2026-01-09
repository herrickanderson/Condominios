<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('gastos_comunes', function (Blueprint $table) {
            // Se agrega la columna fecha_vencimiento, de tipo date y opcional (nullable)
            $table->date('fecha_vencimiento')->nullable()->after('fecha_fin');
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down()
    {
        Schema::table('gastos_comunes', function (Blueprint $table) {
            $table->dropColumn('fecha_vencimiento');
        });
    }
};
