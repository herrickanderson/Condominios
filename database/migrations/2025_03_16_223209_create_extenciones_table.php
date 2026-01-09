<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('extenciones', function (Blueprint $table) {
            $table->id('id_extencion');
            $table->string('nombre', 100);
            $table->string('estado', 255)->default('Activo');
            $table->unsignedBigInteger('id_edificio');
            $table->unsignedBigInteger('id_condominio');
            $table->decimal('area', 10, 2)->default(0);
            $table->string('unidad_medida', 10)->default('mt2');
            $table->string('tipo_extencion', 50); // Por ejemplo, 'Estacionamiento' o 'Bodega'
            $table->integer('cobro_unico')->default(0); // 0 o 1, aplica para Estacionamiento
            $table->timestamps();

            // Relaciones foráneas (ajusta según tus tablas)
            $table->foreign('id_condominio')->references('id_condominio')->on('condominios')->onDelete('cascade');
            $table->foreign('id_edificio')->references('id_edificio')->on('edificios')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('extenciones');
    }
};
