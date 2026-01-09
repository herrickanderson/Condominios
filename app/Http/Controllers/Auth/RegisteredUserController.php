<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . Usuario::class,
            'telefono' => 'nullable|string|max:50',
            'rut' => 'nullable|string|max:20|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'estado' => 'required|in:A,I', // ✅ Validamos el estado (Activo/Inactivo)
        ]);

        $user = Usuario::create([
            'name' => $request->name,
            'apellidos' => $request->apellidos,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'rut' => $request->rut,
            'password' => Hash::make($request->password),
            'estado' => $request->estado, // ✅ Guardamos el estado en la BD
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

}
