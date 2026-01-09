<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->dropColumn('id_asignacion_padre');
        });
    }

    public function down()
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->integer('id_asignacion_padre')->nullable();
        });
    }
};
