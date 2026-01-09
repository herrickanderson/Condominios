<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('extenciones_usuarios', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('id_extencion');
            $table->timestamps();

            // Definimos la clave primaria compuesta
            $table->primary(['user_id', 'id_extencion']);

            // Relaciones foráneas
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_extencion')->references('id_extencion')->on('extenciones')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('extenciones_usuarios');
    }
};
