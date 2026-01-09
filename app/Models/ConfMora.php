<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfMora extends Model
{
    protected $table = 'conf_mora';

    protected $fillable = [
        'id_condominio',
        'tipo_periodo',
        'porcentaje',
    ];

    public function condominio()
    {
        return $this->belongsTo(Condominio::class, 'id_condominio', 'id_condominio');
    }
}
