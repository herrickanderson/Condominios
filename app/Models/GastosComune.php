<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class GastosComune extends Model
{
    protected $table = 'gastos_comunes';
    protected $primaryKey = 'id_gasto';

    protected $casts = [
        'id_condominio'      => 'int',
        'monto_total'        => 'float',
        'fecha_periodo'      => 'datetime',
        'fecha_inicio'       => 'datetime',
        'fecha_fin'          => 'datetime',
        'fecha_vencimiento'  => 'date',
        'id_tipo_prorrateo'   => 'int'
    ];

    protected $fillable = [
        'id_condominio',
        'descripcion',
        'monto_total',
        'tipo_moneda',
        'fecha_periodo',
        'fecha_inicio',
        'fecha_fin',
        'fecha_vencimiento',
        'estado_pago',
        'tipo_cobro',
        'id_tipo_prorrateo'
    ];

    public function condominio()
    {
        return $this->belongsTo(Condominio::class, 'id_condominio');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_gasto');
    }

    public function detalle_gasto_comuns()
    {
       // return $this->hasMany(DetalleGastoComun::class, 'id_gasto');
       return $this->hasMany(DetalleGastoComun::class, 'id_gasto', 'id_gasto');

    }
    public function distribuciones()
    {
        return $this->hasManyThrough(
            \App\Models\DistribucionGasto::class,
            \App\Models\DetalleGastoComun::class,
            'id_gasto',       // llave foránea en DetalleGastoComun
            'id_detalle',     // llave foránea en DistribucionGasto
            'id_gasto',       // llave local en GastosComune
            'id_detalle'      // llave local en DetalleGastoComun
        );
    }
}
