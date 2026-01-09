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
        Schema::table('permisos_mudanza', function (Blueprint $table) {
            $table->foreign(['id_unidad'], 'permisos_mudanza_id_unidad_fkey')->references(['id_unidad'])->on('unidades')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_usuario'], 'permisos_mudanza_id_usuario_fkey')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permisos_mudanza', function (Blueprint $table) {
            $table->dropForeign('permisos_mudanza_id_unidad_fkey');
            $table->dropForeign('permisos_mudanza_id_usuario_fkey');
        });
    }
};
