<?php

namespace App\Http\Controllers;

use App\Models\ConfMora;
use App\Models\Condominio;
use App\Models\ConfiguracionPeriodo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ConfMoraController extends Controller
{
    public function index(Request $request)
    {
        // Si el usuario es administrador, su id_condominio está en el usuario; si es superadmin, se puede filtrar vía request.
        $userCondominio = Auth::user()->id_condominio;
        $isSuperAdmin = $userCondominio ? false : true;
        $selectedCondominio = $userCondominio ?: $request->input('condominio_id');

        // Si no se seleccionó condominio (en superadmin) se asigna el primero de la lista de condominios
        if (!$selectedCondominio) {
            $firstCondo = Condominio::orderBy('nombre')->first();
            $selectedCondominio = $firstCondo ? $firstCondo->id_condominio : null;
        }

        // Consultar configuraciones de mora filtradas por condominio, cargando la relación para obtener el nombre
        $configs = ConfMora::with('condominio')
            ->where('id_condominio', $selectedCondominio)
            ->orderByDesc('created_at')
            ->get();

        // Obtener la configuración de período activa para ese condominio
        $periodConfig = ConfiguracionPeriodo::where('idcondominio', $selectedCondominio)
                            ->where('estado', 1)
                            ->first();

        // Si el usuario es superadmin, obtener la lista de condominios para el filtro
        $condominios = [];
        if ($isSuperAdmin) {
            $condominios = Condominio::select('id_condominio', 'nombre')->orderBy('nombre')->get();
        }

        return Inertia::render('ConfMora/Index', [
            'configs'            => $configs,
            'periodConfig'       => $periodConfig,
            'condominios'        => $condominios,
            'selectedCondominio' => $selectedCondominio,
            'isSuperAdmin'       => $isSuperAdmin,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo_periodo' => 'required|in:diario,semanal,mensual',
            'porcentaje'   => 'required|numeric|min:0',
        ]);

        if (Auth::user()->id_condominio) {
            $data['id_condominio'] = Auth::user()->id_condominio;
        } else {
            $data['id_condominio'] = $request->input('id_condominio');
        }

        ConfMora::create($data);

        return redirect()->back()->with('success', 'Configuración de mora creada correctamente.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'tipo_periodo' => 'required|in:diario,semanal,mensual',
            'porcentaje'   => 'required|numeric|min:0',
        ]);

        $config = ConfMora::findOrFail($id);
        $config->update($validated);

        return redirect()->back()->with('success', 'Configuración de mora actualizada correctamente.');
    }

    public function destroy($id)
    {
        $config = ConfMora::findOrFail($id);
        $config->delete();

        return redirect()->back()->with('success', 'Configuración de mora eliminada.');
    }
}
