<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Usuario::create([
            'name' => 'Admin',
            'apellidos' => 'Sistema',
            'email' => 'admin@condominios.com',
            'password' => Hash::make('password'),
            'estado' => 'A', // Assuming 'A' is for Active based on char(1)
            // Optional fields that might be needed depending on strict mode or logic
            // 'rut' => '11111111-1', 
        ]);
    }
}
