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
        Schema::create('registro_ingresos', function (Blueprint $table) {
            $table->id(); // ID autoincremental
            $table->unsignedBigInteger('user_id'); // Relación con usuarios
            $table->timestamp('fecha_hora_ingreso')->default(now()); // Fecha y hora del ingreso
            $table->string('ip')->nullable(); // IP del usuario
            $table->string('navegador')->nullable(); // Navegador del usuario
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_ingresos');
    }
};
