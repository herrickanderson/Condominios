<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{ public function up()
    {
        Schema::table('detalle_gasto_comun', function (Blueprint $table) {
            // Añadimos una columna 'source' (puedes llamarla 'origen' o 'created_by')
            // y le damos un valor por defecto 'manual'.
            $table->string('source')->default('manual')->after('descripcion_detalle');
        });
    }

    public function down()
    {
        Schema::table('detalle_gasto_comun', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
