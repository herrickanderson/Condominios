<?php

namespace App\Http\Controllers;

use App\Models\ConfPago;
use App\Models\Condominio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ConfPagoController extends Controller
{
    public function index(Request $request)
    {
        // Si el usuario es superadmin, su id_condominio es null
        $userCondominio = Auth::user()->id_condominio;

        // Para superadmin se puede filtrar por condominio mediante el request; para usuarios normales se forzará su condominio
        $selectedCondominio = $userCondominio ?: $request->input('condominio_id');

        // Consulta de configuraciones filtradas según el condominio seleccionado (si existe)
        $query = ConfPago::query();
        if ($selectedCondominio) {
            $query->where('id_condominio', $selectedCondominio);
        }
        $configs = $query->orderByDesc('activo')->get();

        // Si es superadmin, se obtiene la lista de condominios para el filtro; de lo contrario, se pasa un array vacío
        $condominios = [];
        if (!$userCondominio) {
            $condominios = Condominio::select('id_condominio', 'nombre')->orderBy('nombre')->get();
        }

        return Inertia::render('DatosAdministrador/Index', [
            'configs'            => $configs,
            'awsBaseUrl'         => env('VITE_AWS_URL'),
            'condominios'        => $condominios, // Esto será un array o array vacío
            'selectedCondominio' => $selectedCondominio,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'banco'         => 'required|string',
            'tipo_cuenta'   => 'required|string',
            'numero_cuenta' => 'required|string',
            'cci'           => 'nullable|string',
            'propietario'   => 'required|string',
            'telefono'      => 'nullable|string',
            'direccion'     => 'nullable|string',
            'observaciones' => 'nullable|string',
            'qr'            => 'nullable|file|mimes:jpg,png,jpeg|max:2048',
        ]);

        // Si el usuario no es superadmin se asigna su id_condominio; de lo contrario, se espera que venga en el formulario
        if (Auth::user()->id_condominio) {
            $data['id_condominio'] = Auth::user()->id_condominio;
        }

        // Desactivar otras configuraciones activas para el condominio
        ConfPago::where('id_condominio', $data['id_condominio'])->update(['activo' => false]);

        if ($request->hasFile('qr')) {
            $file = $request->file('qr');
            $path = $file->store("datosUsuarios/qr", 's3');
            $data['qr_path'] = $path;
        }

        ConfPago::create($data);

        return redirect()->back()->with('success', 'Configuración creada correctamente.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'banco'         => 'required|string',
            'tipo_cuenta'   => 'required|string',
            'numero_cuenta' => 'required|string',
            'cci'           => 'nullable|string',
            'propietario'   => 'required|string',
            'telefono'      => 'nullable|string',
            'direccion'     => 'nullable|string',
            'observaciones' => 'nullable|string',
            'qr'            => 'nullable|image|max:2048',
        ]);

        $conf = ConfPago::findOrFail($id);
        $conf->fill($validated);

        if ($request->hasFile('qr')) {
            if ($conf->qr_path) {
                Storage::disk('s3')->delete($conf->qr_path);
            }
            $path = $request->file('qr')->store("datosUsuarios/qr", 's3');
            $conf->qr_path = $path;
        }

        $conf->save();

        return redirect()->back()->with('success', 'Configuración actualizada correctamente.');
    }

    public function toggle($id)
    {
        $config = ConfPago::findOrFail($id);
        ConfPago::where('id_condominio', $config->id_condominio)->update(['activo' => false]);
        $config->update(['activo' => true]);

        return redirect()->back()->with('success', 'Configuración activada.');
    }

    public function destroy($id)
    {
        $config = ConfPago::findOrFail($id);
        if ($config->qr_path) {
            Storage::disk('s3')->delete($config->qr_path);
        }
        $config->delete();

        return redirect()->back()->with('success', 'Configuración eliminada.');
    }
}
