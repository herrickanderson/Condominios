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
        Schema::create('tipo_gasto_comun', function (Blueprint $table) {
            $table->increments('id_tipo_gasto');
            $table->string('nombre', 50);
            $table->boolean('aplica_a_todos_edificios')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_gasto_comun');
    }
};
