<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mediciones_consumo', function (Blueprint $table) {
            $table->unsignedBigInteger('id_extencion')->nullable()->after('id_unidad');
            // Definir FK si lo deseas, asumiendo que la tabla extenciones usa "id_extencion" como PK:
            $table->foreign('id_extencion')->references('id_extencion')->on('extenciones')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('mediciones_consumo', function (Blueprint $table) {
            $table->dropForeign(['id_extencion']);
            $table->dropColumn('id_extencion');
        });
    }
};
