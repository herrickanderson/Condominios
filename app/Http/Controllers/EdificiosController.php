<?php

namespace App\Http\Controllers;

use App\Http\Requests\Edificios\StoreRequest;
use App\Models\Condominio;
use App\Models\Edificio;
use App\Models\TipoProrrateo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EdificiosController extends Controller
{
    /**
     * Mostrar listado paginado de edificios.
     */
    public function index()
    {
        $query = Edificio::with('condominio');

        // Filtrar edificios según el condominio del usuario autenticado
        if (Auth::user()->id_condominio) {
            $query->where('id_condominio', Auth::user()->id_condominio);
        }

        $edificios = $query->paginate(5);

        // Mapping de prorrateo condominio: key = id_condominio, value = descripción
        $prorrateoCondominio = DB::table('tipo_prorrateo_condominios')
            ->join('tipo_prorrateo', 'tipo_prorrateo_condominios.id_tipo_prorrateo', '=', 'tipo_prorrateo.id')
            ->select('tipo_prorrateo_condominios.id_condominio', 'tipo_prorrateo.descripcion')
            ->where('tipo_prorrateo_condominios.estado', 1)
            ->get()
            ->keyBy('id_condominio');

        // Mapping de prorrateo individual (todos los tipos)
        $tipoProrrateoInd = TipoProrrateo::select('id', 'descripcion')->get()->keyBy('id');

        return Inertia::render('Edificios/Index', [
            'edificios'           => $edificios,
            'prorrateoCondominio' => $prorrateoCondominio,
            'tipoProrrateoInd'    => $tipoProrrateoInd,
        ]);
    }

    /**
     * Formulario para crear un nuevo edificio.
     */
    public function create()
    {
        $condominios = Condominio::select('id_condominio as id', 'nombre')->get();
        // Todos los tipos de prorrateo individuales sin filtrar
        $tipoProrrateo = TipoProrrateo::all();

        // Mapping de prorrateo condominio: para mostrar el prorrateo del condominio seleccionado
        $prorrateoCondominio = DB::table('tipo_prorrateo_condominios')
            ->join('tipo_prorrateo', 'tipo_prorrateo_condominios.id_tipo_prorrateo', '=', 'tipo_prorrateo.id')
            ->select('tipo_prorrateo_condominios.id_condominio', 'tipo_prorrateo.descripcion')
            ->where('tipo_prorrateo_condominios.estado', 1)
            ->get()
            ->keyBy('id_condominio');

        return Inertia::render('Edificios/Create', [
            'condominios'       => $condominios,
            'tipoProrrateo'     => $tipoProrrateo,
            'prorrateoCondominio' => $prorrateoCondominio,
        ]);
    }

    /**
     * Guardar un nuevo edificio en la base de datos.
     */
    public function store(StoreRequest $request)
    {

        // Validación:
        // - Si se aplica prorrateo del condominio (radio "Sí") se envía 0
        // - Si no se aplica, se envía 1 y se debe seleccionar el tipo de prorrateo individual.
        $data = $request->validate([
            'nombre'            => 'required|string|max:255',
            'id_condominio'     => 'required|integer|exists:condominios,id_condominio',
            'aplica_prorrateo'  => 'required|integer|in:0,1',
            'tipo_prorrateo_id' => 'nullable|integer|exists:tipo_prorrateo,id'
        ]);

        Edificio::create($data);

        return to_route('edificios.index');
    }

    /**
     * Formulario para editar un edificio.
     */
    public function edit(Edificio $edificio)
    {
        // Cargamos la relación de condominio
        $edificio->load('condominio');

        $condominios = Condominio::select('id_condominio as id', 'nombre')->get();
        $tipoProrrateo = TipoProrrateo::all();

        // Mapping de prorrateo condominio (para el condominio asignado)
        $prorrateoCondominio = DB::table('tipo_prorrateo_condominios')
            ->join('tipo_prorrateo', 'tipo_prorrateo_condominios.id_tipo_prorrateo', '=', 'tipo_prorrateo.id')
            ->select('tipo_prorrateo_condominios.id_condominio', 'tipo_prorrateo.descripcion')
            ->where('tipo_prorrateo_condominios.estado', 1)
            ->get()
            ->keyBy('id_condominio');

        return Inertia::render('Edificios/Edit', [
            'edificio'          => $edificio,
            'condominios'       => $condominios,
            'tipoProrrateo'     => $tipoProrrateo,
            'prorrateoCondominio' => $prorrateoCondominio,
        ]);
    }

    /**
     * Actualizar edificio en la base de datos.
     */
    public function update(Request $request, Edificio $edificio)
    {
        $data = $request->validate([
            'nombre'            => 'required|string|max:255',
            'id_condominio'     => 'required|integer|exists:condominios,id_condominio',
            'aplica_prorrateo'  => 'required|integer|in:0,1',
            'tipo_prorrateo_id' => 'nullable|integer|exists:tipo_prorrateo,id'
        ]);

        $edificio->update($data);

        return to_route('edificios.index');
    }

    /**
     * Eliminar un edificio.
     */
    public function destroy(Edificio $edificio)
    {
        if ($edificio->unidades()->exists()) {
            return to_route('edificios.index')->with('error', 'No se puede eliminar este edificio porque tiene unidades asignadas.');
        }

        $edificio->delete();

        return to_route('edificios.index')->with('success', 'Edificio eliminado correctamente.');
    }
}
