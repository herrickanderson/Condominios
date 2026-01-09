<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->increments('id_reserva');
            $table->integer('id_instalacion');
            $table->integer('id_usuario');
            $table->timestamp('fecha_hora_reserva');
            $table->string('duracion');
            $table->enum('estado', ['Reservado', 'Cancelado', 'Utilizado'])->nullable()->default('Reservado');
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
