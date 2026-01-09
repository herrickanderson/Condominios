<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Usuario;
use App\Models\UsuarioRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class rolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtenemos todos los roles
        $roles = Role::all();

        // Filtramos las asignaciones de usuario-rol según el condominio
        if (Auth::user()->id_condominio) {
            $usuarioRoles = UsuarioRole::with(['usuario', 'role'])
                ->whereHas('usuario', function ($q) {
                    $q->where('id_condominio', Auth::user()->id_condominio);
                })->get();
        } else {
            // Si es SuperAdmin, se muestran todas las asignaciones
            $usuarioRoles = UsuarioRole::with(['usuario', 'role'])->get();
        }

        return Inertia::render('Rolesperfil/Index', [
            'roles'         => $roles,
            'usuarioRoles'  => $usuarioRoles,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Si el usuario autenticado tiene asignado un condominio, significa que es administrador
        // y se debe excluir el rol "SuperAdmin" del listado.
        if (Auth::user()->id_condominio) {
            $usuarios = Usuario::where('id_condominio', Auth::user()->id_condominio)->get();
            $roles = Role::where('nombre', '!=', 'SuperAdmin')->get();
        } else {
            // Si es SuperAdmin, se muestran todos los usuarios y roles
            $usuarios = Usuario::all();
            $roles = Role::all();
        }
    
        return Inertia::render('Rolesperfil/Create', [
            'usuarios' => $usuarios,
            'roles'    => $roles,
        ]);
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validar los datos que llegan del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
        ]);

        // 2. Crear el nuevo rol
        Role::create([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        // 3. Redirigir al listado con un mensaje de éxito (opcional)
        return redirect()->route('rolesperfil.index')->with('success', '¡Rol creado exitosamente!');
    }
    public function storeUsuarioRole(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|exists:users,id',
            'id_rol'     => 'required|exists:roles,id_rol',
        ]);

        // Verifica duplicado
        $existe = UsuarioRole::where('id_usuario', $request->id_usuario)
            ->where('id_rol', $request->id_rol)
            ->exists();

        if ($existe) {
            // 1. Vuelves a cargar la data necesaria para 'Create'
            $usuarios = Usuario::all();
            $roles = Role::all();

            // 2. Regresas la misma vista 'Rolesperfil/Create' con un flag 'errorDuplicado'
            return Inertia::render('Rolesperfil/Create', [
                'usuarios' => $usuarios,
                'roles'    => $roles,
                'errorDuplicado' => true, // O cualquier nombre que desees
            ]);
        }

        // Si no existe, creas la asignación y regresas al index
        UsuarioRole::create([
            'id_usuario' => $request->id_usuario,
            'id_rol'     => $request->id_rol,
        ]);

        return redirect()->route('rolesperfil.index')
            ->with('success', '¡Rol asignado exitosamente!');
    }




    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
