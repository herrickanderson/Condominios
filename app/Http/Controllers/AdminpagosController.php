<?php

namespace App\Http\Controllers;

use App\Models\DistribucionGasto;
use App\Models\Edificio;
use App\Models\GastosComune;
use App\Models\Pago;
use App\Models\Usuario;
use App\Models\Extencion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AdminpagosController extends Controller
{
    public function index(Request $request)
    {
        $userCondominio = Auth::user()->id_condominio;

        $periodos = GastosComune::where('id_condominio', $userCondominio)
            ->orderBy('fecha_periodo', 'desc')
            ->get();

        $selectedIdGasto = (int)$request->query('id_gasto', 0);
        $lastSixMonths = now()->subMonths(6);

        if ($selectedIdGasto > 0) {
            $distribuciones = DistribucionGasto::with('detalle_gasto_comun.tipo_gasto')
                ->whereHas('detalle_gasto_comun', function ($query) use ($selectedIdGasto) {
                    $query->where('id_gasto', $selectedIdGasto);
                })
                ->get();

            $gastosPorMes = GastosComune::selectRaw("to_char(fecha_periodo, 'YYYY-MM') as mes, SUM(monto_total) as total")
                ->where('id_condominio', $userCondominio)
                ->where('id_gasto', $selectedIdGasto)
                ->groupBy('mes')
                ->orderBy('mes', 'asc')
                ->get();

            $gastosPorServicio = DB::table('detalle_gasto_comun as dc')
                ->join('tipo_gasto_comun as tg', 'dc.id_tipo_gasto', '=', 'tg.id_tipo_gasto')
                ->where('dc.id_gasto', $selectedIdGasto)
                ->select('tg.nombre as servicio', DB::raw('SUM(dc.monto_detalle) as total'))
                ->groupBy('tg.nombre')
                ->orderBy('total', 'desc')
                ->get();

            $gastosPorTorre = DB::table('detalle_gasto_comun as dc')
                ->join('edificios as e', 'dc.target_tower', '=', 'e.id_edificio')
                ->where('dc.id_gasto', $selectedIdGasto)
                ->whereNotNull('dc.target_tower')
                ->select('e.nombre as torre', DB::raw('SUM(dc.monto_detalle) as total'))
                ->groupBy('e.nombre')
                ->orderBy('total', 'desc')
                ->get();

            $gastosPorGastoComun = null;
        } else {
            $distribuciones = DistribucionGasto::with('detalle_gasto_comun.tipo_gasto')
                ->whereHas('detalle_gasto_comun.gastos_comune', function ($query) use ($userCondominio, $lastSixMonths) {
                    $query->where('id_condominio', $userCondominio)
                        ->where('fecha_periodo', '>=', $lastSixMonths);
                })
                ->get();

            $gastosPorMes = GastosComune::selectRaw("to_char(fecha_periodo, 'YYYY-MM') as mes, SUM(monto_total) as total")
                ->where('id_condominio', $userCondominio)
                ->where('fecha_periodo', '>=', $lastSixMonths)
                ->groupBy('mes')
                ->orderBy('mes', 'asc')
                ->get();

            $gastosPorServicio = DB::table('detalle_gasto_comun as dc')
                ->join('tipo_gasto_comun as tg', 'dc.id_tipo_gasto', '=', 'tg.id_tipo_gasto')
                ->join('gastos_comunes as gc', 'dc.id_gasto', '=', 'gc.id_gasto')
                ->select('tg.nombre as servicio', DB::raw('SUM(dc.monto_detalle) as total'))
                ->where('gc.id_condominio', $userCondominio)
                ->where('gc.fecha_periodo', '>=', $lastSixMonths)
                ->groupBy('tg.nombre')
                ->orderBy('total', 'desc')
                ->get();

            $gastosPorTorre = DB::table('detalle_gasto_comun as dc')
                ->join('gastos_comunes as gc', 'dc.id_gasto', '=', 'gc.id_gasto')
                ->join('edificios as e', 'dc.target_tower', '=', 'e.id_edificio')
                ->where('gc.id_condominio', $userCondominio)
                ->whereNotNull('dc.target_tower')
                ->where('gc.fecha_periodo', '>=', $lastSixMonths)
                ->select('e.nombre as torre', DB::raw('SUM(dc.monto_detalle) as total'))
                ->groupBy('e.nombre')
                ->orderBy('total', 'desc')
                ->get();

            $gastosPorGastoComun = GastosComune::selectRaw("descripcion as gasto, SUM(monto_total) as total")
                ->where('id_condominio', $userCondominio)
                ->where('fecha_periodo', '>=', $lastSixMonths)
                ->groupBy('descripcion')
                ->orderBy('total', 'desc')
                ->get();
        }

        $distribucionesValidas = collect();

        foreach ($distribuciones as $dist) {
            $detalle = $dist->detalle_gasto_comun;
        
            if (!$detalle || !isset($detalle->id_gasto)) {
                continue;
            }
        
            // Determinar el usuario según el scope
            if (!$dist->id_unidad && !$dist->id_extencion) {
                switch ($detalle->distribution_scope) {
                    case 'condominium':
                        $dist->usuario = (object)[
                            'id' => 0,
                            'name' => 'Condominio',
                            'apellidos' => '',
                        ];
                        break;
                    case 'tower':
                        $torre = Edificio::find($detalle->target_tower);
                        $dist->usuario = (object)[
                            'id' => -$detalle->target_tower,
                            'name' => 'Torre',
                            'apellidos' => $torre ? $torre->nombre : 'Desconocida',
                        ];
                        break;
                    default:
                        continue 2;
                }
            } elseif ($dist->id_unidad) {
                $usuario = Usuario::where('id_unidad', $dist->id_unidad)->where('estado', 1)->first();
                if (!$usuario) continue;
                $dist->usuario = $usuario;
            } elseif ($dist->id_extencion) {
                $ext = Extencion::find($dist->id_extencion);
                if (!$ext) continue;
                $usuario = $ext->usuarios()->where('estado', 1)->first();
                if (!$usuario) continue;
                $dist->usuario = $usuario;
            }
        
            // Agrupación y moneda
            $dist->group_key = 'user_' . $dist->usuario->id;
            $dist->group_name = trim($dist->usuario->name . ' ' . $dist->usuario->apellidos);
            $gasto = GastosComune::find($detalle->id_gasto);
            $dist->moneda = $gasto ? $gasto->tipo_moneda : 'Soles';
        
            // 💡 Asignamos servicio y descripción con fallback
            $dist->servicio = $detalle->tipo_gasto->nombre ?? 'Sin tipo';
            $dist->descripcion = $detalle->descripcion_detalle ?? $dist->servicio;
        
            // Estado y pago
            $pago = Pago::where('id_gasto', $detalle->id_gasto)
                ->where('id_usuario', $dist->usuario->id)
                ->first();
        
            if ($pago) {
                $dist->estado       = $pago->estado;
                $dist->pago_id      = $pago->id_pago;
                $dist->monto_pagado = $pago->monto;
                $dist->fecha_pago   = $pago->fecha_pago;
                $dist->archivo      = $pago->archivo;
            } else {
                $dist->estado       = 'Pendiente';
                $dist->pago_id      = null;
                $dist->monto_pagado = null;
                $dist->fecha_pago   = null;
                $dist->archivo      = null;
            }
        
            $distribucionesValidas->push($dist);
        }
        

        $grupos = $distribucionesValidas->groupBy('group_key')->map(function ($group) {
            $first = $group->first();
            $totalAsignado = round($group->sum(fn($dist) => (float)$dist->monto_asignado), 2);
            $totalPagado   = round($group->sum(fn($dist) => (float)($dist->monto_pagado ?? 0)), 2);

            $estadoGrupo = 'Aceptado';
            foreach ($group as $dist) {
                $s = strtolower($dist->estado);
                if ($s === 'pendiente') {
                    $estadoGrupo = 'Pendiente';
                    break;
                } elseif ($s === 'enviado') {
                    $estadoGrupo = 'Enviado';
                }
            }

            return [
                'group_key'      => $first->group_key,
                'nombre'         => $first->group_name,
                'usuario'        => $first->usuario,
                'distribuciones' => $group->values(),
                'total_asignado' => $totalAsignado,
                'total_pagado'   => $totalPagado,
                'estado'         => $estadoGrupo,
                'moneda'         => $first->moneda,
            ];
        })->values();

        return Inertia::render('Dashboard', [
            'periodos'             => $periodos,
            'selectedIdGasto'      => $selectedIdGasto,
            'grupos'               => $grupos,
            'gastosPorMes'         => $gastosPorMes,
            'gastosPorServicio'    => $gastosPorServicio,
            'gastosPorTorre'       => $gastosPorTorre,
            'gastosPorGastoComun'  => $gastosPorGastoComun,
        ]);
    }


    public function validatePago(Request $request)
    {
        $request->validate(['id_pago' => 'required|exists:pagos,id_pago', 'accion' => 'required|in:aceptado,rechazado', 'observacion_admin' => 'nullable|string|max:500']);


        $pago = Pago::findOrFail($request->id_pago);
        $pago->estado = $request->accion === 'aceptado' ? 'Aceptado' : 'Rechazado';
        $pago->observacion_admin = $request->accion === 'rechazado' ? $request->observacion_admin : null;
        $pago->save();

        if ($pago->estado === 'Aceptado') {

            // Despachar el job para notificar el pago aceptado
            \App\Jobs\SendPaymentAcceptedMail::dispatch($pago->id_pago);

            // Actualización para extensiones:
            // Se obtienen las extensiones asignadas al propietario según extenciones_usuarios
            $userExtIds = \App\Models\ExtencionUsuario::where('user_id', $pago->id_usuario)
                ->pluck('id_extencion');

            if ($userExtIds->isNotEmpty()) {
                \Illuminate\Support\Facades\DB::table('mediciones_consumo')
                    ->whereIn('id_extencion', $userExtIds)
                    ->whereRaw("exists (
                        select 1 from detalle_gasto_comun dgc
                        join gastos_comunes gc on gc.id_gasto = dgc.id_gasto
                        join pagos p on p.id_gasto = gc.id_gasto
                        where dgc.source = 'consumption'
                          and p.id_pago = ?
                          and dgc.id_tipo_gasto = mediciones_consumo.id_tipo_gasto
                    )", [$pago->id_pago])
                    ->update(['status' => 'pagado']);
            }

            // Actualización para unidades:
            // Se obtiene el usuario propietario y su unidad asignada
            $usuario = \App\Models\Usuario::find($pago->id_usuario);
            if ($usuario && $usuario->id_unidad) {
                \Illuminate\Support\Facades\DB::table('mediciones_consumo')
                    ->where('id_unidad', $usuario->id_unidad)
                    ->whereRaw("exists (
                        select 1 from detalle_gasto_comun dgc
                        join gastos_comunes gc on gc.id_gasto = dgc.id_gasto
                        join pagos p on p.id_gasto = gc.id_gasto
                        where dgc.source = 'consumption'
                          and p.id_pago = ?
                          and dgc.id_tipo_gasto = mediciones_consumo.id_tipo_gasto
                    )", [$pago->id_pago])
                    ->update(['status' => 'pagado']);
            }
        }

        return response()->json([
            'message' => $pago->estado === 'Aceptado'
                ? 'Pago validado correctamente'
                : 'Pago rechazado con observación',
            'estado' => $pago->estado,
            'observacion_admin' => $pago->observacion_admin
        ]);
    }
}
