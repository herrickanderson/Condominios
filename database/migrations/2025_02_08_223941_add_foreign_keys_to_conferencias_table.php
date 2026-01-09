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
        Schema::table('conferencias', function (Blueprint $table) {
            $table->foreign(['id_moderador'], 'conferencias_id_moderador_fkey')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conferencias', function (Blueprint $table) {
            $table->dropForeign('conferencias_id_moderador_fkey');
        });
    }
};
