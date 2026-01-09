<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tipo_prorrateo_condominios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_tipo_prorrateo');
            $table->unsignedBigInteger('id_condominio');
            $table->timestamps();

            $table->foreign('id_tipo_prorrateo')
                ->references('id')
                ->on('tipo_prorrateo')
                ->onDelete('cascade');

            $table->foreign('id_condominio')
                ->references('id_condominio')
                ->on('condominios')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tipo_prorrateo_condominios');
    }
};
