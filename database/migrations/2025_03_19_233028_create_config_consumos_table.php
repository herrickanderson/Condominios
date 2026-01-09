<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('config_consumos', function (Blueprint $table) {
            $table->bigIncrements('id_config');    // Llave primaria
            $table->unsignedBigInteger('id_condominio');
            $table->unsignedBigInteger('id_tipo_gasto');
            $table->decimal('precio', 12, 2);      // Monto a cobrar por unidad de medida (kwh, m3, etc.)
            $table->timestamps();

            // FK a la tabla condominios
            $table->foreign('id_condominio')
                  ->references('id_condominio')
                  ->on('condominios')
                  ->onDelete('cascade');

            // FK a la tabla tipo_gasto_comun
            $table->foreign('id_tipo_gasto')
                  ->references('id_tipo_gasto')
                  ->on('tipo_gasto_comun')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('config_consumos');
    }
};
