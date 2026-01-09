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
        Schema::table('reservas', function (Blueprint $table) {
            $table->foreign(['id_instalacion'], 'reservas_id_instalacion_fkey')->references(['id_instalacion'])->on('instalaciones')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_usuario'], 'reservas_id_usuario_fkey')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropForeign('reservas_id_instalacion_fkey');
            $table->dropForeign('reservas_id_usuario_fkey');
        });
    }
};
