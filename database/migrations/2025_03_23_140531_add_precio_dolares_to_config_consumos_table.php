<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('config_consumos', function (Blueprint $table) {
            $table->decimal('precio_dolares', 8, 2)->after('precio')->default(0);
        });
    }

    public function down()
    {
        Schema::table('config_consumos', function (Blueprint $table) {
            $table->dropColumn('precio_dolares');
        });
    }
};
