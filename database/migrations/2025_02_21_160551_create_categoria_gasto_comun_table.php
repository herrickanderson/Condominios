<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriaGastoComunTable extends Migration
{
    public function up()
    {
        Schema::create('categoria_gasto_comun', function (Blueprint $table) {
            $table->id('id_categoria');
            $table->string('nombre', 50);
            // Relación con condominio para que cada categoría se asocie a un condominio
            $table->unsignedBigInteger('id_condominio');
            $table->timestamps();

            $table->foreign('id_condominio')
                  ->references('id_condominio')
                  ->on('condominios')
                  ->onDelete('cascade'); // Si se elimina el condominio, se eliminan sus categorías
        });
    }

    public function down()
    {
        Schema::dropIfExists('categoria_gasto_comun');
    }
}
