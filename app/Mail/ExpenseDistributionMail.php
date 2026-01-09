<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpenseDistributionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $gasto,
        public $condominio,
        public $administrador,
        public $mes,
        public $pdf,
        public $nombreCompleto,
        public $montoAsignado,
        public $configPago,
        public $detallesUsuario,
        public $extensiones,
        public $awsBaseUrl,
        public $simbolo
    ) {}

    public function build()
    {
        return $this->subject("Distribución de gastos - {$this->mes}")
            ->view('emails.expense_distribution')
            ->attachData($this->pdf, 'distribucion.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
