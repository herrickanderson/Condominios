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
        Schema::table('detalle_gasto_comun', function (Blueprint $table) {
            // Campo para indicar el ámbito de distribución: 'condominium', 'tower' o 'unit'
            $table->string('distribution_scope', 20)
                ->default('condominium')
                ->comment('Ambito de distribución: condominium, tower o unit')
                ->after('descripcion_detalle');

            // Campo para guardar el ID del edificio (torre) si el ámbito es "tower"
            $table->unsignedInteger('target_tower')
                ->nullable()
                ->after('distribution_scope');

            // Campo para guardar el ID de la unidad si el ámbito es "unit"
            $table->unsignedInteger('target_unit')
                ->nullable()
                ->after('target_tower');

            // Agregamos las claves foráneas (si ya tienes las tablas correspondientes)
            $table->foreign('target_tower')
                ->references('id_edificio')
                ->on('edificios')
                ->onDelete('set null');

            $table->foreign('target_unit')
                ->references('id_unidad')
                ->on('unidades')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('detalle_gasto_comun', function (Blueprint $table) {
            $table->dropForeign(['target_tower']);
            $table->dropForeign(['target_unit']);
            $table->dropColumn('target_unit');
            $table->dropColumn('target_tower');
            $table->dropColumn('distribution_scope');
        });
    }
};
