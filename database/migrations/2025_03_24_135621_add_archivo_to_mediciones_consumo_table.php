<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mediciones_consumo', function (Blueprint $table) {
             $table->text('archivo')->nullable()->after('observacion');
        });
    }
    
    public function down()
    {
        Schema::table('mediciones_consumo', function (Blueprint $table) {
             $table->dropColumn('archivo');
        });
    }
    
};
