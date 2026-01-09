<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class unidadesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Unidade::with([
            'edificio',
            'propietario',
            'serviciosExtras'
        ])->whereHas('edificio', function ($q) {
            if (Auth::user()->id_condominio) {
                $q->where('id_condominio', Auth::user()->id_condominio);
            }
        });

        if ($search) {
            $query->where('nombre_unidad', 'like', "%{$search}%");
        }

        $unidades = $query->orderBy('id_unidad', 'desc')
            ->paginate(10)
            ->appends(['search' => $search]);

        return Inertia::render('Unidades/Index', [
            'unidades' => $unidades,
            'filters'  => ['search' => $search],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->id_condominio) {
            $edificios = \App\Models\Edificio::where('id_condominio', Auth::user()->id_condominio)
                ->select('id_edificio as id', 'nombre')
                ->get();
            $condominios = [];
            $servicios = \App\Models\TipoGastoComun::where('id_condominio', Auth::user()->id_condominio)->get();
        } else {
            $edificios = \App\Models\Edificio::select('id_edificio as id', 'nombre')->get();
            $condominios = \App\Models\Condominio::select('id_condominio as id', 'nombre')->get();
            $servicios = \App\Models\TipoGastoComun::all();
        }

        return Inertia::render('Unidades/Create', [
            'edificios'   => $edificios,
            'condominios' => $condominios,
            'servicios'   => $servicios,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_unidad' => 'required|string|max:100',
            'id_edificio'   => 'required|exists:edificios,id_edificio',
            'area'          => 'required|numeric',
            'unidad_medida' => 'required|string',
            'tipo_unidad'   => 'required|in:departamento,negocio',
            // Solo si es negocio, se pueden enviar servicios_extra
            'servicios_extra'                  => 'array',
            'servicios_extra.*.id_tipo_gasto'  => 'exists:tipo_gasto_comun,id_tipo_gasto',
            'servicios_extra.*.porcentaje_extra' => 'numeric|min:0',
        ]);

        if (Auth::user()->id_condominio) {
            $validated['id_condominio'] = Auth::user()->id_condominio;
        } else {
            $validated['id_condominio'] = $request->input('id_condominio');
        }

        // Crear la unidad
        $unidad = Unidade::create($validated);

        // Si es negocio y hay servicios extra, se crean
        if ($validated['tipo_unidad'] === 'negocio' && !empty($validated['servicios_extra'])) {
            foreach ($validated['servicios_extra'] as $serv) {
                \App\Models\UnidadServicioExtra::create([
                    'id_unidad'        => $unidad->id_unidad,
                    'id_tipo_gasto'    => $serv['id_tipo_gasto'],
                    'porcentaje_extra' => $serv['porcentaje_extra'] ?? 0,
                ]);
            }
        }

        return redirect()->route('unidades.index')->with('success', '¡Unidad creada exitosamente!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\App\Models\Unidade $unidad)
    {
        // Cargar relaciones necesarias
        $unidad->load('edificio', 'propietario', 'serviciosExtras.tipoGasto');

        if (Auth::user()->id_condominio) {
            $edificios = \App\Models\Edificio::where('id_condominio', Auth::user()->id_condominio)
                ->select('id_edificio as id', 'nombre')
                ->get();
            $servicios = \App\Models\TipoGastoComun::where('id_condominio', Auth::user()->id_condominio)->get();
        } else {
            $edificios = \App\Models\Edificio::select('id_edificio as id', 'nombre')->get();
            $servicios = \App\Models\TipoGastoComun::all();
        }

        return Inertia::render('Unidades/Edit', [
            'unidad'    => $unidad,
            'edificios' => $edificios,
            'servicios' => $servicios,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, \App\Models\Unidade $unidad)
    {
        $validated = $request->validate([
            'nombre_unidad' => 'required|string|max:100',
            'id_edificio'   => 'required|exists:edificios,id_edificio',
            'area'          => 'required|numeric',
            'unidad_medida' => 'required|string',
            'tipo_unidad'   => 'required|in:departamento,negocio',
            'servicios_extra' => 'array',
            'servicios_extra.*.id_tipo_gasto'   => 'exists:tipo_gasto_comun,id_tipo_gasto',
            'servicios_extra.*.porcentaje_extra' => 'numeric|min:0',
        ]);

        // Opcional: si la unidad tiene restricciones para cambiar de tipo, se puede validar aquí

        $unidad->update($validated);

        if ($validated['tipo_unidad'] === 'departamento') {
            \App\Models\UnidadServicioExtra::where('id_unidad', $unidad->id_unidad)->delete();
        } else {
            \App\Models\UnidadServicioExtra::where('id_unidad', $unidad->id_unidad)->delete();
            if (!empty($validated['servicios_extra'])) {
                foreach ($validated['servicios_extra'] as $serv) {
                    \App\Models\UnidadServicioExtra::create([
                        'id_unidad'        => $unidad->id_unidad,
                        'id_tipo_gasto'    => $serv['id_tipo_gasto'],
                        'porcentaje_extra' => $serv['porcentaje_extra'] ?? 0,
                    ]);
                }
            }
        }

        return redirect()->route('unidades.index')
            ->with('success', '¡Unidad actualizada exitosamente!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unidade $unidad)
    {
        // Verificar si la unidad tiene relaciones que impidan su eliminación.
        // Por ejemplo: usuarios asignados, distribución de gastos, visitas o servicios extras.
        if (
            $unidad->users()->exists() ||
            $unidad->distribucion_gastos()->exists() ||
            $unidad->visitas()->exists() ||
            $unidad->serviciosExtras()->exists()
        ) {
            return redirect()->route('unidades.index')
                ->with('error', 'No se puede eliminar la unidad porque tiene registros asociados.');
        }

        $unidad->delete();

        return redirect()->route('unidades.index')
            ->with('success', '¡Unidad eliminada correctamente!');
    }
}
