<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtencionServicioExtra extends Model
{
    use HasFactory;

    protected $table = 'extenciones_servicios_extra';

    protected $fillable = [
        'id_extencion',
        'id_tipo_gasto',
        'porcentaje_extra'
    ];

    // Relación con Extencion
    public function extencion()
    {
        return $this->belongsTo(Extencion::class, 'id_extencion', 'id_extencion');
    }

    // Relación con el Tipo de Gasto (asumiendo que tienes un modelo TipoGastoComun)
    public function tipoGasto()
    {
        return $this->belongsTo(TipoGastoComun::class, 'id_tipo_gasto', 'id_tipo_gasto');
    }
}
