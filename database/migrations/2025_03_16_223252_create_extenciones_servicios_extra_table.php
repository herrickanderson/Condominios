<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('extenciones_servicios_extra', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_extencion');
            $table->unsignedBigInteger('id_tipo_gasto');
            $table->decimal('porcentaje_extra', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('id_extencion')->references('id_extencion')->on('extenciones')->onDelete('cascade');
            $table->foreign('id_tipo_gasto')->references('id_tipo_gasto')->on('tipo_gasto_comun')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('extenciones_servicios_extra');
    }
};
