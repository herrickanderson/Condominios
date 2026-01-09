<?php

namespace App\Http\Controllers;

use App\Models\DetalleGastoComun;
use App\Models\GastosComune;
use App\Models\TipoGastoComun;
use App\Models\Edificio;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class detalle_gasto_comunController extends Controller
{
    public function index()
    {
        $detalles = DetalleGastoComun::with([
            'gastos_comune',
            'tipo_gasto_comun',
            'targetTorre',
            'targetUnidad'
        ])
            ->withCount('distribucion_gastos')
            ->when(Auth::user()->id_condominio, function ($query) {
                $query->whereHas('gastos_comune', function ($q) {
                    $q->where('id_condominio', Auth::user()->id_condominio);
                });
            })
            ->paginate(10);

        return Inertia::render('DetalleGastoComun/Index', [
            'detalles' => $detalles,
        ]);
    }

    public function create()
    {
        // IMPORTANTE: Para que esto funcione,
        // en GastosComune debes tener la relación: public function detalle_gasto_comuns()
        // y en DetalleGastoComun: public function distribucion_gastos()
        // así Eloquent puede hacer whereDoesntHave('detalle_gasto_comuns.distribucion_gastos').

        $gastos = GastosComune::select('id_gasto', 'descripcion', 'monto_total')
            ->withSum('detalle_gasto_comuns as monto_asignado', 'monto_detalle')
            ->whereDoesntHave('detalle_gasto_comuns.distribucion_gastos')
            ->when(Auth::user()->id_condominio, function ($query) {
                $query->where('id_condominio', Auth::user()->id_condominio);
            })
            ->get();

        $tipos = TipoGastoComun::select('id_tipo_gasto', 'nombre')
            ->where('id_condominio', Auth::user()->id_condominio)
            ->get();

        $edificios = Edificio::select('id_edificio', 'nombre')
            ->when(Auth::user()->id_condominio, function ($query) {
                $query->where('id_condominio', Auth::user()->id_condominio);
            })
            ->get();

        $unidades = Unidade::select('id_unidad', 'nombre_unidad', 'id_edificio')
            ->when(Auth::user()->id_condominio, function ($query) {
                $query->where('id_condominio', Auth::user()->id_condominio);
            })
            ->get();

        return Inertia::render('DetalleGastoComun/Create', [
            'gastos'    => $gastos,
            'tipos'     => $tipos,
            'edificios' => $edificios,
            'unidades'  => $unidades,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_gasto'            => 'required|exists:gastos_comunes,id_gasto',
            'id_tipo_gasto'       => 'required|exists:tipo_gasto_comun,id_tipo_gasto',
            'id_edificio'         => 'nullable|exists:edificios,id_edificio',
            'monto_detalle'       => 'required|numeric|min:0',
            'file_url'            => 'nullable|file|max:2048',
            'nombre_file'         => 'nullable|string|max:100',
            'observacion'         => 'nullable|string',
            'descripcion_detalle' => 'nullable|string',
            'distribution_scope'  => 'required|string|in:condominium,tower,unit',
            'target_tower'        => 'nullable|exists:edificios,id_edificio',
            'target_unit'         => 'nullable|exists:unidades,id_unidad',
        ]);

        // Verificar duplicado
        $existe = DetalleGastoComun::where('id_gasto', $validated['id_gasto'])
            ->where('id_tipo_gasto', $validated['id_tipo_gasto'])
            ->where('id_edificio', $validated['id_edificio'] ?? null)
            ->where('distribution_scope', $validated['distribution_scope'])
            ->when($validated['distribution_scope'] === 'tower', function ($query) use ($validated) {
                return $query->where('target_tower', $validated['target_tower']);
            })
            ->when($validated['distribution_scope'] === 'unit', function ($query) use ($validated) {
                return $query->where('target_unit', $validated['target_unit']);
            })
            ->exists();

        if ($existe) {
            return back()->withErrors([
                'id_tipo_gasto' => 'Este tipo de gasto y ámbito ya está asignado a este Gasto Común.',
            ])->withInput();
        }

        if ($request->hasFile('file_url')) {
            $baseFolder = env('S3_ENV_FOLDER', 'produccion');
            $folderPath = $baseFolder . '/Doc_generales/gastosComun';
            $file = $request->file('file_url');
            $path = $file->store($folderPath, 's3');
            $validated['file_url'] = $path;
        }

        DetalleGastoComun::create($validated);

        return redirect()->route('detalle_gasto_comun.index')
            ->with('success', '¡Detalle creado exitosamente!');
    }

    public function edit(DetalleGastoComun $detalle)
    {
        // Si el detalle ya tiene distribuciones, no se puede editar
        if ($detalle->distribucion_gastos()->exists()) {
            return redirect()->route('detalle_gasto_comun.index')
                ->with('error', 'No se puede editar este detalle, ya está distribuido.');
        }

        $detalle->load('targetTorre', 'targetUnidad');

        $gastos = GastosComune::select('id_gasto', 'descripcion', 'monto_total')
            ->withSum('detalle_gasto_comuns as monto_asignado', 'monto_detalle')
            ->get();

        $tipos = TipoGastoComun::select('id_tipo_gasto', 'nombre')
            ->where('id_condominio', Auth::user()->id_condominio)
            ->get();

        $edificios = Edificio::select('id_edificio', 'nombre')
            ->when(Auth::user()->id_condominio, function ($query) {
                $query->where('id_condominio', Auth::user()->id_condominio);
            })
            ->get();

        $unidades = Unidade::select('id_unidad', 'nombre_unidad', 'id_edificio')
            ->when(Auth::user()->id_condominio, function ($query) {
                $query->where('id_condominio', Auth::user()->id_condominio);
            })
            ->get();

        return Inertia::render('DetalleGastoComun/Edit', [
            'detalle'   => $detalle,
            'gastos'    => $gastos,
            'tipos'     => $tipos,
            'edificios' => $edificios,
            'unidades'  => $unidades,
        ]);
    }

    public function update(Request $request, DetalleGastoComun $detalle)
    {
        if ($detalle->distribucion_gastos()->exists()) {
            return redirect()->route('detalle_gasto_comun.index')
                ->with('error', 'No se puede actualizar este detalle, ya está distribuido.');
        }

        $validated = $request->validate([
            'id_gasto'            => 'required|exists:gastos_comunes,id_gasto',
            'id_tipo_gasto'       => 'required|exists:tipo_gasto_comun,id_tipo_gasto',
            'id_edificio'         => 'nullable|exists:edificios,id_edificio',
            'monto_detalle'       => 'required|numeric|min:0',
            'file_url'            => 'nullable|file|max:2048',
            'nombre_file'         => 'nullable|string|max:100',
            'observacion'         => 'nullable|string',
            'descripcion_detalle' => 'nullable|string',
            'distribution_scope'  => 'required|string|in:condominium,tower,unit',
            'target_tower'        => 'nullable|exists:edificios,id_edificio',
            'target_unit'         => 'nullable|exists:unidades,id_unidad',
        ]);

        $data = $request->except('file_url');

        if ($request->hasFile('file_url')) {
            $baseFolder = env('S3_ENV_FOLDER', 'produccion');
            $folderPath = $baseFolder . '/Doc_generales/gastosComun';
            $file = $request->file('file_url');
            $newPath = $file->store($folderPath, 's3');
            $data['file_url'] = $newPath;

            if ($detalle->file_url) {
                Storage::disk('s3')->delete($detalle->file_url);
            }
        }

        $detalle->update($data);

        return to_route('detalle_gasto_comun.index')
            ->with('success', '¡Detalle de gasto actualizado exitosamente!');
    }

    public function destroy(DetalleGastoComun $detalle)
    {
        if ($detalle->distribucion_gastos()->exists()) {
            return redirect()->route('detalle_gasto_comun.index')
                ->with('error', 'No se puede eliminar este detalle, ya está distribuido.');
        }

        if ($detalle->file_url) {
            Storage::disk('s3')->delete($detalle->file_url);
        }

        $detalle->delete();

        return to_route('detalle_gasto_comun.index')
            ->with('success', '¡Detalle de gasto eliminado exitosamente!');
    }

    // Mapeo del front (Español) a la BD (Inglés)
    private $scopeMap = [
        'Condominio' => 'condominium',
        'Torre'      => 'tower',
        'Unidad'     => 'unit',
    ];

    public function storeMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_gasto' => 'required|exists:gastos_comunes,id_gasto',
            // CAMBIO: ya no 'required|array', sino 'nullable|array'
            'detalles' => 'nullable|array',  
            'detalles.*.scope' => 'required|string|in:Condominio,Torre,Unidad',
            'detalles.*.tipo.id_tipo_gasto' => 'required|integer|exists:tipo_gasto_comun,id_tipo_gasto',
            'detalles.*.amount' => 'required|numeric|min:0',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }
    
        DB::beginTransaction();
        try {
            $idGasto = $request->id_gasto;
    
            // CAMBIO: si 'detalles' no existe o es null, usamos un array vacío
            $detallesRequest = $request->input('detalles', []);
    
            // 1. Obtener los detalles existentes para este gasto
            $existingDetails = DetalleGastoComun::where('id_gasto', $idGasto)->get();
    
            // 2. IDs de los detalles que sí vienen en la request
            $requestIds = collect($detallesRequest)->pluck('id')->filter()->all();
    
            // 3. Eliminar de la BD solo los detalles 'manual' que ya no vienen
            //    y NO tienen distribuciones.
            foreach ($existingDetails as $detalle) {
                if ($detalle->source === 'manual') {
                    if (! in_array($detalle->id_detalle, $requestIds)
                        && ! $detalle->distribucion_gastos()->exists()
                    ) {
                        $detalle->delete();
                    }
                }
            }
    
            // 4. Crear o actualizar los que llegan en la request
            foreach ($detallesRequest as $d) {
                $scopeBD = $this->scopeMap[$d['scope']] ?? 'condominium';
                $idTipo  = $d['tipo']['id_tipo_gasto'];
                $amount  = $d['amount'];
                $desc    = $d['description'] ?? null;
    
                // Convertir 'Torre','Unidad' => se traduce a tower/unit
                $tower = in_array($d['scope'], ['Torre','Unidad']) ? ($d['tower'] ?? null) : null;
                $unit  = ($d['scope'] === 'Unidad') ? ($d['unit'] ?? null) : null;
    
                // Quitar strings "null" o vacíos que rompen con Postgres
                if ($tower === '' || $tower === 'null') $tower = null;
                if ($unit  === '' || $unit  === 'null')  $unit  = null;
    
                // Determinar si es creación o edición
                $fakeId = true;
                if (isset($d['id'])) {
                    $strId = (string) $d['id'];
                    if (ctype_digit($strId)) {
                        $fakeId = false; // es un ID real
                    }
                }
    
                if (! $fakeId) {
                    // ACTUALIZAR
                    $detalleBD = DetalleGastoComun::find($d['id']);
                    if ($detalleBD && ! $detalleBD->distribucion_gastos()->exists()) {
                        $detalleBD->update([
                            'id_tipo_gasto'       => $idTipo,
                            'monto_detalle'       => $amount,
                            'descripcion_detalle' => $desc,
                            'distribution_scope'  => $scopeBD,
                            'target_tower'        => $tower,
                            'target_unit'         => $unit,
                            'source'              => 'manual',
                        ]);
                    } else {
                        // o creamos uno nuevo
                        DetalleGastoComun::create([
                            'id_gasto'           => $idGasto,
                            'id_tipo_gasto'      => $idTipo,
                            'monto_detalle'      => $amount,
                            'descripcion_detalle'=> $desc,
                            'distribution_scope' => $scopeBD,
                            'target_tower'       => $tower,
                            'target_unit'        => $unit,
                            'source'             => 'manual',
                        ]);
                    }
                } else {
                    // CREAR
                    DetalleGastoComun::create([
                        'id_gasto'           => $idGasto,
                        'id_tipo_gasto'      => $idTipo,
                        'monto_detalle'      => $amount,
                        'descripcion_detalle'=> $desc,
                        'distribution_scope' => $scopeBD,
                        'target_tower'       => $tower,
                        'target_unit'        => $unit,
                        'source'             => 'manual',
                    ]);
                }
            }
    
            // 5. Recalcular la suma total
            $suma = DetalleGastoComun::where('id_gasto', $idGasto)->sum('monto_detalle');
            DB::table('gastos_comunes')
                ->where('id_gasto', $idGasto)
                ->update(['monto_total' => $suma]);
    
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Detalles guardados correctamente.'
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }
    
}
