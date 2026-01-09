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
    Schema::create('conf_pagos', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('id_condominio');
        $table->string('banco');
        $table->string('tipo_cuenta');
        $table->string('numero_cuenta');
        $table->string('cci')->nullable();
        $table->string('propietario');
        $table->string('telefono')->nullable();
        $table->string('direccion')->nullable();
        $table->text('observaciones')->nullable();
        $table->string('qr_path')->nullable(); // ruta en S3
        $table->boolean('activo')->default(true);
        $table->timestamps();

        $table->foreign('id_condominio')->references('id_condominio')->on('condominios')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conf_pagos');
    }
};
