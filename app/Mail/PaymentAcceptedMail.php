<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pago;
    public $condominio;
    public $administrador;
    public $pdfContent;
    public $usuario;
    public $gastoComun;   // El gasto
    public $montoTotal;   // Monto que se pagó
    public $nombrePropietario;
    public $nombreResidente;

    public function __construct($pago, $condominio, $administrador, $pdfContent, $usuario, $gastoComun, $montoTotal, $nombrePropietario, $nombreResidente)
    {
        $this->pago               = $pago;
        $this->condominio         = $condominio;
        $this->administrador      = $administrador;
        $this->pdfContent         = $pdfContent;
        $this->usuario            = $usuario;
        $this->gastoComun         = $gastoComun;
        $this->montoTotal         = $montoTotal;
        $this->nombrePropietario  = $nombrePropietario;
        $this->nombreResidente    = $nombreResidente;
    }

    public function build()
    {
        // Usamos una vista para el cuerpo del email. Lo llamamos 'emails.payment_accepted'
        return $this->view('emails.payment_accepted')
                    ->subject('¡Tu pago ha sido aceptado!')
                    ->attachData($this->pdfContent, 'comprobante.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
