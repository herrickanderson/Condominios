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
        Schema::table('distribucion_gasto', function (Blueprint $table) {
            $table->foreign(['id_detalle'], 'distribucion_gasto_id_detalle_fkey')->references(['id_detalle'])->on('detalle_gasto_comun')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_unidad'], 'distribucion_gasto_id_unidad_fkey')->references(['id_unidad'])->on('unidades')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distribucion_gasto', function (Blueprint $table) {
            $table->dropForeign('distribucion_gasto_id_detalle_fkey');
            $table->dropForeign('distribucion_gasto_id_unidad_fkey');
        });
    }
};
