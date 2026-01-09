<?php

namespace App\Http\Controllers;

use App\Models\DistribucionGasto;
use App\Models\Extencion;
use App\Models\GastosComune;
use App\Models\Pago;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class pagosController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Obtenemos los id de extensiones asignadas al usuario (tabla pivote extenciones_usuarios)
        $extIds = DB::table('extenciones_usuarios')
            ->where('user_id', $user->id)
            ->pluck('id_extencion')
            ->toArray();

        // Cargamos los pagos del usuario autenticado con sus relaciones:
        $pagos = Pago::with([
            'gastos_comune' => function ($q) use ($user, $extIds) {
                $q->where('id_condominio', $user->id_condominio)
                    ->with([
                        'detalle_gasto_comuns' => function ($detalleQuery) use ($user, $extIds) {
                            $detalleQuery->with([
                                'distribucion_gastos' => function ($distQuery) use ($user, $extIds) {
                                    $distQuery->where(function ($query) use ($user, $extIds) {
                                        $query->where('id_unidad', $user->id_unidad);
                                        if (!empty($extIds)) {
                                            $query->orWhereIn('id_extencion', $extIds);
                                        }
                                    });
                                },
                                'tipo_gasto_comun'
                            ]);
                        }
                    ]);
            }
        ])
            ->where('id_usuario', $user->id)
            ->orderBy('id_pago', 'desc')
            ->get();

        // Agregamos la lógica para buscar archivos de consumo desde mediciones_consumo
        foreach ($pagos as $pago) {
            foreach ($pago->gastos_comune->detalle_gasto_comuns ?? [] as $detalle) {
                foreach ($detalle->distribucion_gastos ?? [] as $dist) {
                    if ($detalle->source === 'consumption') {
                        // Buscar archivo en mediciones_consumo
                        $mcQuery = DB::table('mediciones_consumo')
                            ->where('id_condominio', $user->id_condominio)
                            ->where('id_tipo_gasto', $detalle->id_tipo_gasto);

                        if ($detalle->target_unit) {
                            $mcQuery->where('id_unidad', $detalle->target_unit);
                        }
                        if ($detalle->target_extencion) {
                            $mcQuery->where('id_extencion', $detalle->target_extencion);
                        }

                        $mc = $mcQuery->first();
                        $dist->archivo = $mc && $mc->archivo ? $mc->archivo : null;
                    } elseif ($detalle->source === 'manual') {
                        // Usar archivo adjunto directamente desde detalle_gasto_comun
                        $dist->archivo = $detalle->file_url ?? null;
                    } else {
                        $dist->archivo = null;
                    }
                }
            }
        }

        return Inertia::render('Pagos/Index', [
            'pagos' => $pagos,
        ]);
    }


    /**
     * Muestra las distribuciones pendientes, sin un pago "Aceptado" para este usuario.
     * Se añade lógica para buscar el archivo en mediciones_consumo si source='consumption'
     * y se marca si es consumo de extención.
     */
    public function pendientes()
    {
        $user = Auth::user();

        // Obtenemos los id de extensiones asignadas al usuario (tabla pivote extenciones_usuarios)
        $extIds = DB::table('extenciones_usuarios')
            ->where('user_id', $user->id)
            ->pluck('id_extencion')
            ->toArray();


        $pendientesRaw = DistribucionGasto::with([
            'detalle_gasto_comun.gastos_comune',   // para acceder a tipo_moneda, etc.
            'detalle_gasto_comun.tipo_gasto_comun',
            'extencion' => function ($q) {

                $q->select('id_extencion', 'nombre', 'tipo_extencion', 'cobro_unico');
            },
        ])
            ->where(function ($query) use ($user, $extIds) {
                $query->where('id_unidad', $user->id_unidad);
                if (!empty($extIds)) {
                    $query->orWhereIn('id_extencion', $extIds);
                }
            })
            ->get();

        // 3) Excluir las distribuciones cuyo gasto ya tenga un pago "Aceptado" de este usuario
        $pendientes = $pendientesRaw->filter(function ($dist) use ($user) {
            $detalle = $dist->detalle_gasto_comun;
            if (!$detalle) return false;

            $gasto = $detalle->gastos_comune;
            if (!$gasto) return false;

            $existePagoAceptado = Pago::where('id_gasto', $gasto->id_gasto)
                ->where('id_usuario', $user->id)
                ->where('estado', 'Aceptado')
                ->exists();

            return !$existePagoAceptado;
        });


        foreach ($pendientes as $dist) {
            $detalle = $dist->detalle_gasto_comun;
            if (!$detalle) {
                $dist->archivo_consumo = null;
                $dist->is_extencion = false;
                continue;
            }

            if ($detalle->source === 'consumption') {
                // Buscar la medición consumo donde se haya adjuntado un archivo
                $mcQuery = DB::table('mediciones_consumo')
                    ->where('id_condominio', $user->id_condominio)
                    ->where('id_tipo_gasto', $detalle->id_tipo_gasto);

                if ($detalle->target_unit) {
                    $mcQuery->where('id_unidad', $detalle->target_unit);
                }
                if ($detalle->target_extencion) {
                    $mcQuery->where('id_extencion', $detalle->target_extencion);
                }

                $mc = $mcQuery->first();
                // Asumimos que en mediciones_consumo hay una columna 'archivo' con la ruta
                $dist->archivo_consumo = $mc && $mc->archivo ? $mc->archivo : null;
                // Si existe target_extencion, se marca como consumo de extención
                $dist->is_extencion = $detalle->target_extencion ? true : false;
            } else {
                $dist->archivo_consumo = null;
                $dist->is_extencion = false;
            }
        }

        // 5) Pagos del usuario para saber si está "Enviado", "Rechazado", etc.
        $userPayments = Pago::where('id_usuario', $user->id)->get();

        return Inertia::render('Pagos/Pendientes', [
            'pendientes'   => $pendientes,
            'userPayments' => $userPayments,
        ]);
    }

    /**
     * Guarda un nuevo pago con estado "Enviado".
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'id_gasto'   => 'required|exists:gastos_comunes,id_gasto',
            'monto'      => 'required|numeric|min:0',
            'fecha_pago' => 'required|date',
            'archivo'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'observacion' => 'nullable|string',
        ]);

        // Verificamos que el gasto pertenezca al condominio del usuario
        $gasto = GastosComune::where('id_gasto', $validated['id_gasto'])
            ->where('id_condominio', $user->id_condominio)
            ->firstOrFail();

        $rutaArchivo = null;
        $nombreArchivo = null;

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $folderPath = 'produccion/Pagos/condominio_' . $user->id_condominio;
            $filename   = 'user_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            $rutaArchivo = $file->storeAs($folderPath, $filename, 's3');
            $nombreArchivo = $file->getClientOriginalName();
        }

        // Creamos el pago con estado "Enviado"
        Pago::create([
            'id_gasto'       => $gasto->id_gasto,
            'id_usuario'     => $user->id,
            'monto'          => $validated['monto'],
            'fecha_pago'     => $validated['fecha_pago'],
            'metodo_pago'    => 'Transferencia',
            'referencia'     => null,
            'archivo'        => $rutaArchivo,
            'nombre_archivo' => $nombreArchivo,
            'estado'         => 'Enviado',
            'observacion'    => $validated['observacion'] ?? '',
        ]);

        return redirect()->route('pagos.pendientes')
            ->with('success', 'Pago enviado con éxito. Espera la validación del administrador.');
    }

    /**
     * Actualiza un pago existente => vuelve a estado "Enviado".
     */
    public function update(Request $request, $idPago)
    {
        $user = Auth::user();
        $pago = Pago::findOrFail($idPago);
        if ($pago->id_usuario !== $user->id) {
            abort(403, 'No autorizado para actualizar este pago.');
        }

        $validated = $request->validate([
            'monto'      => 'required|numeric|min:0',
            'fecha_pago' => 'required|date',
            'archivo'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'observacion' => 'nullable|string',
        ]);

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $folderPath = 'produccion/Pagos/condominio_' . $user->id_condominio;
            $filename   = 'user_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $rutaArchivo = $file->storeAs($folderPath, $filename, 's3');

            $pago->archivo        = $rutaArchivo;
            $pago->nombre_archivo = $file->getClientOriginalName();
        }

        $pago->monto       = $validated['monto'];
        $pago->fecha_pago  = $validated['fecha_pago'];
        $pago->observacion = $validated['observacion'] ?? '';
        $pago->estado      = 'Enviado';
        $pago->save();

        return redirect()->route('pagos.pendientes')
            ->with('success', 'Pago corregido y enviado nuevamente. Espera la validación del administrador.');
    }

    /**
     * Generar PDF (solo si el pago está Aceptado).
     */
    public function downloadReceipt($id)
    {
        $user = Auth::user();
        $pago = Pago::with([
            'usuario.unidad.edificio',
            'gastos_comune.condominio'
        ])
            ->findOrFail($id);

        if ($pago->id_usuario !== $user->id || $pago->estado !== 'Aceptado') {
            abort(403, 'No autorizado para generar este comprobante.');
        }

        $gastoComun = $pago->gastos_comune;
        $condominio = $gastoComun->condominio;

        $unidadId = $pago->usuario->id_unidad;

        $propietario = Usuario::where('id_unidad', $unidadId)
            ->whereHas('roles', function ($q) {
                $q->where('roles.id_rol', 4);
            })
            ->first();

        $arrendatario = Usuario::where('id_unidad', $unidadId)
            ->whereHas('roles', function ($q) {
                $q->where('roles.id_rol', 3);
            })
            ->first();

        $nombrePropietario = $propietario
            ? ($propietario->name . ' ' . $propietario->apellidos)
            : 'No registrado';

        $nombreResidente = $arrendatario
            ? ($arrendatario->name . ' ' . $arrendatario->apellidos)
            : $nombrePropietario;

        $montoTotal = $pago->monto;
        $descripcionGasto = $gastoComun->descripcion;

        $pdf = PDF::loadView('pdf.receipt', [
            'pago'              => $pago,
            'condominio'        => $condominio,
            'gastoComun'        => $gastoComun,
            'nombrePropietario' => $nombrePropietario,
            'nombreResidente'   => $nombreResidente,
            'descripcionGasto'  => $descripcionGasto,
            'montoTotal'        => $montoTotal,
        ]);

        return $pdf->download('comprobante_pago_' . $pago->id_pago . '.pdf');
    }

    /**
     * Datos para el gráfico de resumen (dashboard).
     */
    public function dashboardGastos(Request $request)
    {
        $user = Auth::user();
        $unidadId = $user->id_unidad;
        $filter = $request->get('filter');

        if ($filter && $filter !== '6meses') {
            $gastos = GastosComune::where('id_condominio', $user->id_condominio)
                ->where('descripcion', $filter)
                ->get();
        } else {
            $sixMonthsAgo = now()->subMonths(6);
            $gastos = GastosComune::where('id_condominio', $user->id_condominio)
                ->where('fecha_periodo', '>=', $sixMonthsAgo)
                ->get();
        }

        $gastosPorMes = $gastos->map(function ($gasto) use ($unidadId) {
            $sum = DistribucionGasto::whereHas('detalle_gasto_comun', function ($query) use ($gasto) {
                $query->where('id_gasto', $gasto->id_gasto);
            })
                ->where('id_unidad', $unidadId)
                ->sum('monto_asignado');

            return [
                'mes'   => $gasto->descripcion,
                'total' => (float) $sum,
            ];
        });

        return response()->json(['gastosPorMes' => $gastosPorMes]);
    }

    /**
     * Datos históricos (histórico de servicios) para el dashboard.
     */
    public function historicalGastos(Request $request)
    {
        $user = Auth::user();
        $unidadId = $user->id_unidad;
        $filter = $request->get('gasto');

        $endDate = now();
        $startDate = now()->subMonths(6);

        $data = DB::table('gastos_comunes as gc')
            ->join('detalle_gasto_comun as dg', 'gc.id_gasto', '=', 'dg.id_gasto')
            ->join('distribucion_gasto as dist', 'dg.id_detalle', '=', 'dist.id_detalle')
            ->join('tipo_gasto_comun as tg', 'dg.id_tipo_gasto', '=', 'tg.id_tipo_gasto')
            ->where('gc.id_condominio', $user->id_condominio)
            ->where('dist.id_unidad', $unidadId)
            ->whereBetween('gc.fecha_periodo', [$startDate, $endDate])
            ->when($filter && $filter !== '6meses', function ($query) use ($filter) {
                $query->where('gc.descripcion', $filter);
            })
            ->select(
                DB::raw("to_char(gc.fecha_periodo, 'YYYY-MM') as periodo"),
                'tg.nombre as servicio',
                DB::raw("SUM(dist.monto_asignado) as total")
            )
            ->groupBy('periodo', 'tg.nombre')
            ->orderBy('periodo')
            ->get();

        return response()->json(['historicalData' => $data]);
    }
}
