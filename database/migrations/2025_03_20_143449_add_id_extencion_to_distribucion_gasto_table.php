<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('distribucion_gasto', function (Blueprint $table) {
            // Columna nullable para extención
            $table->unsignedBigInteger('id_extencion')->nullable()->after('id_unidad');

            // Foreign key opcional
            $table->foreign('id_extencion')
                ->references('id_extencion')->on('extenciones')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('distribucion_gasto', function (Blueprint $table) {
            $table->dropForeign(['id_extencion']);
            $table->dropColumn('id_extencion');
        });
    }
};
