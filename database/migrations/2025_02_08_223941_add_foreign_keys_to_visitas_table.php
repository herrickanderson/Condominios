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
        Schema::table('visitas', function (Blueprint $table) {
            $table->foreign(['id_unidad'], 'visitas_id_unidad_fkey')->references(['id_unidad'])->on('unidades')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_usuario'], 'visitas_id_usuario_fkey')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitas', function (Blueprint $table) {
            $table->dropForeign('visitas_id_unidad_fkey');
            $table->dropForeign('visitas_id_usuario_fkey');
        });
    }
};
