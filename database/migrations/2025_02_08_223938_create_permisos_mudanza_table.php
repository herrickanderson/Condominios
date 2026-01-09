<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permisos_mudanza', function (Blueprint $table) {
            $table->increments('id_permiso');
            $table->integer('id_unidad');
            $table->integer('id_usuario');
            $table->date('fecha_solicitada');
            $table->date('fecha_aprobacion')->nullable();
            $table->text('horario')->nullable();
            $table->boolean('aprobado')->nullable()->default(false);
            $table->text('observaciones')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permisos_mudanza');
    }
};
