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
        Schema::create('gastos_comunes', function (Blueprint $table) {
            $table->increments('id_gasto');
            $table->integer('id_condominio');
            $table->string('descripcion');
            $table->decimal('monto_total', 12);
            $table->date('fecha_periodo');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->enum('estado_pago', ['Pendiente', 'Pagado', 'Conciliado'])->nullable()->default('Pendiente');
            $table->enum('tipo_cobro', ['General', 'Individual', 'Alicuota'])->nullable()->default('General');
            $table->integer('id_tipo_prorrateo')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos_comunes');
    }
};
