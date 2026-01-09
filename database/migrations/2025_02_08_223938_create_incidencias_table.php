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
        Schema::create('incidencias', function (Blueprint $table) {
            $table->increments('id_incidencia');
            $table->integer('id_usuario_origen');
            $table->integer('id_usuario_destino');
            $table->enum('tipo_mensaje', ['Peticion', 'Queja', 'Reclamo', 'Sugerencia', 'Felicitacion']);
            $table->text('descripcion');
            $table->enum('estado', ['Nueva', 'EnProceso', 'Finalizada'])->nullable()->default('Nueva');
            $table->timestamp('fecha_registro')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};
