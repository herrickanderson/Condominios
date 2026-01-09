<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('id_unidad')->nullable()->after('id_condominio');
            $table->foreign('id_unidad')
                  ->references('id_unidad')
                  ->on('unidades')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_unidad']);
            $table->dropColumn('id_unidad');
        });
    }
};
