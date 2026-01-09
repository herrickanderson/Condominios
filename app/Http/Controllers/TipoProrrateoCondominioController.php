<?php

namespace App\Http\Controllers;

use App\Models\TipoProrrateoCondominio;
use App\Models\Condominio;
use App\Models\TipoProrrateo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TipoProrrateoCondominioController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Traer registros con relaciones
        $registros = TipoProrrateoCondominio::with(['condominio', 'tipoProrrateo'])
            ->when($user->id_condominio, function ($query) use ($user) {
                $query->where('id_condominio', $user->id_condominio);
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Si es superadmin, se envían todos los condominios; de lo contrario, se usa el del usuario
        $condominios = $user->id_condominio
            ? []
            : Condominio::select('id_condominio as id', 'nombre')->get();

        // Traer la lista de tipos de prorrateo
        $tiposProrrateo = TipoProrrateo::select('id', 'descripcion')->orderBy('descripcion')->get();

        return Inertia::render('TipoProrrateoCondominio/Index', [
            'registros'      => $registros,
            'condominios'    => $condominios,
            'tiposProrrateo' => $tiposProrrateo,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Reglas de validación
        $rules = [
            'id_tipo_prorrateo' => 'required|exists:tipo_prorrateo,id',
        ];
        if (!$user->id_condominio) {
            $rules['id_condominio'] = 'required|exists:condominios,id_condominio';
        }
        $validated = $request->validate($rules);

        // Si el usuario tiene asignado un condominio, se fuerza ese valor
        if ($user->id_condominio) {
            $validated['id_condominio'] = $user->id_condominio;
        }

        // Buscar duplicado para el condominio actual y el mismo tipo (activo o inactivo)
        $duplicado = TipoProrrateoCondominio::where('id_condominio', $validated['id_condominio'])
            ->where('id_tipo_prorrateo', $validated['id_tipo_prorrateo'])
            ->first();

        if ($duplicado && $duplicado->estado == 1) {
            // Si ya existe y está activo, no se crea un nuevo registro
            return redirect()->route('tipo_prorrateo_condominio.index')
                ->with('error', 'Ya existe un registro activo para este condominio con el mismo tipo de prorrateo.');
        }

        // Inhabilitar todos los registros para el condominio actual
        TipoProrrateoCondominio::where('id_condominio', $validated['id_condominio'])
            ->update(['estado' => 0]);

        // Crear el nuevo registro con estado activo (1)
        $validated['estado'] = 1;
        TipoProrrateoCondominio::create($validated);

        return redirect()->route('tipo_prorrateo_condominio.index')
            ->with('success', 'Registro creado y registros anteriores inhabilitados.');
    }

    public function update(Request $request, TipoProrrateoCondominio $registro)
    {
        $user = Auth::user();

        // Reglas de validación para actualización
        $rules = [
            'id_tipo_prorrateo' => 'required|exists:tipo_prorrateo,id',
            'estado'            => 'required|in:0,1',
        ];
        if (!$user->id_condominio) {
            $rules['id_condominio'] = 'required|exists:condominios,id_condominio';
        }
        $validated = $request->validate($rules);

        if ($user->id_condominio) {
            $validated['id_condominio'] = $user->id_condominio;
        }

        // Si se actualiza a activo (1), inhabilitar todos los registros del condominio actual
        if ($validated['estado'] == 1) {
            TipoProrrateoCondominio::where('id_condominio', $validated['id_condominio'])
                ->update(['estado' => 0]);
        }

        // Actualizar el registro actual
        $registro->update($validated);

        return redirect()->route('tipo_prorrateo_condominio.index')
            ->with('success', 'Registro actualizado exitosamente!');
    }

    public function destroy(TipoProrrateoCondominio $registro)
    {
        $registro->delete();
        return redirect()->route('tipo_prorrateo_condominio.index')
            ->with('success', 'Registro eliminado exitosamente!');
    }
}
