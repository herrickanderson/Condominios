<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tipo_gasto_comun', function (Blueprint $table) {
            // Campo para indicar si se usará el prorrateo del condominio (0) o gasto individual (1)
            $table->integer('aplica_prorrateo_condominio')->default(0)
                  ->comment('0: usa prorrateo del condominio, 1: gasto individual');
            // Campo para guardar el id del tipo de gasto individual, si corresponde
            $table->integer('tipo_prorrateo_id')->nullable()
                  ->comment('Si aplica_prorrateo_condominio es 1, almacena el id del tipo de gasto individual');
        });
    }

    public function down()
    {
        Schema::table('tipo_gasto_comun', function (Blueprint $table) {
            $table->dropColumn('aplica_prorrateo_condominio');
            $table->dropColumn('tipo_prorrateo_id');
        });
    }
};
