<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_prorrateo', function (Blueprint $table) {
            // Cambiamos la columna id_condominio a nullable
            $table->unsignedBigInteger('id_condominio')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tipo_prorrateo', function (Blueprint $table) {
            // Revertimos el cambio si fuera necesario
            $table->unsignedBigInteger('id_condominio')->nullable(false)->change();
        });
    }
};
