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
        Schema::create('pagos', function (Blueprint $table) {
            $table->increments('id_pago');
            $table->integer('id_gasto');
            $table->integer('id_usuario');
            $table->decimal('monto', 12);
            $table->date('fecha_pago');
            $table->enum('metodo_pago', ['Webpay', 'Khipu', 'Efectivo', 'Transferencia']);
            $table->string('referencia', 50)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
