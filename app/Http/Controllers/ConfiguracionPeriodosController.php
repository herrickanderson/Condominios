<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionPeriodo;
use App\Models\Condominio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ConfiguracionPeriodosController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search', '');

        // Query base
        $query = ConfiguracionPeriodo::with('condominio');

        // Si el usuario tiene un condominio, filtramos
        if ($user->id_condominio) {
            $query->where('idcondominio', $user->id_condominio);
            $condominios = []; // no necesitamos lista si no es superadmin
        } else {
            // Caso superadmin: cargamos todos
            $condominios = Condominio::select('id_condominio as id', 'nombre')->get();
        }

        // Búsqueda
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('dia_inicio', 'like', "%{$search}%")
                  ->orWhere('dia_fin', 'like', "%{$search}%")
                  ->orWhere('dia_vencimiento', 'like', "%{$search}%")
                  ->orWhere('nombre_periodo', 'like', "%{$search}%");
            });
        }

        // Paginación
        $periodos = $query->orderBy('id', 'desc')
            ->paginate(10)
            ->appends(['search' => $search]);

        return Inertia::render('Periodos/Index', [
            'periodos'   => $periodos,
            'condominios'=> $condominios,
            'filters'    => ['search' => $search],
        ]);
    }

    public function store(Request $request)
    {
        // Validación
        $rules = [
            'dia_inicio'      => 'required|integer|min:1|max:31',
            'dia_fin'         => 'required|integer|min:1|max:31',
            'dia_vencimiento' => 'required|integer|min:1|max:31',
        ];

        // Si es superadmin, necesita idcondominio
        if (!Auth::user()->id_condominio) {
            $rules['idcondominio'] = 'required|exists:condominios,id_condominio';
        }

        $validated = $request->validate($rules);

        // Si no es superadmin, usamos su id_condominio
        if (Auth::user()->id_condominio) {
            $validated['idcondominio'] = Auth::user()->id_condominio;
        }

        // Inactivar periodos anteriores
        ConfiguracionPeriodo::where('idcondominio', $validated['idcondominio'])
            ->update(['estado' => 0]);

        // Contamos todas las filas para generar un nombre único
        $count = ConfiguracionPeriodo::where('idcondominio', $validated['idcondominio'])->count();
        $nombrePeriodo = 'Periodo ' . ($count + 1);

        $validated['nombre_periodo'] = $nombrePeriodo;
        $validated['estado'] = 1; // siempre activo al crear

        ConfiguracionPeriodo::create($validated);

        return redirect()->route('periodos.index')
            ->with('success', '¡Periodo creado exitosamente! Los anteriores se inhabilitaron.');
    }

    public function update(Request $request, ConfiguracionPeriodo $periodo)
    {
        $validated = $request->validate([
            'dia_inicio'      => 'required|integer|min:1|max:31',
            'dia_fin'         => 'required|integer|min:1|max:31',
            'dia_vencimiento' => 'required|integer|min:1|max:31',
            'estado'          => 'required|in:0,1',
        ]);

        // Si se activa, inactivamos los demás
        if ($validated['estado'] == 1) {
            ConfiguracionPeriodo::where('idcondominio', $periodo->idcondominio)
                ->update(['estado' => 0]);
        }

        // Mantenemos nombre_periodo
        $periodo->update($validated);

        return redirect()->route('periodos.index')
            ->with('success', '¡Periodo actualizado correctamente!');
    }

    public function destroy(ConfiguracionPeriodo $periodo)
    {
        $periodo->delete();
        return redirect()->route('periodos.index')
            ->with('success', '¡Periodo eliminado correctamente!');
    }
}
