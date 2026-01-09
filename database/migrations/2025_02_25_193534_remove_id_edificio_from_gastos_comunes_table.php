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
            // Verificamos si la columna existe antes de intentar eliminarla
            if (Schema::hasColumn('gastos_comunes', 'id_edificio')) {
                // Primero quitamos la FK si existe
                $table->dropForeign(['id_edificio']);
                // Luego removemos la columna
                $table->dropColumn('id_edificio');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('gastos_comunes', function (Blueprint $table) {
            // Si quisieras revertirlo, volverías a crear la columna
            // (Solo si realmente quieres soportar rollback)
            $table->unsignedInteger('id_edificio')->nullable()->after('id_condominio');
            $table->foreign('id_edificio')
                ->references('id_edificio')
                ->on('edificios')
                ->onDelete('set null');
        });
    }
};
