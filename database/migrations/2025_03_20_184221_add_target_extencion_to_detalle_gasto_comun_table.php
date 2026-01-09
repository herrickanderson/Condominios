<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('detalle_gasto_comun', function (Blueprint $table) {
            // Agrega la columna (nullable)
            $table->unsignedBigInteger('target_extencion')->nullable()->after('target_unit');

            // Crea la FK
            $table->foreign('target_extencion')
                  ->references('id_extencion')->on('extenciones')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('detalle_gasto_comun', function (Blueprint $table) {
            $table->dropForeign(['target_extencion']);
            $table->dropColumn('target_extencion');
        });
    }
};
