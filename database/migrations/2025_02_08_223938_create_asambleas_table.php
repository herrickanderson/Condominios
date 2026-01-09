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
        Schema::create('asambleas', function (Blueprint $table) {
            $table->increments('id_asamblea');
            $table->integer('id_condominio');
            $table->date('fecha');
            $table->enum('tipo_asamblea', ['Ordinaria', 'Extraordinaria', 'Comite']);
            $table->text('documento_acta')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asambleas');
    }
};
