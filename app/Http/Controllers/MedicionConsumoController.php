<?php

namespace App\Http\Controllers;

use App\Models\MedicionConsumo;
use App\Models\TipoGastoComun;
use App\Models\Unidade;
use App\Models\Extencion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MedicionConsumoController extends Controller
{
    // =====================================================
    // INDEX (listado de servicios pendientes por estado)
    // =====================================================
    public function index()
    {
        $condominioId = Auth::user()->id_condominio;

        // 1) Torres/Edificios
        $torres = \App\Models\Edificio::where('id_condominio', $condominioId)
            ->select('id_edificio as id', 'nombre')
            ->get();

        // 2) Servicios que aplican consumo
        $servicios = TipoGastoComun::where('id_condominio', $condominioId)
            ->where('consumo', 1)
            ->get();

        // 3) Unidades con usuario activo
        $unidades = Unidade::with(['users' => function($q) {
                $q->where('estado', 1);
            }])
            ->where('id_condominio', $condominioId)
            ->select('id_unidad','nombre_unidad','id_edificio','tipo_unidad')
            ->get();

        // =====================================================
        // Filtramos servicios en UNIDADES
        // =====================================================
        $pendientes_unidades = $unidades->map(function($unidad) use ($servicios) {
            $firstUser = $unidad->users->first();
            $occupantName = $firstUser ? $firstUser->name : null;

            // Para cada servicio, chequeamos la última medición:
            $filtered = $servicios->filter(function($service) use ($unidad) {
                // Última medición para (unidad, servicio)
                $lastMed = MedicionConsumo::where('id_unidad', $unidad->id_unidad)
                    ->where('id_tipo_gasto', $service->id_tipo_gasto)
                    ->orderBy('id', 'desc')
                    ->first();

                if (!$lastMed) {
                    return true;
                }

                $statusLower = strtolower($lastMed->status);
                if (in_array($statusLower, ['pendiente','consumido'])) {
                    return false;
                }

                return true;
            });

            $unidad->services     = $filtered->values();
            $unidad->occupantName = $occupantName;

            return $unidad;
        })->filter(function($u){
            return $u->services->count() > 0;
        })->values();

        // =====================================================
        // Filtramos servicios en EXTENSIONES (Bodegas/Estac.)
        // =====================================================
        $extenciones = Extencion::where('id_condominio', Auth::user()->id_condominio)
            ->whereIn('tipo_extencion', ['Bodega','Estacionamiento'])
            ->whereExists(function($q){
                $q->select(DB::raw(1))
                    ->from('extenciones_usuarios')
                    ->join('users','extenciones_usuarios.user_id','=','users.id')
                    ->whereColumn('extenciones_usuarios.id_extencion','extenciones.id_extencion')
                    ->where('users.estado',1);
            })
            ->select('id_extencion','nombre','id_edificio','tipo_extencion','area','cobro_unico')
            ->get();

        $pendientes_extenciones = $extenciones->map(function($ext) use ($servicios) {
            $userRow = DB::table('extenciones_usuarios')
                ->join('users','extenciones_usuarios.user_id','=','users.id')
                ->where('extenciones_usuarios.id_extencion',$ext->id_extencion)
                ->where('users.estado',1)
                ->select('users.name')
                ->first();
            $occupantName = $userRow ? $userRow->name : null;

            $filtered = $servicios->filter(function($service) use ($ext) {
                if ($ext->cobro_unico == 1) {
                    if (strtolower($service->nombre) !== 'luz') {
                        return false;
                    }
                }
                $lastMed = MedicionConsumo::where('id_extencion', $ext->id_extencion)
                    ->where('id_tipo_gasto', $service->id_tipo_gasto)
                    ->orderBy('id','desc')
                    ->first();

                if (!$lastMed) {
                    return true;
                }

                $statusLower = strtolower($lastMed->status);
                if (in_array($statusLower, ['pendiente','consumido'])) {
                    return false;
                }

                return true;
            });

            $ext->services     = $filtered->values();
            $ext->occupantName = $occupantName;

            return $ext;
        })->filter(function($ext){
            return $ext->services->count() > 0;
        })->values();

        return Inertia::render('Mediciones/Index', [
            'torres' => $torres,
            'pendientes_unidades' => $pendientes_unidades,
            'pendientes_extenciones' => $pendientes_extenciones,
            'condominioId' => $condominioId
        ]);
    }

    // =====================================================
    // STORE (guardar nueva medición)
    // =====================================================
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_condominio'    => 'required|integer',
            'id_unidad'        => 'nullable|integer',
            'id_extencion'     => 'nullable|integer',
            'id_tipo_gasto'    => 'required|integer',
            'fecha_medicion'   => 'required|date',
            'lectura_anterior' => 'nullable|numeric',
            'lectura_actual'   => 'required|numeric',
            'observacion'      => 'nullable|string',
            'archivo'          => 'nullable|file|mimes:jpg,jpeg,png,pdf',
        ]);

        // Antes de crear, buscamos última medición de (unidad o extención) + id_tipo_gasto
        $query = MedicionConsumo::where('id_condominio', $data['id_condominio'])
            ->where('id_tipo_gasto', $data['id_tipo_gasto']);

        if (!empty($data['id_unidad'])) {
            $query->where('id_unidad', $data['id_unidad']);
        }
        if (!empty($data['id_extencion'])) {
            $query->where('id_extencion', $data['id_extencion']);
        }

        $lastMed = $query->orderBy('id','desc')->first();

        if ($lastMed) {
            $statusLower = strtolower($lastMed->status);
            if (in_array($statusLower, ['pendiente','consumido'])) {
                return redirect()->route('mediciones.index')
                    ->with('error','Ya existe una medición con estado pendiente o consumido. Debe cambiarse a pagado o mora para poder ingresar otra.')
                    ->withInput();
            }
        }

        if (isset($data['lectura_anterior']) && $data['lectura_anterior'] !== null) {
            if ($data['lectura_actual'] <= $data['lectura_anterior']) {
                return redirect()->route('mediciones.index')
                    ->with('error','La Lectura Actual debe ser mayor que la Anterior.')
                    ->withInput();
            }
        }

        // Si se subió un archivo, almacenarlo en S3 en carpeta "mediciones"
        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $folderPath = env('S3_ENV_FOLDER', 'produccion') . '/mediciones';
            $routeName = $file->store($folderPath, 's3');
            $data['archivo'] = $routeName;
        }

        $data['status']  = 'pendiente';
        $data['id_user'] = Auth::id();
        $data['consumo'] = $data['lectura_anterior']
            ? ($data['lectura_actual'] - $data['lectura_anterior'])
            : null;

        MedicionConsumo::create($data);

        return redirect()->route('mediciones.index')
            ->with('success','Medición guardada exitosamente.');
    }

    // =====================================================
    // Cambio de estado manual (setEstado)
    // =====================================================
    public function setEstado(Request $request, MedicionConsumo $medicione)
    {
        $nuevoEstado = strtolower($request->input('status'));
        $validos = ['pendiente','consumido','pagado','mora'];

        if (!in_array($nuevoEstado, $validos)) {
            return back()->with('error','Estado inválido');
        }
        $medicione->update(['status' => $nuevoEstado]);
        return back()->with('success',"Estado cambiado a: $nuevoEstado");
    }

    // =====================================================
    // UPDATE / DESTROY
    // =====================================================
    public function update(Request $request, MedicionConsumo $medicione)
    {
        $data = $request->validate([
            'fecha_medicion'   => 'required|date',
            'lectura_anterior' => 'nullable|numeric',
            'lectura_actual'   => 'required|numeric',
            'observacion'      => 'nullable|string',
        ]);

        if (!empty($data['lectura_anterior'])) {
            if ($data['lectura_actual'] <= $data['lectura_anterior']) {
                return redirect()->route('mediciones.index')
                    ->with('error','La Lectura Actual debe ser mayor que la Anterior.')
                    ->withInput();
            }
        }

        $data['consumo'] = !empty($data['lectura_anterior'])
            ? ($data['lectura_actual'] - $data['lectura_anterior'])
            : null;

        $medicione->update($data);

        return redirect()->route('mediciones.index')
            ->with('success','Medición actualizada exitosamente.');
    }

    public function destroy(MedicionConsumo $medicione)
    {
        $medicione->delete();
        return redirect()->route('mediciones.index')
            ->with('success','Medición eliminada correctamente.');
    }
}
