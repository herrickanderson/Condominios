<?php

namespace App\Listeners;

use App\Models\RegistroIngreso;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSuccessfulLogin
{
   /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        // Registra el ingreso en la base de datos

        RegistroIngreso::create([
            'user_id'            => $event->user->id,
            'fecha_hora_ingreso' => now(), // Usa la fecha y hora actual
            'ip'                 => request()->ip(),
            'navegador'          => request()->header('User-Agent'),
        ]);
    }
}
