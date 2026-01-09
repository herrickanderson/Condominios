<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('prorrateo_valores', function (Blueprint $table) {
            // Si la columna tiene una FK, la eliminamos primero
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    public function down(): void {
        Schema::table('prorrateo_valores', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->default(1)->after('tipo_prorrateo_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

};
