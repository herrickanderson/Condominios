<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detalle_gasto_comun', function (Blueprint $table) {
            // Agrega la columna 'descripcion_detalle' de tipo text (o varchar, según prefieras).
            // Se marca como nullable y se ubica después de 'monto_detalle'.
            $table->text('descripcion_detalle')->nullable()->after('monto_detalle');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detalle_gasto_comun', function (Blueprint $table) {
            // Elimina la columna en caso de rollback
            $table->dropColumn('descripcion_detalle');
        });
    }
};
