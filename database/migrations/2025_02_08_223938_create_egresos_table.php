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
        Schema::create('egresos', function (Blueprint $table) {
            $table->increments('id_egreso');
            $table->integer('id_condominio');
            $table->integer('id_proveedor');
            $table->string('numero_documento', 50);
            $table->date('fecha_documento');
            $table->string('concepto');
            $table->decimal('monto', 12);
            $table->date('fecha_cobro');
            $table->enum('forma_cobro', ['FondoReserva', 'GastoComun', 'Individual', 'SinCobro']);
            $table->enum('estado', ['Ingresado', 'Autorizado', 'Confeccionado', 'Pagado', 'Anulado'])->nullable()->default('Ingresado');
            $table->text('adjunto')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egresos');
    }
};
