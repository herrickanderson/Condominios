<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfPago extends Model
{
    protected $table = 'conf_pagos';
    protected $fillable = [
        'id_condominio',
        'banco',
        'tipo_cuenta',
        'numero_cuenta',
        'cci',
        'propietario',
        'telefono',
        'direccion',
        'observaciones',
        'qr_path',
        'activo',
    ];

    public function condominio()
    {
        return $this->belongsTo(Condominio::class, 'id_condominio');
    }
}
