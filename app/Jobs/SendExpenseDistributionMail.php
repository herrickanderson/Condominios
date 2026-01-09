<?php

namespace App\Jobs;

use App\Mail\ExpenseDistributionMail;
use App\Models\GastosComune;
use App\Models\Usuario;
use App\Models\ConfPago;
use App\Models\DistribucionGasto;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Barryvdh\DomPDF\Facade\Pdf;

class SendExpenseDistributionMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $idGasto;
    protected $awsBaseUrl;

    public function __construct($idGasto, $awsBaseUrl)
    {
        $this->idGasto = $idGasto;
        $this->awsBaseUrl = $awsBaseUrl;
    }

    public function handle()
    {
        $gasto = GastosComune::with('condominio')->find($this->idGasto);
        if (!$gasto) return;

        $configPago = ConfPago::where('id_condominio', $gasto->id_condominio)
            ->where('activo', 1)
            ->first();

        $simbolo = strtolower($gasto->tipo_moneda) === 'dólares' ? '$' : 'S/';

        $distribuciones = DistribucionGasto::with([
            'detalle.tipo_gasto_comun',
            'detalle.gastos_comune',
            'extencion.usuarios'
        ])
            ->whereHas('detalle', fn($q) => $q->where('id_gasto', $this->idGasto))
            ->get();

        $usuariosDistribuciones = [];

        foreach ($distribuciones as $dist) {
            if ($dist->id_unidad) {
                $usuario = Usuario::with('unidad.edificio', 'extenciones.edificio', 'extenciones.serviciosExtras')
                    ->where('id_unidad', $dist->id_unidad)
                    ->first();
                if (!$usuario) continue;

                $usuariosDistribuciones[$usuario->id]['usuario'] = $usuario;
                $usuariosDistribuciones[$usuario->id]['distribuciones'][] = $dist;
            } elseif ($dist->id_extencion && $dist->extencion && $dist->extencion->usuarios) {
                foreach ($dist->extencion->usuarios as $usuario) {
                    $usuario = Usuario::with('unidad.edificio', 'extenciones.edificio', 'extenciones.serviciosExtras')
                        ->find($usuario->id);
                    if (!$usuario) continue;

                    $usuariosDistribuciones[$usuario->id]['usuario'] = $usuario;
                    $usuariosDistribuciones[$usuario->id]['distribuciones'][] = $dist;
                }
            }
        }

        foreach ($usuariosDistribuciones as $data) {
            $usuario = $data['usuario'];
            $distribucionesUsuario = collect($data['distribuciones']);

            $montoAsignadoUsuario = $distribucionesUsuario->sum('monto_asignado');

            // Unidad: agrupar por tipo de gasto
            $detallesUnidad = $distribucionesUsuario
                ->whereNotNull('id_unidad')
                ->map(function ($dist) {
                    return [
                        'categoria' => optional($dist->detalle->tipo_gasto_comun)?->nombre ?? 'Sin categoría',
                        'tipo' => optional($dist->detalle->tipo_gasto_comun)?->nombre ?? 'Sin tipo',
                        'monto' => $dist->monto_asignado,
                    ];
                })
                ->groupBy('categoria');

            // Extensiones: agrupación por tipo -> id -> datos
            $extensionesPorTipo = [];
            foreach ($usuario->extenciones as $ext) {
                $distribs = $distribucionesUsuario->where('id_extencion', $ext->id_extencion);
                if ($distribs->count()) {
                    $detalles = $distribs->map(function ($d) {
                        return [
                            'tipo' => optional($d->detalle->tipo_gasto_comun)?->nombre ?? 'Sin tipo',
                            'monto' => $d->monto_asignado,
                        ];
                    });

                    $extensionesPorTipo[$ext->tipo_extencion][$ext->id_extencion] = [
                        'datos' => $ext,
                        'detalles' => $detalles
                    ];
                }
            }

            $administrador = Usuario::where('id_condominio', $gasto->id_condominio)
                ->whereHas('roles', fn($q) => $q->where('roles.id_rol', 2))
                ->first();

            $pdf = Pdf::loadView('pdf.receiptDetail', [
                'condominio' => $gasto->condominio,
                'gasto' => $gasto,
                'user' => $usuario,
                'montoAsignadoUsuario' => $montoAsignadoUsuario,
                'detallesUsuario' => $detallesUnidad,
                'extensiones' => $extensionesPorTipo,
                'totalGeneral' => $montoAsignadoUsuario,
                'simbolo' => $simbolo,
                'configPago' => $configPago,
                'awsBaseUrl' => $this->awsBaseUrl
            ]);

            Mail::to($usuario->email)->send(new ExpenseDistributionMail(
                gasto: $gasto,
                condominio: $gasto->condominio,
                administrador: $administrador,
                mes: date('F Y', strtotime($gasto->fecha_inicio)),
                pdf: $pdf->output(),
                nombreCompleto: $usuario->name . ' ' . $usuario->apellidos,
                montoAsignado: $montoAsignadoUsuario,
                configPago: $configPago,
                detallesUsuario: $detallesUnidad,
                extensiones: $extensionesPorTipo,
                awsBaseUrl: $this->awsBaseUrl,
                simbolo: $simbolo
            ));
        }
    }
}
