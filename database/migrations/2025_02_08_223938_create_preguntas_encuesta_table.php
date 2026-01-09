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
        Schema::create('preguntas_encuesta', function (Blueprint $table) {
            $table->increments('id_pregunta');
            $table->integer('id_encuesta');
            $table->text('texto');
            $table->enum('tipo_respuesta', ['Unica', 'Multiple']);
            $table->boolean('obligatoria')->nullable()->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preguntas_encuesta');
    }
};
