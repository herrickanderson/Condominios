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
        Schema::table('preguntas_encuesta', function (Blueprint $table) {
            $table->foreign(['id_encuesta'], 'preguntas_encuesta_id_encuesta_fkey')->references(['id_encuesta'])->on('encuestas')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preguntas_encuesta', function (Blueprint $table) {
            $table->dropForeign('preguntas_encuesta_id_encuesta_fkey');
        });
    }
};
