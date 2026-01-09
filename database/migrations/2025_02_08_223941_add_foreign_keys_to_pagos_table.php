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
        Schema::table('pagos', function (Blueprint $table) {
            $table->foreign(['id_gasto'], 'pagos_id_gasto_fkey')->references(['id_gasto'])->on('gastos_comunes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_usuario'], 'pagos_id_usuario_fkey')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign('pagos_id_gasto_fkey');
            $table->dropForeign('pagos_id_usuario_fkey');
        });
    }
};
