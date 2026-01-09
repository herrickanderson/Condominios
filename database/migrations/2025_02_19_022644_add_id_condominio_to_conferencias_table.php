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
        Schema::table('conferencias', function (Blueprint $table) {
            $table->unsignedInteger('id_condominio')->after('id_moderador')->nullable();
            $table->foreign('id_condominio')
                  ->references('id_condominio')
                  ->on('condominios')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('conferencias', function (Blueprint $table) {
            $table->dropForeign(['id_condominio']);
            $table->dropColumn('id_condominio');
        });
    }
};
