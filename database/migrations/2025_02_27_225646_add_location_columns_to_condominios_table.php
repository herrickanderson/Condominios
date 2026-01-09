<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('condominios', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('mandato_khipu');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down()
    {
        Schema::table('condominios', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
