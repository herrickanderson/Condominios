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
        Schema::table('users', function (Blueprint $table) {
            // Aquí agregamos los campos luego de 'rut', o donde prefieras.
            $table->string('tipo_documento', 50)->nullable()->after('rut');
            $table->string('numero_documento', 50)->nullable()->after('tipo_documento');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tipo_documento');
            $table->dropColumn('numero_documento');
        });
    }
};
