<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $now = Carbon::now();
    
        $users = [
            [
                'name' => 'Herrick Anderson Davis Martinez',
                'apellidos' => 'Davis Martinez',
                'email' => 'herrickanderson@gmail.com',
                'telefono' => '+51958897726',
                'password' => Hash::make('admin123'),
            ],
            [
                'name' => 'JuanPablo Santander',
                'apellidos' => 'Santander',
                'email' => 'jp@gmail.com',
                'telefono' => '+51912345678',
                'password' => Hash::make('admin123'),
            ],
            [
                'name' => 'Karina Soncco',
                'apellidos' => 'Soncco',
                'email' => 'karinasoncco@gmail.com',
                'telefono' => '+51987654321',
                'password' => Hash::make('admin123'),
            ],
            [
                'name' => 'Karimme',
                'apellidos' => 'Flores',
                'email' => 'Karimmeflores@gmail.com',
                'telefono' => '+51987654321',
                'password' => Hash::make('admin123'),
            ],
        ];
    
        foreach ($users as $data) {
            // Verifica si el usuario ya existe
            $existingUser = DB::table('users')->where('email', $data['email'])->first();
    
            if (!$existingUser) {
                // Si no existe, lo crea
                $userId = DB::table('users')->insertGetId([
                    'name' => $data['name'],
                    'apellidos' => $data['apellidos'],
                    'email' => $data['email'],
                    'telefono' => $data['telefono'],
                    'password' => $data['password'],
                    'estado' => 'A',
                    'tiene_extencion' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                // Ya existe
                $userId = $existingUser->id;
            }
    
            // Verifica si ya tiene el rol de Superadmin (id_rol = 1)
            $hasRole = DB::table('usuario_roles')
                ->where('id_usuario', $userId)
                ->where('id_rol', 1)
                ->exists();
    
            if (!$hasRole) {
                DB::table('usuario_roles')->insert([
                    'id_usuario' => $userId,
                    'id_rol' => 1
                ]);
            }
        }
    }
    
    public function down()
    {
        // Eliminar roles primero
        $emails = [
            'herrickanderson@gmail.com',
            'jp@gmail.com',
            'karinasoncco@gmail.com'
        ];

        $users = DB::table('users')->whereIn('email', $emails)->get();

        foreach ($users as $user) {
            DB::table('usuario_roles')->where('id_usuario', $user->id)->delete();
        }

        // Luego eliminar usuarios
        DB::table('users')->whereIn('email', $emails)->delete();
    }
    
};
