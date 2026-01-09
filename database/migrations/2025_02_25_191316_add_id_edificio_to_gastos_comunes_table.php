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
            // Agregamos el campo id_edificio (nullable)
            $table->unsignedInteger('id_edificio')->nullable()->after('id_condominio');

            // Creamos la FK con la tabla edificios (onDelete('set null') o como prefieras)
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
        Schema::table('gastos_comunes', function (Blueprint $table) {
            // Para rollback: primero se elimina la FK y luego la columna
            $table->dropForeign(['id_edificio']);
            $table->dropColumn('id_edificio');
        });
    }
};
