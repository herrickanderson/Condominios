<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateExceptCommand extends Command
{
    protected $signature = 'db:truncate-except';
    protected $description = 'Elimina datos de todas las tablas excepto roles, usuario_roles, users y tipo_prorrateo';

    public function handle()
    {
        // Agrega todas las tablas que no deseas truncar
        $excludedTables = ['roles', 'usuario_roles', 'users', 'tipo_prorrateo'];
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        // Obtener tablas según el motor de BD
        if ($driver === 'pgsql') {
            $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE'");
            $tables = array_column($tables, 'table_name');
        } else {
            $tables = DB::select('SHOW TABLES');
            $tables = array_map(function ($table) use ($connection) {
                return $table->{'Tables_in_' . config("database.connections.{$connection}.database")};
            }, $tables);
        }

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $tableName) {
            if (!in_array($tableName, $excludedTables)) {
                DB::table($tableName)->truncate();
                $this->info("Tabla truncada: $tableName");
            }
        }

        Schema::enableForeignKeyConstraints();

        $this->info('¡Base de datos limpiada exitosamente!');
        return 0;
    }
}
