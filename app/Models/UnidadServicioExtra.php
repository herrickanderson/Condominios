<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadServicioExtra extends Model
{
    use HasFactory;

    protected $table = 'unidades_servicios_extras';

    protected $fillable = [
        'id_unidad',
        'id_tipo_gasto',
        'porcentaje_extra',
    ];

    // Relación con la unidad
    public function unidad()
    {
        return $this->belongsTo(Unidade::class, 'id_unidad');
    }

    // Relación con el tipo de gasto
    public function tipoGasto()
    {
        return $this->belongsTo(TipoGastoComun::class, 'id_tipo_gasto');
    }
}
