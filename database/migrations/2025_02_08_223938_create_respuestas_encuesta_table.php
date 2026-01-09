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
        Schema::create('respuestas_encuesta', function (Blueprint $table) {
            $table->increments('id_respuesta');
            $table->integer('id_pregunta');
            $table->integer('id_usuario');
            $table->text('respuesta_texto');
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respuestas_encuesta');
    }
};
