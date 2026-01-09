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
        Schema::create('detalle_gasto_comun', function (Blueprint $table) {
            $table->increments('id_detalle');
            $table->integer('id_gasto');
            $table->integer('id_tipo_gasto');
            $table->decimal('monto_detalle', 12);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_gasto_comun');
    }
};
