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
        Schema::create('unidades', function (Blueprint $table) {
            $table->increments('id_unidad');
            $table->string('nombre_unidad', 100);
            $table->enum('tipo_unidad', ['Departamento', 'Estacionamiento', 'Bodega', 'Local'])->nullable();
            $table->enum('prorrateo_tipo', ['Lineal', 'Por_Mt2', 'Por_Porcentaje'])->nullable();
            $table->decimal('prorrateo_valor', 8, 6)->nullable()->default(0);
            $table->enum('estado', ['Activo', 'Inactivo', 'En_Mantenimiento'])->nullable()->default('Activo');
            $table->integer('id_edificio');
            $table->integer('id_asignacion_padre')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};
