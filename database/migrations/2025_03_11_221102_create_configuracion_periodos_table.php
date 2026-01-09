<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('configuracion_periodos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idcondominio');
            $table->integer('dia_inicio');
            $table->integer('dia_fin');
            $table->integer('dia_vencimiento');
            $table->integer('estado')->default(1); // 1: activo, 0: inactivo
            $table->timestamps();

            // Relación con la tabla 'condominios'
            $table->foreign('idcondominio')
                ->references('id_condominio')
                ->on('condominios')
                ->onDelete('cascade');
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_periodos');
    }
};
