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
            // Agregamos la columna tipo_moneda, de tipo string, con valores "Soles" o "Dolares" y valor por defecto "Soles".
            $table->string('tipo_moneda')->default('Soles')->after('tipo_cobro');
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down()
    {
        Schema::table('gastos_comunes', function (Blueprint $table) {
            $table->dropColumn('tipo_moneda');
        });
    }

};
