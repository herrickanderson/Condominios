<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->text('observacion_admin')->nullable()->after('observacion'); // Nueva columna
        });
    }

    public function down()
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn('observacion_admin');
        });
    }
};

