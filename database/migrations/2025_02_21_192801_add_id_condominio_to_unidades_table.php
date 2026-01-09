<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('unidades', function (Blueprint $table) {
            // Agregamos la columna id_condominio; el tipo se define según la PK de condominios (normalmente unsignedBigInteger)
            $table->unsignedBigInteger('id_condominio')->after('id_edificio');

            // Definimos la clave foránea que referencia a la tabla condominios
            $table->foreign('id_condominio')
                  ->references('id_condominio')
                  ->on('condominios')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->dropForeign(['id_condominio']);
            $table->dropColumn('id_condominio');
        });
    }
};
