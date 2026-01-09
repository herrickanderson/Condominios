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
        Schema::table('incidencias', function (Blueprint $table) {
            $table->foreign(['id_usuario_destino'], 'incidencias_id_usuario_destino_fkey')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_usuario_origen'], 'incidencias_id_usuario_origen_fkey')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropForeign('incidencias_id_usuario_destino_fkey');
            $table->dropForeign('incidencias_id_usuario_origen_fkey');
        });
    }
};
