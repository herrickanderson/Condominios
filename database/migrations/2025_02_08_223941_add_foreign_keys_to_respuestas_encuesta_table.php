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
        Schema::table('respuestas_encuesta', function (Blueprint $table) {
            $table->foreign(['id_pregunta'], 'respuestas_encuesta_id_pregunta_fkey')->references(['id_pregunta'])->on('preguntas_encuesta')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_usuario'], 'respuestas_encuesta_id_usuario_fkey')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('respuestas_encuesta', function (Blueprint $table) {
            $table->dropForeign('respuestas_encuesta_id_pregunta_fkey');
            $table->dropForeign('respuestas_encuesta_id_usuario_fkey');
        });
    }
};
