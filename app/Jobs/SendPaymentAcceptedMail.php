<?php

namespace App\Jobs;

use App\Mail\PaymentAcceptedMail;
use App\Models\Pago;
use App\Models\Usuario;
use App\Models\GastosComune;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;

class SendPaymentAcceptedMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $idPago;

    public function __construct($idPago)
    {
        $this->idPago = $idPago;
    }

    public function handle()
    {
        // 1. Obtenemos el pago
        $pago = Pago::with(['usuario.unidad.edificio', 'gastos_comune.condominio'])->find($this->idPago);
        if (!$pago) {
            return;
        }

        // 2. Obtenemos datos principales
        $usuario   = $pago->usuario;  // Contiene name, apellidos, etc.
        $gasto     = $pago->gastos_comune; // Monto total, descripcion, etc.
        $condominio= $gasto ? $gasto->condominio : null;

        // Buscamos un administrador si necesitas
        $administrador = null;
        if ($condominio) {
            $administrador = Usuario::where('id_condominio', $condominio->id_condominio)
                ->whereHas('roles', function ($q) {
                    $q->where('roles.id_rol', 2);
                })->first();
        }

        // 3. Preparamos el PDF usando 'receipt.blade.php'
        //    En tu "receipt.blade.php", aceptas variables: $pago, $condominio, $gastoComun, $nombrePropietario, ...
        //    Ajusta según tus controladores
        $pdf = Pdf::loadView('pdf.receipt', [
            'pago'              => $pago,
            'condominio'        => $condominio,
            'gastoComun'        => $gasto,
            'nombrePropietario' => $usuario->name . ' ' . $usuario->apellidos,
            'nombreResidente'   => $usuario->name . ' ' . $usuario->apellidos,
            'descripcionGasto'  => $gasto->descripcion ?? 'Gasto',
            'montoTotal'        => $pago->monto,  // Monto pagado
        ]);

        // 4. Enviar el correo "PaymentAcceptedMail" con PDF adjunto
        Mail::to($usuario->email)
            ->send(new PaymentAcceptedMail(
                $pago,
                $condominio,
                $administrador,
                $pdf->output(),
                $usuario,
                $gasto,
                $pago->monto,
                $usuario->name . ' ' . $usuario->apellidos, // Propietario
                $usuario->name . ' ' . $usuario->apellidos  // Residente
            ));
    }
}
