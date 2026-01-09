<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distribucion_gasto', function (Blueprint $table) {
            // Se agrega la columna 'fecha_vencimiento' de tipo date.
            // Se coloca después de 'monto_asignado' y se marca como nullable (puede quedar sin valor).
            $table->date('fecha_vencimiento')->nullable()->after('monto_asignado');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distribucion_gasto', function (Blueprint $table) {
            // Se elimina la columna en caso de rollback.
            $table->dropColumn('fecha_vencimiento');
        });
    }
};
