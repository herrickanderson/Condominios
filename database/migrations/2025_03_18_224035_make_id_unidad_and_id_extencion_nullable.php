<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mediciones_consumo', function (Blueprint $table) {
            $table->unsignedBigInteger('id_unidad')->nullable()->change();
            $table->unsignedBigInteger('id_extencion')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('mediciones_consumo', function (Blueprint $table) {
            $table->unsignedBigInteger('id_unidad')->nullable(false)->change();
            $table->unsignedBigInteger('id_extencion')->nullable(false)->change();
        });
    }

};
