<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('edificios', function (Blueprint $table) {
            // Campo para marcar si se aplica prorrateo individual: 0 = no, 1 = sí
            $table->integer('aplica_prorrateo')->default(0)
                  ->comment('0: usa prorrateo del condominio, 1: aplica prorrateo individual');

            // Campo para guardar el id del tipo prorrateo (puede ser nulo)
            $table->integer('tipo_prorrateo_id')->nullable()
                  ->comment('Si aplica_prorrateo es 1, este campo almacena el id del tipo de prorrateo');
        });
    }

    public function down()
    {
        Schema::table('edificios', function (Blueprint $table) {
            $table->dropColumn('aplica_prorrateo');
            $table->dropColumn('tipo_prorrateo_id');
        });
    }
};
