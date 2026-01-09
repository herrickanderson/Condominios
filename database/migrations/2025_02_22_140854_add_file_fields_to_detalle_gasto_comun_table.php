<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('detalle_gasto_comun', function (Blueprint $table) {
            $table->string('nombre_file', 100)->nullable()->after('monto_detalle');
            $table->string('file_url')->nullable()->after('nombre_file');
            $table->text('observacion')->nullable()->after('file_url');
        });
    }

    public function down()
    {
        Schema::table('detalle_gasto_comun', function (Blueprint $table) {
            $table->dropColumn(['nombre_file', 'file_url', 'observacion']);
        });
    }
};
