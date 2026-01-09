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
        Schema::create('invitados_conferencia', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_conferencia');
            $table->integer('id_usuario');
            $table->enum('estado_invitacion', ['Enviado', 'Aceptado', 'Rechazado'])->nullable()->default('Enviado');
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitados_conferencia');
    }
};
