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
        Schema::table('instalaciones', function (Blueprint $table) {
            $table->foreign(['id_condominio'], 'instalaciones_id_condominio_fkey')->references(['id_condominio'])->on('condominios')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instalaciones', function (Blueprint $table) {
            $table->dropForeign('instalaciones_id_condominio_fkey');
        });
    }
};
