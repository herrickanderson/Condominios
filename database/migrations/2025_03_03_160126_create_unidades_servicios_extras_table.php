<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades_servicios_extras', function (Blueprint $table) {
            $table->id();

            // FK a la tabla 'unidades'
            // ->nullable() si deseas permitir que sea nulo
            $table->unsignedBigInteger('id_unidad')->nullable();

            // FK a la tabla 'tipo_gasto_comun'
            $table->unsignedBigInteger('id_tipo_gasto')->nullable();

            // Campo para guardar el porcentaje extra
            $table->decimal('porcentaje_extra', 5, 2)->nullable();

            // Campos automáticos de Laravel (created_at y updated_at)
            $table->timestamps();

            // Definimos las foreign keys
            $table->foreign('id_unidad')
                  ->references('id_unidad')
                  ->on('unidades')
                  ->onDelete('cascade'); // o set null, según tu lógica

            $table->foreign('id_tipo_gasto')
                  ->references('id_tipo_gasto')
                  ->on('tipo_gasto_comun')
                  ->onDelete('cascade'); // o set null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidades_servicios_extras');
    }
};
