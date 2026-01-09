<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('conf_mora', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_condominio');
            // Tipo de periodo para la mora: basado en la configuración del período (diario, semanal o mensual)
            $table->enum('tipo_periodo', ['diario', 'semanal', 'mensual'])->default('mensual');
            // Porcentaje de mora (por ejemplo, 1.50 para 1.50%)
            $table->decimal('porcentaje', 5, 2);
            $table->timestamps();

            $table->foreign('id_condominio')
                  ->references('id_condominio')
                  ->on('condominios')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('conf_mora');
    }
};
