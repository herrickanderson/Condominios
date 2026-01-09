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
        Schema::create('distribucion_gasto', function (Blueprint $table) {
            $table->increments('id_distribucion');
            $table->integer('id_detalle');
            $table->integer('id_unidad');
            $table->decimal('monto_asignado', 12);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribucion_gasto');
    }
};
