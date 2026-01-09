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
        Schema::table('invitados_conferencia', function (Blueprint $table) {
            $table->foreign(['id_conferencia'], 'invitados_conferencia_id_conferencia_fkey')->references(['id_conferencia'])->on('conferencias')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_usuario'], 'invitados_conferencia_id_usuario_fkey')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitados_conferencia', function (Blueprint $table) {
            $table->dropForeign('invitados_conferencia_id_conferencia_fkey');
            $table->dropForeign('invitados_conferencia_id_usuario_fkey');
        });
    }
};
