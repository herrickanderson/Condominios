<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('unidades', function (Blueprint $table) {
            // Agregamos el campo 'area' para almacenar los metros cuadrados.
            $table->decimal('area', 10, 2)->default(0)->after('id_edificio');
            // Agregamos el campo 'unidad_medida', con valor por defecto 'mt2'
            $table->string('unidad_medida', 10)->default('mt2')->after('area');
        });
    }

    public function down()
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->dropColumn(['area', 'unidad_medida']);
        });
    }
};
