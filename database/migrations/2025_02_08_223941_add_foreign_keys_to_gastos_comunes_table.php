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
        Schema::table('gastos_comunes', function (Blueprint $table) {
            $table->foreign(['id_condominio'], 'gastos_comunes_id_condominio_fkey')->references(['id_condominio'])->on('condominios')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gastos_comunes', function (Blueprint $table) {
            $table->dropForeign('gastos_comunes_id_condominio_fkey');
        });
    }
};
