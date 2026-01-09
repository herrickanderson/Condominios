<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionPeriodo extends Model
{
    protected $table = 'configuracion_periodos';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'idcondominio',
        'nombre_periodo',      // <--- nuevo campo
        'dia_inicio',
        'dia_fin',
        'dia_vencimiento',
        'estado',
    ];

    public function condominio()
    {
        return $this->belongsTo(\App\Models\Condominio::class, 'idcondominio', 'id_condominio');
    }
}
