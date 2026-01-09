<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('archivo')->nullable(); // Ruta al archivo (PDF, imagen, etc.)
            $table->string('nombre_archivo')->nullable(); // Nombre del archivo
            $table->string('estado')->default('pendiente'); // Estado del pago
            $table->text('observacion')->nullable(); // Observaciones adicionales
        });
    }

    public function down()
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn('archivo');
            $table->dropColumn('nombre_archivo');
            $table->dropColumn('estado');
            $table->dropColumn('observacion');
        });
    }
};
