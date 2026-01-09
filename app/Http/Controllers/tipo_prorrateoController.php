<?php

namespace App\Http\Controllers;

use App\Models\Condominio;
use App\Models\TipoProrrateo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class tipo_prorrateoController extends Controller
{
    // Listado de tipos prorrateo
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = TipoProrrateo::query();

        if ($search) {
            $query->where('descripcion', 'like', "%{$search}%");
        }

        // No se filtra por condominio, se muestran todos los registros.
        $tipos = $query->orderBy('id', 'desc')->paginate(10)
            ->appends(['search' => $search]);

        return Inertia::render('TipoProrrateo/Index', [
            'tipos'   => $tipos,
            'filters' => ['search' => $search],
        ]);
    }



    // Muestra el formulario para crear un nuevo tipo prorrateo
    public function create()
    {
        // Si el usuario no tiene condominio (SuperAdmin), enviar la lista de condominios para elegir
        if (!Auth::user()->id_condominio) {
            $condominios = Condominio::select('id_condominio as id', 'nombre')->get();
        } else {
            $condominios = [];
        }

        return Inertia::render('TipoProrrateo/Create', [
            'condominios' => $condominios,
        ]);
    }

    // Guarda el nuevo tipo prorrateo
    public function store(Request $request)
    {
        $rules = [
            'descripcion' => 'required|string|max:50',
        ];

        if (!Auth::user()->id_condominio) {
            $rules['id_condominio'] = 'required|exists:condominios,id_condominio';
        }

        $validated = $request->validate($rules);

        // Si es administrador, forzamos el condominio del usuario autenticado
        if (Auth::user()->id_condominio) {
            $validated['id_condominio'] = Auth::user()->id_condominio;
        }

        TipoProrrateo::create($validated);

        return redirect()->route('tipos.index')->with('success', '¡Tipo prorrateo creado exitosamente!');
    }

    // Muestra el formulario para editar un tipo prorrateo
    public function edit(TipoProrrateo $tipo)
    {
        if (!Auth::user()->id_condominio) {
            $condominios = Condominio::select('id_condominio as id', 'nombre')->get();
        } else {
            $condominios = [];
        }

        return Inertia::render('TipoProrrateo/Edit', [
            'tipo'        => $tipo,
            'condominios' => $condominios,
        ]);
    }

    // Actualiza el tipo prorrateo
    public function update(Request $request, TipoProrrateo $tipo)
    {
        $rules = [
            'descripcion' => 'required|string|max:50',
        ];

        if (!Auth::user()->id_condominio) {
            $rules['id_condominio'] = 'required|exists:condominios,id_condominio';
        }

        $validated = $request->validate($rules);
        if (Auth::user()->id_condominio) {
            $validated['id_condominio'] = Auth::user()->id_condominio;
        }

        $tipo->update($validated);

        return redirect()->route('tipos.index')->with('success', '¡Tipo prorrateo actualizado exitosamente!');
    }

    // Elimina el tipo prorrateo
    public function destroy(TipoProrrateo $tipo)
    {
        $tipo->delete();
        return redirect()->route('tipos.index')->with('success', '¡Tipo prorrateo eliminado exitosamente!');
    }
}
