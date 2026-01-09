<?php

namespace App\Http\Controllers;

use App\Models\ConfigConsumo;
use App\Models\Condominio;
use App\Models\TipoGastoComun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ConfigConsumoController extends Controller
{
    // ======================
    //   INDEX
    // ======================
    public function index()
    {
        // 1) Se identifica el condominio actual o se obtiene lista de condominios si es superadmin
        $condominioId = Auth::user()->id_condominio;

        // 2) Consulta de las configuraciones (paginadas o no)
        $query = ConfigConsumo::with(['tipoGasto', 'condominio'])
            ->orderBy('id_config', 'desc');

        // Si el usuario no es superadmin, filtramos al condominio actual
        if ($condominioId) {
            $query->where('id_condominio', $condominioId);
        }

        $configuraciones = $query->paginate(10);

        // 3) Cargar lista de TipoGastoComun -> solo los que consumo=1
        $tiposConsumoQuery = TipoGastoComun::where('consumo', true);
        if ($condominioId) {
            $tiposConsumoQuery->where('id_condominio', $condominioId);
        }
        $tiposConsumo = $tiposConsumoQuery->get();

        // 4) Condominios para superadmin
        $condominios = [];
        if (!$condominioId) {
            $condominios = Condominio::select('id_condominio', 'nombre')->get();
        }

        return Inertia::render('MontodeServicios/Index', [
            'configuraciones' => $configuraciones,
            'tiposConsumo'    => $tiposConsumo,
            'condominios'     => $condominios,
        ]);
    }

    // ======================
    //   STORE
    // ======================
    public function store(Request $request)
    {
        // Validar (ahora se validan ambos campos: precio en soles y en dólares)
        $rules = [
            'id_tipo_gasto'   => 'required|exists:tipo_gasto_comun,id_tipo_gasto',
            'precio'          => 'required|numeric|min:0',
            'precio_dolares'  => 'required|numeric|min:0',
        ];

        // Solo si es superadmin (sin id_condominio) pedimos id_condominio
        if (!Auth::user()->id_condominio) {
            $rules['id_condominio'] = 'required|exists:condominios,id_condominio';
        }

        $data = $request->validate($rules);

        // Forzamos el id_condominio para usuarios no superadmin
        if (Auth::user()->id_condominio) {
            $data['id_condominio'] = Auth::user()->id_condominio;
        }

        // Crear la nueva configuración
        ConfigConsumo::create($data);

        return redirect()->back()->with('success', 'Configuración creada exitosamente');
    }

    // ======================
    //   UPDATE
    // ======================
    public function update(Request $request, $id)
    {
        $config = ConfigConsumo::findOrFail($id);

        $rules = [
            'id_tipo_gasto'   => 'required|exists:tipo_gasto_comun,id_tipo_gasto',
            'precio'          => 'required|numeric|min:0',
            'precio_dolares'  => 'required|numeric|min:0',
        ];

        // Si es superadmin, puede actualizar el id_condominio también
        if (!Auth::user()->id_condominio) {
            $rules['id_condominio'] = 'required|exists:condominios,id_condominio';
        }

        $data = $request->validate($rules);

        if (Auth::user()->id_condominio) {
            $data['id_condominio'] = Auth::user()->id_condominio;
        }

        $config->update($data);

        return redirect()->back()->with('success', 'Configuración actualizada exitosamente');
    }

    // ======================
    //   DESTROY
    // ======================
    public function destroy($id)
    {
        $config = ConfigConsumo::findOrFail($id);

        try {
            $config->delete();
            return redirect()->back()->with('success', 'Configuración eliminada correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar la configuración: ' . $e->getMessage());
        }
    }
}
