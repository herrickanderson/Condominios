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
        Schema::table('unidades', function (Blueprint $table) {
            $table->dropColumn(['tipo_unidad', 'prorrateo_tipo', 'prorrateo_valor']);
        });
    }

    public function down(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            // Vuelve a agregar las columnas en caso de rollback (definiendo sus tipos)
            $table->enum('tipo_unidad', ['Departamento', 'Estacionamiento', 'Bodega', 'Local'])->nullable();
            $table->enum('prorrateo_tipo', ['Lineal', 'Por_Mt2', 'Por_Porcentaje'])->nullable();
            $table->decimal('prorrateo_valor', 8, 6)->nullable()->default(0);
        });
    }
};
