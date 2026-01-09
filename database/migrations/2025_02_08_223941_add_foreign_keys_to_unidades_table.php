<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->foreign(['id_asignacion_padre'], 'unidades_id_asignacion_padre_fkey')->references(['id_unidad'])->on('unidades')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['id_edificio'], 'unidades_id_edificio_fkey')->references(['id_edificio'])->on('edificios')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            // Elimina la llave foránea solo si existe, usando IF EXISTS
            DB::statement('ALTER TABLE unidades DROP CONSTRAINT IF EXISTS unidades_id_asignacion_padre_fkey');
            DB::statement('ALTER TABLE unidades DROP CONSTRAINT IF EXISTS unidades_id_edificio_fkey');
        });
    }
};
