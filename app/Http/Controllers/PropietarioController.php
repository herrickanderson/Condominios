<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use App\Models\DistribucionGasto;
use App\Models\Pago;
use Illuminate\Support\Facades\Auth;

class PropietarioController extends Controller
{
    /**
     * Vista principal del Propietario:
     *  - Mini-dashboard (gráfico)
     *  - Info del arrendatario
     *  - Pendientes (con tipo_gasto_comun)
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $propietario = Auth::user();
        $unidadId = $propietario->id_unidad;

        // Buscar arrendatario (rol 3) en la misma unidad
        $arrendatario = Usuario::where('id_unidad', $unidadId)
            ->where('id_condominio', $propietario->id_condominio)
            ->whereHas('roles', fn($q) => $q->where('roles.id_rol', 3))
            ->first();

        // Pendientes
        $pendientes = [];
        if ($arrendatario) {
            // Cargamos tipo_gasto_comun en la relación
            $pendientesCollection = DistribucionGasto::with([
                'detalle_gasto_comun.gastos_comune' => function($query) use ($propietario) {
                    $query->where('id_condominio', $propietario->id_condominio);
                },
                'detalle_gasto_comun.tipo_gasto_comun' // Para mostrar el nombre del tipo
            ])
            ->where('id_unidad', $arrendatario->id_unidad)
            ->get()
            ->filter(function ($dist) use ($arrendatario) {
                $idGasto = $dist->detalle_gasto_comun->gastos_comune->id_gasto;
                // Si ya existe un pago, no es pendiente
                $existePago = Pago::where('id_gasto', $idGasto)
                    ->where('id_usuario', $arrendatario->id)
                    ->exists();
                return !$existePago;
            });

            // Convertimos a array nativo
            $pendientes = $pendientesCollection->values()->all();
        }

        return Inertia::render('Propietario/Index', [
            'arrendatario' => $arrendatario,
            'pendientes'   => $pendientes,
        ]);
    }

    /**
     * Devuelve datos JSON para el gráfico (últimos 6 meses).
     * Ejemplo: suma de "monto_asignado" en distribucion_gasto, agrupado por mes.
     */
    public function arrendatarioChart(Request $request)
    {
          /** @var \App\Models\User $user */
          $propietario = Auth::user();
        $unidadId = $propietario->id_unidad;

        $arrendatario = Usuario::where('id_unidad', $unidadId)
            ->where('id_condominio', $propietario->id_condominio)
            ->whereHas('roles', fn($q) => $q->where('roles.id_rol', 3))
            ->first();

        if (!$arrendatario) {
            return response()->json(['chartData' => []]);
        }

        $endDate = now();
        $startDate = now()->subMonths(6);

        // Obtenemos la suma de "monto_asignado" por mes
        $rows = DB::table('distribucion_gasto as dist')
            ->join('detalle_gasto_comun as dg', 'dist.id_detalle', '=', 'dg.id_detalle')
            ->join('gastos_comunes as gc', 'dg.id_gasto', '=', 'gc.id_gasto')
            ->where('dist.id_unidad', $arrendatario->id_unidad)
            ->where('gc.id_condominio', $propietario->id_condominio)
            ->whereBetween('gc.fecha_periodo', [$startDate, $endDate])
            ->select(
                DB::raw("to_char(gc.fecha_periodo, 'YYYY-MM') as periodo"),
                DB::raw("SUM(dist.monto_asignado) as total")
            )
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        $chartData = $rows->map(function($row) {
            return [
                'label' => $row->periodo,   // "2025-03"
                'value' => (float) $row->total,
            ];
        })->values()->all();

        return response()->json(['chartData' => $chartData]);
    }

    /**
     * (Opcional) Vista de pagos realizados del arrendatario.
     */
    public function pagosRealizados()
    {
         /** @var \App\Models\User $user */
        $propietario = Auth::user();

          
    
        $unidadId = $propietario->id_unidad;

        $arrendatario = Usuario::where('id_unidad', $unidadId)
            ->where('id_condominio', $propietario->id_condominio)
            ->whereHas('roles', fn($q) => $q->where('roles.id_rol', 3))
            ->first();

        $pagos = [];
        if ($arrendatario) {
            $pagosCollection = Pago::with('gastos_comune')
                ->where('id_usuario', $arrendatario->id)
                ->orderBy('id_pago','desc')
                ->get();

            $pagos = $pagosCollection->values()->all();
        }

        return Inertia::render('Propietario/PagosRealizados', [
            'arrendatario' => $arrendatario,
            'pagos'        => $pagos,
        ]);
    }
}
