<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tipo_gasto_comun', function (Blueprint $table) {
            $table->unsignedBigInteger('id_categoria')->nullable()->after('aplica_a_todos_edificios');
            $table->foreign('id_categoria')
                ->references('id_categoria')
                ->on('categoria_gasto_comun')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('tipo_gasto_comun', function (Blueprint $table) {
            $table->dropForeign(['id_categoria']);
            $table->dropColumn('id_categoria');
        });
    }
};
