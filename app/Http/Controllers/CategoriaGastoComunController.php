<?php

namespace App\Http\Controllers;

use App\Models\CategoriaGastoComun;
use App\Models\Condominio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CategoriaGastoComunController extends Controller
{
    public function index(Request $request)
    {
        $query = CategoriaGastoComun::query();
        // Filtrar por condominio si el usuario tiene asignado
        if (Auth::user()->id_condominio) {
            $query->where('id_condominio', Auth::user()->id_condominio);
        }
        $categorias = $query->orderBy('id_categoria', 'desc')->paginate(10);
        return Inertia::render('CategoriaGastoComun/Index', compact('categorias'));
    }

    public function create()
    {
        // Si el usuario es SuperAdmin, se envía la lista de condominios
        if (!Auth::user()->id_condominio) {
            $condominios = Condominio::select('id_condominio as id', 'nombre')->get();
        } else {
            $condominios = [];
        }
        return Inertia::render('CategoriaGastoComun/Create', [
            'condominios' => $condominios,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'nombre' => 'required|string|max:50',
        ];
        if (!Auth::user()->id_condominio) {
            $rules['id_condominio'] = 'required|exists:condominios,id_condominio';
        }
        $validated = $request->validate($rules);
        if (Auth::user()->id_condominio) {
            $validated['id_condominio'] = Auth::user()->id_condominio;
        }
        CategoriaGastoComun::create($validated);
        return redirect()->route('categoria_gasto_comun.index')
            ->with('success', '¡Categoría creada exitosamente!');
    }

    public function edit(CategoriaGastoComun $categoria)
    {
        if (!Auth::user()->id_condominio) {
            $condominios = Condominio::select('id_condominio as id', 'nombre')->get();
        } else {
            $condominios = [];
        }
        return Inertia::render('CategoriaGastoComun/Edit', [
            'categoria' => $categoria,
            'condominios' => $condominios,
        ]);
    }

    public function update(Request $request, CategoriaGastoComun $categoria)
    {
        $rules = [
            'nombre' => 'required|string|max:50',
        ];
        if (!Auth::user()->id_condominio) {
            $rules['id_condominio'] = 'required|exists:condominios,id_condominio';
        }
        $validated = $request->validate($rules);
        if (Auth::user()->id_condominio) {
            $validated['id_condominio'] = Auth::user()->id_condominio;
        }
        $categoria->update($validated);
        return redirect()->route('categoria_gasto_comun.index')
            ->with('success', '¡Categoría actualizada exitosamente!');
    }

    public function destroy(CategoriaGastoComun $categoria)
    {
        // Validar: no se debe eliminar si existen tipos asociados
        if ($categoria->tipos()->exists()) {
            return redirect()->route('categoria_gasto_comun.index')
                ->with('error', 'No se puede eliminar la categoría porque tiene tipos asociados.');
        }
        $categoria->delete();
        return redirect()->route('categoria_gasto_comun.index')
            ->with('success', '¡Categoría eliminada exitosamente!');
    }
}
