<?php

namespace App\Http\Controllers;

use App\Models\TipoGastoComun;
use App\Models\CategoriaGastoComun;
use App\Models\Condominio;
use App\Models\TipoProrrateo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TipoGastoComunController extends Controller
{
    public function index(Request $request)
    {
        // Se obtienen los registros con la relación de categoría y se cuenta la relación con unidades de servicio extra.
        $tipos = TipoGastoComun::with(['categoria'])
            ->withCount('unidadesServicioExtras')
            ->when(Auth::user()->id_condominio, function ($query) {
                $query->where('id_condominio', Auth::user()->id_condominio);
            })
            ->orderBy('id_tipo_gasto', 'desc')
            ->paginate(10);

        // Si el usuario es SuperAdmin (sin condominio asignado), se pasan los condominios para el formulario.
        $condominios = Auth::user()->id_condominio
            ? []
            : Condominio::select('id_condominio as id', 'nombre')->get();

        // Se filtran las categorías según el condominio (si aplica).
        $categorias = CategoriaGastoComun::when(Auth::user()->id_condominio, function ($query) {
            $query->where('id_condominio', Auth::user()->id_condominio);
        })->orderBy('nombre')->get();

        // Mapping de Prorrateo de Condominio.
        $prorrateoCondominio = DB::table('tipo_prorrateo_condominios')
            ->join('tipo_prorrateo', 'tipo_prorrateo_condominios.id_tipo_prorrateo', '=', 'tipo_prorrateo.id')
            ->select('tipo_prorrateo_condominios.id_condominio', 'tipo_prorrateo.descripcion')
            ->where('tipo_prorrateo_condominios.estado', 1)
            ->get()
            ->keyBy('id_condominio');

        // Opciones para gasto individual.
        $tipoProrrateo = TipoProrrateo::select('id', 'descripcion')->get();

        return Inertia::render('TipoGastoComun/Index', [
            'tipos'               => $tipos,
            'categorias'          => $categorias,
            'condominios'         => $condominios,
            'prorrateoCondominio' => $prorrateoCondominio,
            'tipoProrrateo'       => $tipoProrrateo,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'nombre'                    => 'required|string|max:50',
            'aplica_a_todos_edificios'  => 'required|boolean',
            'id_categoria'              => 'required|exists:categoria_gasto_comun,id_categoria',
            'aplica_prorrateo_condominio' => 'required|integer|in:0,1',
            'tipo_prorrateo_id'         => 'nullable|integer|exists:tipo_prorrateo,id',
            'consumo'                   => 'required|boolean',
        ];

        // Si se marca consumo, se requiere la medida.
        if ($request->consumo) {
            $rules['medida'] = 'required|string|max:50';
        } else {
            $rules['medida'] = 'nullable';
        }

        if (!Auth::user()->id_condominio) {
            $rules['id_condominio'] = 'required|exists:condominios,id_condominio';
        }

        $validated = $request->validate($rules);

        if (Auth::user()->id_condominio) {
            $validated['id_condominio'] = Auth::user()->id_condominio;
        }

        TipoGastoComun::create($validated);

        return redirect()->route('tipo_gasto_comun.index')
            ->with('success', '¡Tipo de gasto común creado exitosamente!');
    }

    public function update(Request $request, TipoGastoComun $tipo)
    {
        $rules = [
            'nombre'                    => 'required|string|max:50',
            'aplica_a_todos_edificios'  => 'required|boolean',
            'id_categoria'              => 'required|exists:categoria_gasto_comun,id_categoria',
            'aplica_prorrateo_condominio' => 'required|integer|in:0,1',
            'tipo_prorrateo_id'         => 'nullable|integer|exists:tipo_prorrateo,id',
            'consumo'                   => 'required|boolean',
        ];

        if ($request->consumo) {
            $rules['medida'] = 'required|string|max:50';
        } else {
            $rules['medida'] = 'nullable';
        }

        if (!Auth::user()->id_condominio) {
            $rules['id_condominio'] = 'required|exists:condominios,id_condominio';
        }

        $validated = $request->validate($rules);

        if (Auth::user()->id_condominio) {
            $validated['id_condominio'] = Auth::user()->id_condominio;
        }

        $tipo->update($validated);

        return redirect()->route('tipo_gasto_comun.index')
            ->with('success', '¡Tipo de gasto común actualizado exitosamente!');
    }

    public function destroy(TipoGastoComun $tipo)
    {
        if ($tipo->unidadesServicioExtras()->exists()) {
            return redirect()->route('tipo_gasto_comun.index')
                ->with('error', 'No se puede eliminar este servicio porque está relacionado con alguna unidad.');
        }

        try {
            $tipo->delete();
            return redirect()->route('tipo_gasto_comun.index')
                ->with('success', '¡Tipo de gasto común eliminado exitosamente!');
        } catch (QueryException $e) {
            return redirect()->route('tipo_gasto_comun.index')
                ->with('error', 'Acción Bloqueada: No se puede eliminar este servicio porque está vinculado a distribuciones, pagos o unidades.');
        }
    }
}
