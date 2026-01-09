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
        Schema::table('historial_incidencias', function (Blueprint $table) {
            $table->foreign(['id_incidencia'], 'historial_incidencias_id_incidencia_fkey')->references(['id_incidencia'])->on('incidencias')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['remitente'], 'historial_incidencias_remitente_fkey')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historial_incidencias', function (Blueprint $table) {
            $table->dropForeign('historial_incidencias_id_incidencia_fkey');
            $table->dropForeign('historial_incidencias_remitente_fkey');
        });
    }
};
