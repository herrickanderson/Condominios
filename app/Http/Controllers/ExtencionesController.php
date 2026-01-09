<?php

namespace App\Http\Controllers;

use App\Models\Extencion;
use App\Models\ExtencionServicioExtra;
use App\Models\Edificio;
use App\Models\Condominio;
use App\Models\TipoGastoComun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ExtencionesController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Extencion::with(['edificio', 'serviciosExtras'])
            ->whereHas('edificio', function ($q) {
                if (Auth::user()->id_condominio) {
                    $q->where('id_condominio', Auth::user()->id_condominio);
                }
            });

        if ($search) {
            $query->where('nombre', 'like', "%{$search}%");
        }

        $extenciones = $query->orderBy('id_extencion', 'desc')
            ->paginate(10)
            ->appends(['search' => $search]);

        return Inertia::render('Extenciones/Index', [
            'extenciones' => $extenciones,
            'filters'     => ['search' => $search],
        ]);
    }

    public function create()
    {
        if (Auth::user()->id_condominio) {
            $edificios = Edificio::where('id_condominio', Auth::user()->id_condominio)
                ->select('id_edificio as id', 'nombre')
                ->get();
            $condominios = []; // El usuario ya tiene asignado un condominio
            $servicios = TipoGastoComun::where('id_condominio', Auth::user()->id_condominio)->get();
        } else {
            $edificios = Edificio::select('id_edificio as id', 'nombre')->get();
            $condominios = Condominio::select('id_condominio as id', 'nombre')->get();
            $servicios = TipoGastoComun::all();
        }

        return Inertia::render('Extenciones/Create', [
            'edificios'   => $edificios,
            'condominios' => $condominios,
            'servicios'   => $servicios,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'          => 'required|string|max:100',
            'id_edificio'     => 'required|exists:edificios,id_edificio',
            'area'            => 'required|numeric',
            'unidad_medida'   => 'required|string',
            'tipo_extencion'  => 'required|in:Estacionamiento,Bodega',
            'cobro_unico'     => 'nullable|in:0,1',
            'servicios_extra' => 'nullable|array',
            'servicios_extra.*.id_tipo_gasto'    => 'required|exists:tipo_gasto_comun,id_tipo_gasto',
            'servicios_extra.*.porcentaje_extra' => 'required|numeric|min:0'
        ]);

        if (Auth::user()->id_condominio) {
            $validated['id_condominio'] = Auth::user()->id_condominio;
        } else {
            $validated['id_condominio'] = $request->input('id_condominio');
        }

        $validated['servicios_extra'] = $validated['servicios_extra'] ?? [];

        $extencion = Extencion::create($validated);

        foreach ($validated['servicios_extra'] as $serv) {
            ExtencionServicioExtra::create([
                'id_extencion'    => $extencion->id_extencion,
                'id_tipo_gasto'   => $serv['id_tipo_gasto'],
                'porcentaje_extra'=> $serv['porcentaje_extra'],
            ]);
        }

        return redirect()->route('extenciones.index')
            ->with('success', '¡Extención creada exitosamente!');
    }

    public function edit(Extencion $extencion)
    {
        // Cargamos las relaciones para que se envíen en la respuesta JSON.
        $extencion->load(['edificio', 'serviciosExtras']);

        if (Auth::user()->id_condominio) {
            $edificios = Edificio::where('id_condominio', Auth::user()->id_condominio)
                ->select('id_edificio as id', 'nombre')
                ->get();
            $servicios = TipoGastoComun::where('id_condominio', Auth::user()->id_condominio)->get();
        } else {
            $edificios = Edificio::select('id_edificio as id', 'nombre')->get();
            $servicios = TipoGastoComun::all();
        }

        return Inertia::render('Extenciones/Edit', [
            'extencion' => $extencion,
            'edificios' => $edificios,
            'servicios' => $servicios,
        ]);
    }

    public function update(Request $request, Extencion $extencion)
    {
        $validated = $request->validate([
            'nombre'          => 'required|string|max:100',
            'id_edificio'     => 'required|exists:edificios,id_edificio',
            'area'            => 'required|numeric',
            'unidad_medida'   => 'required|string',
            'tipo_extencion'  => 'required|in:Estacionamiento,Bodega',
            'cobro_unico'     => 'nullable|in:0,1',
            'servicios_extra' => 'nullable|array',
            'servicios_extra.*.id_tipo_gasto'    => 'required|exists:tipo_gasto_comun,id_tipo_gasto',
            'servicios_extra.*.porcentaje_extra' => 'required|numeric|min:0'
        ]);

        $extencion->update($validated);

        // Eliminamos los registros actuales de servicios extras y los recreamos.
        $extencion->serviciosExtras()->delete();

        if (isset($validated['servicios_extra']) && is_array($validated['servicios_extra'])) {
            foreach ($validated['servicios_extra'] as $serv) {
                ExtencionServicioExtra::create([
                    'id_extencion'    => $extencion->id_extencion,
                    'id_tipo_gasto'   => $serv['id_tipo_gasto'],
                    'porcentaje_extra'=> $serv['porcentaje_extra'],
                ]);
            }
        }

        return redirect()->route('extenciones.index')
            ->with('success', '¡Extención actualizada exitosamente!');
    }

    public function destroy(Extencion $extencion)
    {
        if ($extencion->serviciosExtras()->exists()) {
            return redirect()->route('extenciones.index')
                ->with('error', 'No se puede eliminar la extención porque tiene servicios asociados.');
        }

        $extencion->delete();
        return redirect()->route('extenciones.index')
            ->with('success', '¡Extención eliminada correctamente!');
    }
}
