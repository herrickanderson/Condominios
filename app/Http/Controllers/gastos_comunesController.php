<?php

namespace App\Http\Controllers;

use App\Jobs\SendExpenseDistributionMail;
use App\Models\GastosComune;
use App\Models\Condominio;
use App\Models\DetalleGastoComun;
use App\Models\DistribucionGasto;
use App\Models\Edificio;
use App\Models\TipoGastoComun;
use App\Models\Unidade;
use App\Models\UnidadServicioExtra;
use App\Models\Extencion;
use App\Models\MedicionConsumo;
use App\Models\Usuario;
use App\Models\ConfiguracionPeriodo;
use App\Models\ConfigConsumo; // Modelo para la tabla 'config_consumos'
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\ExpenseDistributionMail;
use Illuminate\Support\Facades\Mail;

class gastos_comunesController extends Controller
{
    // ========================================
    // ================ INDEX =================
    // ========================================
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = GastosComune::query();

        // Filtrar por condominio del usuario
        if (Auth::user()->id_condominio) {
            $query->where('id_condominio', Auth::user()->id_condominio);
        }
        if ($search) {
            $query->where('descripcion', 'like', "%{$search}%");
        }

        // Cargar datos
        $gastos = $query->with([
            'distribuciones.unidad.edificio',
            'detalle_gasto_comuns.tipo_gasto_comun',
            'detalle_gasto_comuns.targetTorre',
            'detalle_gasto_comuns.targetUnidad',
            'detalle_gasto_comuns.targetExtencion',
        ])
            ->withCount('distribuciones')
            ->orderBy('id_gasto', 'desc')
            ->paginate(10)
            ->appends(['search' => $search]);

        // Para mostrar en modal “Agregar Detalles”
        $tipos = TipoGastoComun::where('id_condominio', Auth::user()->id_condominio)->get();
        $edificios = Edificio::where('id_condominio', Auth::user()->id_condominio)->get();
        $unidades = Unidade::where('id_condominio', Auth::user()->id_condominio)->get();

        return Inertia::render('GastosComunes/Index', [
            'gastos'    => $gastos,
            'filters'   => ['search' => $search],
            'tipos'     => $tipos,
            'edificios' => $edificios,
            'unidades'  => $unidades,
        ]);
    }

    // ========================================
    // ================ CREATE ================
    // ========================================
    public function create()
    {
        if (!Auth::user()->id_condominio) {
            // Usuario no vinculado a un condominio
            $condominios = Condominio::select('id_condominio as id', 'nombre')->get();
            $activeConfig = null;
        } else {
            $condominios = [];
            $activeConfig = ConfiguracionPeriodo::where('idcondominio', Auth::user()->id_condominio)
                ->where('estado', 1)
                ->first();
        }

        return Inertia::render('GastosComunes/Create', [
            'condominios' => $condominios,
            'activeConfig' => $activeConfig,
        ]);
    }

    // ========================================
    // ================ STORE =================
    // ========================================
    public function store(Request $request)
    {
        $rules = [
            'descripcion'       => 'required|string|max:255',
            'tipo_moneda'       => 'required|string|in:Soles,Dolares',
            'fecha_periodo'     => 'required|date',
            'fecha_inicio'      => 'required|date',
            'fecha_fin'         => 'required|date',
            'fecha_vencimiento' => 'required|date',
            'estado_pago'       => 'nullable|string',
            'tipo_cobro'        => 'nullable|string',
            'id_tipo_prorrateo' => 'nullable|integer',
        ];

        if (!Auth::user()->id_condominio) {
            // debe enviar id_condominio
            $rules['id_condominio'] = 'required|exists:condominios,id_condominio';
        }

        $validated = $request->validate($rules);

        if (Auth::user()->id_condominio) {
            $validated['id_condominio'] = Auth::user()->id_condominio;
        }

        // Forzamos monto_total = 0
        $validated['monto_total'] = 0;

        // Evitar duplicado en el mismo mes
        $year = date('Y', strtotime($validated['fecha_inicio']));
        $month = date('m', strtotime($validated['fecha_inicio']));
        $exists = GastosComune::whereYear('fecha_inicio', $year)
            ->whereMonth('fecha_inicio', $month)
            ->where('id_condominio', $validated['id_condominio'])
            ->exists();
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ya existe un gasto común creado en el mes seleccionado.');
        }

        GastosComune::create($validated);

        return redirect()->route('gastos_comunes.index')
            ->with('success', '¡Gasto común creado exitosamente!');
    }

    // ========================================
    // ================ EDIT ==================
    // ========================================
    public function edit(GastosComune $gasto)
    {
        // No editar si ya tiene distribuciones
        if ($gasto->distribuciones()->exists()) {
            return redirect()->route('gastos_comunes.index')
                ->with('error', 'No se puede editar el gasto, ya está distribuido.');
        }

        if (!Auth::user()->id_condominio) {
            $condominios = Condominio::select('id_condominio as id', 'nombre')->get();
            $activeConfig = null;
        } else {
            $condominios = [];
            $activeConfig = ConfiguracionPeriodo::where('idcondominio', Auth::user()->id_condominio)
                ->where('estado', 1)
                ->first();
        }

        return Inertia::render('GastosComunes/Edit', [
            'gasto'         => $gasto,
            'condominios'   => $condominios,
            'activeConfig'  => $activeConfig,
        ]);
    }

    // ========================================
    // ================ UPDATE ================
    // ========================================
    public function update(Request $request, GastosComune $gasto)
    {
        // No actualizar si ya tiene distribuciones
        if ($gasto->distribuciones()->exists()) {
            return redirect()->route('gastos_comunes.index')
                ->with('error', 'No se puede actualizar el gasto, ya está distribuido.');
        }

        $rules = [
            'descripcion'       => 'required|string|max:255',
            'monto_total'       => 'required|numeric',
            'tipo_moneda'       => 'required|string|in:Soles,Dolares',
            'fecha_periodo'     => 'required|date',
            'fecha_inicio'      => 'required|date',
            'fecha_fin'         => 'required|date',
            'fecha_vencimiento' => 'required|date',
            'id_tipo_prorrateo' => 'nullable|integer',
        ];

        if (!Auth::user()->id_condominio) {
            $rules['id_condominio'] = 'required|exists:condominios,id_condominio';
        }

        $validated = $request->validate($rules);

        if (Auth::user()->id_condominio) {
            $validated['id_condominio'] = Auth::user()->id_condominio;
        }

        $gasto->update($validated);

        return redirect()->route('gastos_comunes.index')
            ->with('success', '¡Gasto común actualizado exitosamente!');
    }

    // ========================================
    // ============== DESTROY =================
    // ========================================
    public function destroy(GastosComune $gasto)
    {
        // No eliminar si ya tiene distribuciones
        if ($gasto->distribuciones()->exists()) {
            return redirect()->route('gastos_comunes.index')
                ->with('error', 'No se puede eliminar el gasto, ya está distribuido.');
        }

        try {
            $gasto->delete();
            return redirect()->route('gastos_comunes.index')
                ->with('success', '¡Gasto eliminado exitosamente!');
        } catch (\Exception $e) {
            return redirect()->route('gastos_comunes.index')
                ->with('error', 'Ocurrió un error al eliminar el gasto.');
        }
    }

    // ========================================
    // ========== CALCULAR DISTRIB  ===========
    // ========================================
    /**
     * Lógica de prorrateo para tu “Distribución Final”.
     * Reemplaza exactamente por la versión larga que ya tenías.
     * Se mantiene sin cambios, solo pegamos tu contenido original.
     */
    public function calculateDistribution($id_gasto)
    {
        $gasto = GastosComune::findOrFail($id_gasto);
        if ($gasto->id_condominio !== Auth::user()->id_condominio) {
            abort(403, 'No autorizado.');
        }
        $detalles = DetalleGastoComun::where('id_gasto', $gasto->id_gasto)->get();

        if ($detalles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay detalles para distribuir en este gasto.'
            ], 400);
        }

        $globalProrrateo = DB::table('tipo_prorrateo_condominios')
            ->where('id_condominio', $gasto->id_condominio)
            ->where('estado', 1)
            ->value('id_tipo_prorrateo') ?? 1;

        $allEdificios = Edificio::where('id_condominio', $gasto->id_condominio)->get();
        $existeProrrateoPorEdificio = $allEdificios->where('aplica_prorrateo', 1)->count() > 0;

        DB::beginTransaction();
        try {
            // Borramos distribuciones previas
            DistribucionGasto::whereIn('id_detalle', $detalles->pluck('id_detalle'))->delete();

            foreach ($detalles as $detalle) {
                $scope = $detalle->distribution_scope;
                $montoDetalle = $detalle->monto_detalle;

                // Filtrar “unidades”
                $unidades = $this->obtenerUnidadesParticipantes($detalle, $gasto);
                $countUnidades = $unidades->count();

                // si es extension
             /*   if ($scope === 'extension' && $detalle->target_extencion) {
                    DistribucionGasto::create([
                        'id_detalle'       => $detalle->id_detalle,
                        'id_unidad'        => null,
                        'id_extencion'     => $detalle->target_extencion,
                        'monto_asignado'   => $montoDetalle,
                        'fecha_vencimiento' => $gasto->fecha_vencimiento,
                    ]);
                    continue;
                }*/

                if ($scope === 'extension' && $detalle->target_extencion) {
                    $ext = Extencion::find($detalle->target_extencion);

                    // Verifica que la extensión exista y tenga al menos un usuario activo asociado
                    if ($ext && $ext->usuarios()->where('estado', 1)->exists()) {
                        DistribucionGasto::create([
                            'id_detalle'         => $detalle->id_detalle,
                            'id_unidad'          => null,
                            'id_extencion'       => $detalle->target_extencion,
                            'monto_asignado'     => $montoDetalle,
                            'fecha_vencimiento'  => $gasto->fecha_vencimiento,
                        ]);
                    }

                    continue;
                }





                // scope unit => una sola unidad
                if ($scope === 'unit' && $detalle->target_unit) {
                    DistribucionGasto::create([
                        'id_detalle'       => $detalle->id_detalle,
                        'id_unidad'        => $detalle->target_unit,
                        'monto_asignado'   => $montoDetalle,
                        'fecha_vencimiento' => $gasto->fecha_vencimiento,
                    ]);
                    continue;
                }

                // scope tower
                if ($scope === 'tower' && $detalle->target_tower) {
                    $ed = Edificio::find($detalle->target_tower);
                    if ($ed) {
                        $this->distribuirInternoTorre(
                            $detalle->id_detalle,
                            $ed,
                            $montoDetalle,
                            $detalle->id_tipo_gasto,
                            $globalProrrateo,
                            $gasto->fecha_vencimiento
                        );
                    }
                    continue;
                }

                // scope condominium
                if ($scope === 'condominium') {
                    if (!$existeProrrateoPorEdificio) {
                        // Reparto global
                        $unidades = $this->calcularPorcentajeBase($unidades, $globalProrrateo);
                        $unidades = $this->agregarPorcentajeExtra($unidades, $detalle->id_tipo_gasto);
                        $unidades = $this->ajustarPorcentajesFinales($unidades);

                        // Penny allocation algorithm
                        $totalMonto = $montoDetalle;
                        $montoAcumulado = 0;
                        $unidadesCount = $unidades->count();
                        $i = 0;

                        foreach ($unidades as $u) {
                            $i++;
                            if ($i == $unidadesCount) {
                                // Last unit gets the remaining amount
                                $montito = round($totalMonto - $montoAcumulado, 2);
                            } else {
                                $montito = round(($u->porcentaje_final / 100) * $totalMonto, 2);
                            }
                            
                            $montoAcumulado += $montito;

                            DistribucionGasto::create([
                                'id_detalle'       => $detalle->id_detalle,
                                'id_unidad'        => $u->id_unidad,
                                'monto_asignado'   => $montito,
                                'fecha_vencimiento' => $gasto->fecha_vencimiento,
                            ]);
                        }
                    } else {
                        // Se fracciona por torre
                        $totalUnidadesGlobal = $unidades->count();
                        $edificios = Edificio::where('id_condominio', $gasto->id_condominio)->get();

                        foreach ($edificios as $ed) {
                            $cantU = $unidades->where('id_edificio', $ed->id_edificio)->count();
                            if ($totalUnidadesGlobal == 0) {
                                continue;
                            }
                            $montoTorre = ($cantU / $totalUnidadesGlobal) * $montoDetalle;
                            $this->distribuirInternoTorre(
                                $detalle->id_detalle,
                                $ed,
                                $montoTorre,
                                $detalle->id_tipo_gasto,
                                $globalProrrateo,
                                $gasto->fecha_vencimiento
                            );
                        }
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al distribuir: ' . $e->getMessage(),
            ], 500);
        }

        // Lanza job asíncrono
        //SendExpenseDistributionMail::dispatch($gasto->id_gasto);
        SendExpenseDistributionMail::dispatch($gasto->id_gasto, env('VITE_AWS_URL'));


        return response()->json([
            'success' => true,
            'message' => '¡Distribución generada correctamente! Se enviarán correos en segundo plano.'
        ]);
    }

    public function validateDistribution($id_gasto)
    {
        $gasto = GastosComune::withCount('distribuciones')->findOrFail($id_gasto);
        if ($gasto->id_condominio !== Auth::user()->id_condominio) {
            abort(403, 'No autorizado.');
        }

        if ($gasto->distribuciones_count == 0) {
            return response()->json([
                'success' => false,
                'message' => 'El gasto aún no ha sido distribuido.'
            ], 400);
        }

        $distribuciones = DB::table('distribucion_gasto')
            ->join('detalle_gasto_comun', 'distribucion_gasto.id_detalle', '=', 'detalle_gasto_comun.id_detalle')
            ->where('detalle_gasto_comun.id_gasto', $gasto->id_gasto)
            ->select('distribucion_gasto.*')
            ->get();

        foreach ($distribuciones as $dist) {
            // Ejemplo de validación de pagos
            $sumOfPayments = DB::table('pagos')
                ->join('users', 'pagos.id_usuario', '=', 'users.id')
                ->where('pagos.id_gasto', $gasto->id_gasto)
                ->where('users.id_unidad', $dist->id_unidad)
                ->where(DB::raw('lower(pagos.estado)'), 'aceptado')
                ->sum('pagos.monto');

            if ($sumOfPayments < $dist->monto_asignado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aún faltan pagos pendientes por parte de las unidades.'
                ], 200);
            }
        }

        $gasto->estado_pago = 'Conciliado';
        $gasto->save();

        return response()->json([
            'success' => true,
            'message' => '¡Gasto validado correctamente!'
        ], 200);
    }

    // ========================================
    // = DISTRIBUIR CONSUMOS PENDIENTES (ALL) =
    // ========================================
    /**
     * Toma TODAS las mediciones status=pendiente, calcula precio y total,
     * y las agrega a detalle_gasto_comun. Ignoramos paginación.
     */
    public function distributeConsumption(Request $request, $id_gasto)
    {
        $gasto = GastosComune::findOrFail($id_gasto);
        if ($gasto->id_condominio !== Auth::user()->id_condominio) {
            abort(403, 'No autorizado.');
        }

        DB::beginTransaction();
        try {
            // Tomar TODAS las mediciones pendientes en este condominio
            // Eager loading para evitar N+1
            $pendientes = MedicionConsumo::with(['tipo_gasto_comun', 'unidad', 'extencion'])
                ->where('id_condominio', $gasto->id_condominio)
                ->whereRaw('LOWER(status) = ?', ['pendiente'])
                ->get();

            $montoSumado = 0;

            foreach ($pendientes as $pm) {
                $tipoGasto = $pm->tipo_gasto_comun;
                if (!$tipoGasto) {
                    continue;
                }

                // Buscamos config_consumos
                // Esto sigue siendo N+1 pero es más complejo de optimizar sin cambiar la estructura
                // Podríamos cargar todos los configs del condominio en memoria antes del loop
                $config = DB::table('config_consumos')
                    ->where('id_condominio', $pm->id_condominio)
                    ->where('id_tipo_gasto', $pm->id_tipo_gasto)
                    ->first();
                $precio = $config ? floatval($config->precio) : 0;

                $consumo = floatval($pm->consumo);
                $total = round($consumo * $precio, 2);

                $serviceName = $tipoGasto->nombre;
                $destino     = '';
                if ($pm->id_unidad) {
                    $unidad = $pm->unidad;
                    $destino = $unidad ? "Unidad {$unidad->nombre_unidad}" : "Unidad N/A";
                } elseif ($pm->id_extencion) {
                    $ext = $pm->extencion;
                    $destino = $ext ? "Extensión {$ext->nombre}" : 'Extensión N/A';
                } else {
                    $destino = 'Condominio (N/A)';
                }
                $desc = "Consumo de {$serviceName} ({$destino}): " .
                    "Lecturas [{$pm->lectura_anterior} - {$pm->lectura_actual}], " .
                    "consumo={$pm->consumo}, precio={$precio}";

                // Insertar en detalle_gasto_comun
                $nuevoDetalle = new DetalleGastoComun();
                $nuevoDetalle->id_gasto          = $gasto->id_gasto;
                $nuevoDetalle->id_tipo_gasto     = $pm->id_tipo_gasto;
                $nuevoDetalle->monto_detalle     = $total;
                $nuevoDetalle->descripcion_detalle = $desc;
                $nuevoDetalle->source = 'consumption';       // <-- ¡IMPORTANTE!

                // Determinar scope
                if ($pm->id_unidad) {
                    $nuevoDetalle->distribution_scope = 'unit';
                    $nuevoDetalle->target_unit        = $pm->id_unidad;
                } elseif ($pm->id_extencion) {
                    $nuevoDetalle->distribution_scope = 'extension';
                    $nuevoDetalle->target_extencion   = $pm->id_extencion;
                } else {
                    $nuevoDetalle->distribution_scope = 'condominium';
                }

                $nuevoDetalle->save();

                // Sumar a monto_total
                $montoSumado += $total;

                // Marcar medición => consumido
                $pm->status = 'consumido';
                $pm->save();
            }

            // actualizar gasto
            if ($montoSumado > 0) {
                $gasto->monto_total += $montoSumado;
                $gasto->save();
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Todos los consumos pendientes han sido agregados a este gasto y marcados como consumidos.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al distribuir consumos: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // ========== FINAL DISTRIB PREVIEW =======
    // ========================================
    /**
     * Genera estructura jerárquica “tower => occupant => unit => extension => services”.
     * Lo devolvemos en “hierarchical” para que el front lo pinte.
     */
    public function finalDistributionPreview($id_gasto)
    {
        $gasto = GastosComune::with([
            'detalle_gasto_comuns.tipo_gasto_comun',
            'detalle_gasto_comuns.targetUnidad.users',
            'detalle_gasto_comuns.targetUnidad.edificio',
            'detalle_gasto_comuns.targetExtencion',
            'detalle_gasto_comuns.targetTorre',
        ])->findOrFail($id_gasto);

        if ($gasto->id_condominio !== Auth::user()->id_condominio) {
            abort(403, 'No autorizado.');
        }

        if ($gasto->detalle_gasto_comuns->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay detalles para distribuir.'
            ]);
        }

        $detalles = $gasto->detalle_gasto_comuns;

        $torresGrouping = [];

        foreach ($detalles as $d) {
            $scope = $d->distribution_scope;
            $tipoGasto = $d->tipo_gasto_comun ? $d->tipo_gasto_comun->nombre : 'N/A';
            $monto     = $d->monto_detalle;

            // Determinar torre
            $torreObj = $d->targetTorre;
            if (!$torreObj && $d->targetUnidad && $d->targetUnidad->edificio) {
                $torreObj = $d->targetUnidad->edificio;
            }
            $torreName = $torreObj ? $torreObj->nombre : 'Sin Torre';

            // occupantName
            $occupantName = 'General';
            $unidadName   = null;
            $extName      = null;

            if ($scope === 'unit' && $d->targetUnidad) {
                $u = $d->targetUnidad->users->first();
                $occupantName = $u ? $u->name : 'Sin ocupante';
                $unidadName = $d->targetUnidad->nombre_unidad;
            } elseif ($scope === 'extension' && $d->targetExtencion) {
                $extName = $d->targetExtencion->nombre;

                // occupant de la extensión
                $extUsers = $d->targetExtencion->usuarios;
                if ($extUsers->count() > 0) {
                    $occupantName = $extUsers->first()->name;
                } else {
                    $occupantName = 'Sin ocupante (Extensión)';
                }
            }

            if (!isset($torresGrouping[$torreName])) {
                $torresGrouping[$torreName] = [];
            }
            if (!isset($torresGrouping[$torreName][$occupantName])) {
                $torresGrouping[$torreName][$occupantName] = [
                    'units'      => [],
                    'extensions' => [],
                ];
            }

            if ($unidadName) {
                if (!isset($torresGrouping[$torreName][$occupantName]['units'][$unidadName])) {
                    $torresGrouping[$torreName][$occupantName]['units'][$unidadName] = [];
                }
                $torresGrouping[$torreName][$occupantName]['units'][$unidadName][] = [
                    'service' => $tipoGasto,
                    'monto'   => $monto,
                ];
            } elseif ($extName) {
                if (!isset($torresGrouping[$torreName][$occupantName]['extensions'][$extName])) {
                    $torresGrouping[$torreName][$occupantName]['extensions'][$extName] = [];
                }
                $torresGrouping[$torreName][$occupantName]['extensions'][$extName][] = [
                    'service' => $tipoGasto,
                    'monto'   => $monto,
                ];
            } else {
                // condominio => occupant=“General”
                if (!isset($torresGrouping[$torreName][$occupantName]['units']['Condominio'])) {
                    $torresGrouping[$torreName][$occupantName]['units']['Condominio'] = [];
                }
                $torresGrouping[$torreName][$occupantName]['units']['Condominio'][] = [
                    'service' => $tipoGasto,
                    'monto'   => $monto,
                ];
            }
        }

        // Convertir a array final
        $hierarchical = [];
        foreach ($torresGrouping as $torreName => $occupants) {
            $occArray = [];
            foreach ($occupants as $occupantName => $data) {
                $uArr = [];
                if (!empty($data['units'])) {
                    foreach ($data['units'] as $uName => $services) {
                        $uArr[] = [
                            'unit_name' => $uName,
                            'services'  => $services,
                        ];
                    }
                }
                $eArr = [];
                if (!empty($data['extensions'])) {
                    foreach ($data['extensions'] as $eName => $services) {
                        $eArr[] = [
                            'extension_name' => $eName,
                            'services'       => $services,
                        ];
                    }
                }
                $occArray[] = [
                    'occupant_name' => $occupantName,
                    'units'         => $uArr,
                    'extensions'    => $eArr,
                ];
            }
            $hierarchical[] = [
                'tower_name' => $torreName,
                'occupants'  => $occArray,
            ];
        }

        return response()->json([
            'success'       => true,
            'hierarchical'  => $hierarchical,
        ]);
    }

    // ========================================
    // = CONFIRMAR DISTRIBUCIÓN FINAL (BOTÓN) =
    // ========================================
    public function confirmFinalDistribution($id_gasto)
    {
        $result = $this->calculateDistribution($id_gasto);
        $json   = $result->getData();

        if (!empty($json->success) && $json->success === true) {
            return response()->json([
                'success' => true,
                'message' => 'Distribución final confirmada.'
            ]);
        }
        return $result; // en caso de error
    }

    // ========================================
    // ========== PREVIEW CONSUMPTION =========
    // ========================================
    /**
     * Retorna la lista paginada (5 x pág) de mediciones en status=pendiente,
     * pero la distribución en “distributeConsumption()” tomará TODAS.
     */
    public function previewConsumption($id_gasto)
    {
        $gasto = GastosComune::findOrFail($id_gasto);
        if ($gasto->id_condominio !== Auth::user()->id_condominio) {
            abort(403, 'No autorizado.');
        }
        $monedaGasto = $gasto->tipo_moneda; // "Soles" o "Dolares"

        $query = MedicionConsumo::where('id_condominio', $gasto->id_condominio)
            ->whereRaw('LOWER(status) = ?', ['pendiente'])
            ->orderBy('id', 'desc');

        $pendientes = $query->paginate(5)->appends(['page' => request('page')]);

        if ($pendientes->total() === 0) {
            return response()->json([
                'success' => true,
                'data'    => [],
                'links'   => '',
                'message' => 'No hay consumos pendientes.'
            ]);
        }

        $mapped = [];

        foreach ($pendientes as $pm) {
            // Tipo de Gasto
            $tipoGasto = TipoGastoComun::find($pm->id_tipo_gasto);
            $serviceName = $tipoGasto ? $tipoGasto->nombre : 'Servicio';

            // Config de consumos
            $config = DB::table('config_consumos')
                ->where('id_condominio', $pm->id_condominio)
                ->where('id_tipo_gasto', $pm->id_tipo_gasto)
                ->first();

            // Seleccionar precio según la moneda del Gasto
            if ($monedaGasto === 'Dolares') {
                $precio = $config ? floatval($config->precio_dolares) : 0;
            } else {
                $precio = $config ? floatval($config->precio) : 0;
            }

            // Calcular consumo y total
            $consumo = floatval($pm->consumo);
            $total   = round($consumo * $precio, 2);

            // Determinar destino (unidad, extensión o condominio)
            if ($pm->id_unidad) {
                $unidad = Unidade::find($pm->id_unidad);
                $destino = $unidad ? "Unidad {$unidad->nombre_unidad}" : 'Unidad N/A';
            } elseif ($pm->id_extencion) {
                $ext = Extencion::find($pm->id_extencion);
                $destino = $ext ? "Extensión {$ext->nombre}" : 'Extensión N/A';
            } else {
                $destino = 'Condominio (N/A)';
            }

            // Formatear moneda
            $simbolo = ($monedaGasto === 'Dolares') ? '$' : 'S/';

            // Agregar al array de respuesta
            $mapped[] = [
                'id_medicion'      => $pm->id,
                'servicio'         => $serviceName,
                'service_label'    => "$serviceName ($destino)",
                'lectura_anterior' => $pm->lectura_anterior,
                'lectura_actual'   => $pm->lectura_actual,
                'consumo'          => $pm->consumo,
                'precio'           => $simbolo . $precio,
                'total'            => $simbolo . $total,
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $mapped,
            'links'   => $pendientes->links()->render(),
        ]);
    }


    // ========================================
    // ============ AUXILIARES ===============
    // ========================================
    private function obtenerUnidadesParticipantes($detalle, $gasto)
    {
        $query = Unidade::query();

        switch ($detalle->distribution_scope) {
            case 'tower':
                if ($detalle->target_tower) {
                    $query->where('id_edificio', $detalle->target_tower);
                }
                break;
            case 'condominium':
                $query->where('id_condominio', $gasto->id_condominio);
                break;
            case 'unit':
                if ($detalle->target_unit) {
                    $query->where('id_unidad', $detalle->target_unit);
                }
                break;
            case 'extension':
                return collect();
            default:
                $query->where('id_condominio', $gasto->id_condominio);
                break;
        }

        // Unidades con usuario activo
        $query->whereHas('users', function ($q) {
            $q->where('estado', 1);
        });
        return $query->get();
    }

    private function distribuirInternoTorre($id_detalle, Edificio $edificio, $montoTorre, $id_tipo_gasto, $prorrateoGlobal, $fechaVenc)
    {
        $unidades = Unidade::where('id_edificio', $edificio->id_edificio)
            ->whereHas('users', fn($q) => $q->where('estado', 1))
            ->get();

        if ($unidades->count() === 0) return;

        $prorrateoType = $edificio->aplica_prorrateo == 1
            ? $edificio->tipo_prorrateo_id
            : $prorrateoGlobal;

        $unidades = $this->calcularPorcentajeBase($unidades, $prorrateoType);
        $unidades = $this->agregarPorcentajeExtra($unidades, $id_tipo_gasto);
        $unidades = $this->ajustarPorcentajesFinales($unidades);

        $totalMonto = $montoTorre;
        $montoAcumulado = 0;
        $unidadesCount = $unidades->count();
        $i = 0;

        foreach ($unidades as $u) {
            $i++;
            if ($i == $unidadesCount) {
                // Last unit gets the remaining amount
                $montoAsig = round($totalMonto - $montoAcumulado, 2);
            } else {
                $montoAsig = round(($u->porcentaje_final / 100) * $totalMonto, 2);
            }
            
            $montoAcumulado += $montoAsig;

            DistribucionGasto::create([
                'id_detalle'       => $id_detalle,
                'id_unidad'        => $u->id_unidad,
                'monto_asignado'   => $montoAsig,
                'fecha_vencimiento' => $fechaVenc,
            ]);
        }
    }

    private function calcularPorcentajeBase($unidades, $prorrateoType)
    {
        if ($unidades->count() === 0) return $unidades;

        if ($prorrateoType == 1) {
            // por área
            $totalArea = $unidades->sum('area');
            foreach ($unidades as $u) {
                $u->porcentaje_base = $totalArea > 0
                    ? ($u->area / $totalArea) * 100
                    : 0;
            }
        } elseif ($prorrateoType == 2) {
            // equitativo
            $cant = $unidades->count();
            $base = $cant > 0 ? 100 / $cant : 0;
            foreach ($unidades as $u) {
                $u->porcentaje_base = $base;
            }
        } else {
            // default
            foreach ($unidades as $u) {
                $u->porcentaje_base = 0;
            }
        }
        return $unidades;
    }

    private function agregarPorcentajeExtra($unidades, $id_tipo_gasto)
    {
        $extras = UnidadServicioExtra::where('id_tipo_gasto', $id_tipo_gasto)
            ->whereIn('id_unidad', $unidades->pluck('id_unidad'))
            ->get()
            ->keyBy('id_unidad');

        foreach ($unidades as $u) {
            $extraRow = $extras->get($u->id_unidad);
            $u->porcentaje_extra = $extraRow ? floatval($extraRow->porcentaje_extra) : 0;
        }
        return $unidades;
    }

    private function ajustarPorcentajesFinales($unidades)
    {
        if ($unidades->count() === 0) return $unidades;

        $E_total = $unidades->sum('porcentaje_extra');
        $sinExtra = $unidades->filter(fn($x) => $x->porcentaje_extra == 0);
        $N_sin_extra = $sinExtra->count();

        if ($E_total <= 0 || $N_sin_extra <= 0) {
            foreach ($unidades as $u) {
                $u->porcentaje_final = $u->porcentaje_base;
            }
            return $unidades;
        }
        
        // CORRECCIÓN: Calcular la suma de porcentaje_base de unidades SIN extra
        $sumaSinExtra = $sinExtra->sum('porcentaje_base');
        
        foreach ($unidades as $u) {
            if ($u->porcentaje_extra > 0) {
                // Unidades con extra: se suma el % extra
                $u->porcentaje_final = $u->porcentaje_base + $u->porcentaje_extra;
            } else {
                // Unidades sin extra: descuento proporcional a su % base
                // Fórmula: peso = (% base individual / suma total % base sin extra)
                // Descuento individual = E_total * peso
                $peso = $sumaSinExtra > 0 ? ($u->porcentaje_base / $sumaSinExtra) : 0;
                $u->porcentaje_final = $u->porcentaje_base - ($E_total * $peso);
            }
        }
        return $unidades;
    }
}
