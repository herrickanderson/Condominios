<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mediciones_consumo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_condominio');
            $table->unsignedBigInteger('id_unidad');
            $table->unsignedBigInteger('id_tipo_gasto');
            $table->date('fecha_medicion');
            $table->decimal('lectura_anterior', 12, 2)->nullable();
            $table->decimal('lectura_actual', 12, 2);
            // Puedes optar por calcular el consumo en la aplicación o, si tu versión de PostgreSQL lo permite, como columna generada.
            $table->decimal('consumo', 12, 2)->nullable();
            $table->unsignedBigInteger('id_user');
            $table->string('status')->default('activo');
            $table->text('observacion')->nullable();
            $table->timestamps();

            // Definir claves foráneas
            $table->foreign('id_condominio')->references('id_condominio')->on('condominios')->onDelete('cascade');
            $table->foreign('id_unidad')->references('id_unidad')->on('unidades')->onDelete('cascade');
            $table->foreign('id_tipo_gasto')->references('id_tipo_gasto')->on('tipo_gasto_comun')->onDelete('cascade');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mediciones_consumo');
    }
};
