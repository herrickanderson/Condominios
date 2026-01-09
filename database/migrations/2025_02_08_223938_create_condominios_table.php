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
        Schema::create('condominios', function (Blueprint $table) {
            $table->increments('id_condominio');
            $table->string('nombre', 100);
            $table->string('rut', 20);
            $table->string('direccion', 200);
            $table->string('telefono', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('logo')->nullable();
            $table->text('firma')->nullable();
            $table->date('fecha_contable_inicial')->nullable();
            $table->decimal('fondo_reserva', 12)->nullable();
            $table->text('datos_bancarios')->nullable();
            $table->string('mandato_khipu', 50)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('condominios');
    }
};
