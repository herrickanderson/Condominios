<?php

namespace App\Http\Controllers;

use App\Models\ProrrateoValor;
use App\Models\TipoProrrateo;
use App\Models\Condominio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProrrateoValoresController extends Controller
{
    /**
     * Muestra el listado de registros de prorrateo_valores.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Inicia la consulta sin cargar la relación 'usuario'
        $query = ProrrateoValor::with(['tipoProrrateo', 'condominio']);

        // Filtra por condominio si el usuario es administrador
        if (Auth::user()->id_condominio) {
            $query->where('id_condominio', Auth::user()->id_condominio);
        }

        // Aplica búsqueda sobre "criterio"
        if ($search) {
            $query->where('criterio', 'like', "%{$search}%");
        }

        $prorrateos = $query->orderBy('id', 'desc')->paginate(10)
            ->appends(['search' => $search]);

        return Inertia::render('ProrrateoValores/Index', [
            'prorrateos' => $prorrateos,
            'filters'    => ['search' => $search],
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo registro de prorrateo_valores.
     */
    public function create()
    {
        // Para un administrador, filtra los tipos de prorrateo del condominio asignado
        if (Auth::user()->id_condominio) {
            $condominioId = Auth::user()->id_condominio;
            $tipos = TipoProrrateo::where('id_condominio', $condominioId)->get();
        } else {
            // SuperAdmin: envía todos los tipos
            $tipos = TipoProrrateo::all();
        }

        // Para SuperAdmin, se envía la lista de condominios para que pueda elegir
        $condominios = [];
        if (!Auth::user()->id_condominio) {
            $condominios = Condominio::select('id_condominio as id', 'nombre')->get();
        }

        return Inertia::render('ProrrateoValores/Create', [
            'tipos'       => $tipos,
            'condominios' => $condominios,
        ]);
    }

    /**
     * Guarda un nuevo registro de prorrateo_valores.
     */
    public function store(Request $request)
    {
        $rules = [
            'tipo_prorrateo_id' => 'required|exists:tipo_prorrateo,id',
            'criterio'          => 'required|string|max:50',
            'valor_criterio'    => 'required|numeric',
        ];

        // Si el usuario es SuperAdmin, se requiere el id_condominio en el formulario
        if (!Auth::user()->id_condominio) {
            $rules['id_condominio'] = 'required|exists:condominios,id_condominio';
        }

        $validated = $request->validate($rules);

        // Para administradores, forzamos el condominio del usuario autenticado
        if (Auth::user()->id_condominio) {
            $validated['id_condominio'] = Auth::user()->id_condominio;
        }

        ProrrateoValor::create($validated);

        return redirect()->route('prorrateos.index')
            ->with('success', '¡Prorrateo registrado exitosamente!');
    }

    /**
     * Muestra el formulario para editar un registro.
     */
    public function edit(ProrrateoValor $prorrateoValor)
    {
        if (Auth::user()->id_condominio) {
            $tipos = TipoProrrateo::where('id_condominio', Auth::user()->id_condominio)->get();
        } else {
            $tipos = TipoProrrateo::all();
        }

        $condominios = [];
        if (!Auth::user()->id_condominio) {
            $condominios = Condominio::select('id_condominio as id', 'nombre')->get();
        }

        return Inertia::render('ProrrateoValores/Edit', [
            'prorrateoValor' => $prorrateoValor,
            'tipos'          => $tipos,
            'condominios'    => $condominios,
        ]);
    }

    /**
     * Actualiza un registro de prorrateo_valores.
     */
    public function update(Request $request, ProrrateoValor $prorrateoValor)
    {
        $rules = [
            'tipo_prorrateo_id' => 'required|exists:tipo_prorrateo,id',
            'criterio'          => 'required|string|max:50',
            'valor_criterio'    => 'required|numeric',
        ];

        if (!Auth::user()->id_condominio) {
            $rules['id_condominio'] = 'required|exists:condominios,id_condominio';
        }

        $validated = $request->validate($rules);
        if (Auth::user()->id_condominio) {
            $validated['id_condominio'] = Auth::user()->id_condominio;
        }

        $prorrateoValor->update($validated);

        return redirect()->route('prorrateos.index')
            ->with('success', '¡Prorrateo actualizado exitosamente!');
    }

    /**
     * Elimina un registro de prorrateo_valores.
     */
    public function destroy(ProrrateoValor $prorrateoValor)
    {
        $prorrateoValor->delete();
        return redirect()->route('prorrateos.index')
            ->with('success', '¡Registro eliminado exitosamente!');
    }
}
