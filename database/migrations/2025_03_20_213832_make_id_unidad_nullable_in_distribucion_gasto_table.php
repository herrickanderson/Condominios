<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('distribucion_gasto', function (Blueprint $table) {
            // 1) Eliminamos la constraint usando el nombre real
            $table->dropForeign('distribucion_gasto_id_unidad_fkey');

            // 2) Hacemos la columna id_unidad nullable
            $table->integer('id_unidad')->nullable()->change();

            // 3) Volvemos a crear la FK con la sintaxis de Laravel
            $table->foreign('id_unidad')
                  ->references('id_unidad')
                  ->on('unidades')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('distribucion_gasto', function (Blueprint $table) {
            // Revertir: primero quitamos la FK que acabamos de crear
            $table->dropForeign(['id_unidad']);

            // Volvemos a poner la columna como NOT NULL
            $table->integer('id_unidad')->nullable(false)->change();

            // Y recreamos la FK con el nombre original
            $table->foreign('id_unidad', 'distribucion_gasto_id_unidad_fkey')
                  ->references('id_unidad')
                  ->on('unidades')
                  ->onDelete('cascade');
        });
    }
};
