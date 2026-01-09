<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserInfoController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\Usuario|null $user */
        $user = Auth::user();
        if ($user) {
            // Consulta explícitamente la relación 'roles'
            $roles = $user->roles()->get();
            // Asigna el resultado a la propiedad 'roles' del usuario
            $user->roles = $roles;
        }
        return response()->json(['user' => $user]);
    }
}
