<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tipo_prorrateo_condominios', function (Blueprint $table) {
            $table->integer('estado')->default(1)->after('id_condominio');
        });
    }

    public function down()
    {
        Schema::table('tipo_prorrateo_condominios', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
