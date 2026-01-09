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
        Schema::create('prorrateo_valores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_prorrateo_id');
            $table->unsignedBigInteger('user_id'); // o el id correspondiente
            $table->unsignedBigInteger('id_condominio'); // si es necesario
            $table->string('criterio', 50);
            $table->decimal('valor_criterio', 8, 2);
            $table->timestamps();

            // Clave foránea para tipo_prorrateo
            $table->foreign('tipo_prorrateo_id')
                ->references('id')
                ->on('tipo_prorrateo')
                ->onDelete('cascade');

            // Clave foránea para usuarios (tabla 'users')
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Clave foránea para condominio (referenciando 'id_condominio' en la tabla 'condominios')
            $table->foreign('id_condominio')
                ->references('id_condominio')
                ->on('condominios')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('prorrateo_valores');
    }
};
