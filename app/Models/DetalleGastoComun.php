<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleGastoComun extends Model
{
    protected $table = 'detalle_gasto_comun';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false;

    protected $casts = [
        'id_gasto'           => 'int',
        'id_tipo_gasto'      => 'int',
        'id_edificio'        => 'int',
        'monto_detalle'      => 'float',
        'distribution_scope' => 'string',
        'target_tower'       => 'int',
        'target_unit'        => 'int',
        'target_extencion'   => 'int', // <--- nuevo, si usas “int”
        'source'             => 'string',
    ];

    protected $fillable = [
        'id_gasto',
        'id_tipo_gasto',
        'id_edificio',
        'monto_detalle',
        'nombre_file',
        'file_url',
        'observacion',
        'descripcion_detalle',
        'distribution_scope',
        'target_tower',
        'target_unit',
        'target_extencion', // <--- nuevo
        'source', // <--- lo agregas aquí
    ];

    // Gasto
    public function gastos_comune()
    {
        return $this->belongsTo(GastosComune::class, 'id_gasto', 'id_gasto');
    }

    // Tipo de Gasto
    public function tipo_gasto_comun()
    {
        return $this->belongsTo(TipoGastoComun::class, 'id_tipo_gasto', 'id_tipo_gasto');
    }

    // Distribuciones (si se usan)
    public function distribucion_gastos()
    {
        return $this->hasMany(DistribucionGasto::class, 'id_detalle', 'id_detalle');
    }

    // Edificio asociado (opcional)
    public function edificio()
    {
        return $this->belongsTo(Edificio::class, 'id_edificio');
    }

    // Torre destino
    public function targetTorre()
    {
        return $this->belongsTo(Edificio::class, 'target_tower');
    }

    // Unidad destino
    public function targetUnidad()
    {
        return $this->belongsTo(Unidade::class, 'target_unit');
    }

    // Extensión destino
    public function targetExtencion()
    {
        return $this->belongsTo(Extencion::class, 'target_extencion', 'id_extencion');
    }
    public function tipo_gasto()
    {
        return $this->belongsTo(TipoGastoComun::class, 'id_tipo_gasto');
    }
}
