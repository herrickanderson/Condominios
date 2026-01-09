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
            // Agregamos la columna id_edificio, nullable
            $table->unsignedInteger('id_edificio')->nullable()->after('id_tipo_gasto');

            // Creamos la FK con la tabla edificios
            $table->foreign('id_edificio')
                ->references('id_edificio')
                ->on('edificios')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('detalle_gasto_comun', function (Blueprint $table) {
            // Para revertir, quitamos primero la FK y luego la columna
            $table->dropForeign(['id_edificio']);
            $table->dropColumn('id_edificio');
        });
    }
};
