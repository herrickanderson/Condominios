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
        Schema::table('detalle_gasto_comun', function (Blueprint $table) {
            $table->foreign(['id_gasto'], 'detalle_gasto_comun_id_gasto_fkey')->references(['id_gasto'])->on('gastos_comunes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_tipo_gasto'], 'detalle_gasto_comun_id_tipo_gasto_fkey')->references(['id_tipo_gasto'])->on('tipo_gasto_comun')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_gasto_comun', function (Blueprint $table) {
            $table->dropForeign('detalle_gasto_comun_id_gasto_fkey');
            $table->dropForeign('detalle_gasto_comun_id_tipo_gasto_fkey');
        });
    }
};
