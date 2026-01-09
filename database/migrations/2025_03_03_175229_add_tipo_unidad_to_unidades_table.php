<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            // Agregamos un campo para indicar si es "departamento" o "negocio"
            $table->string('tipo_unidad', 50)
                  ->default('departamento')
                  ->after('unidad_medida');
        });
    }

    public function down(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->dropColumn('tipo_unidad');
        });
    }
};
