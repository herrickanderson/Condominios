<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistribucionGasto extends Model
{
    protected $table = 'distribucion_gasto';
    protected $primaryKey = 'id_distribucion';
    public $timestamps = false;

    protected $casts = [
        'id_detalle'        => 'int',
        'id_unidad'         => 'int',
        'monto_asignado'    => 'float',
        'fecha_vencimiento' => 'date',
        'id_extencion'      => 'int', // si lo has agregado
    ];

    protected $fillable = [
        'id_detalle',
        'id_unidad',
        'monto_asignado',
        'fecha_vencimiento',
        'id_extencion', // si lo agregaste en la tabla
    ];

    public function detalle_gasto_comun()
    {
        return $this->belongsTo(DetalleGastoComun::class, 'id_detalle');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidade::class, 'id_unidad');
    }

    public function extencion()
    {
        return $this->belongsTo(Extencion::class, 'id_extencion', 'id_extencion');
    }
    public function detalle()
    {
        return $this->belongsTo(DetalleGastoComun::class, 'id_detalle');
    }
}

