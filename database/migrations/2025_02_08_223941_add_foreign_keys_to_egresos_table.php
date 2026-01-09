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
        Schema::table('egresos', function (Blueprint $table) {
            $table->foreign(['id_condominio'], 'egresos_id_condominio_fkey')->references(['id_condominio'])->on('condominios')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_proveedor'], 'fk_proveedor')->references(['id_proveedor'])->on('proveedores')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egresos', function (Blueprint $table) {
            $table->dropForeign('egresos_id_condominio_fkey');
            $table->dropForeign('fk_proveedor');
        });
    }
};
