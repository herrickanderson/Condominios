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
        Schema::table('usuario_roles', function (Blueprint $table) {
            $table->foreign(['id_rol'], 'usuario_roles_id_rol_fkey')->references(['id_rol'])->on('roles')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_usuario'], 'usuario_roles_id_usuario_fkey')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuario_roles', function (Blueprint $table) {
            $table->dropForeign('usuario_roles_id_rol_fkey');
            $table->dropForeign('usuario_roles_id_usuario_fkey');
        });
    }
};
