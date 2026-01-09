<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoGastoComun extends Model
{
    protected $table = 'tipo_gasto_comun';
    protected $primaryKey = 'id_tipo_gasto';
    public $timestamps = false;

    protected $casts = [
        'aplica_a_todos_edificios' => 'boolean',
        'consumo' => 'boolean',
    ];

    protected $fillable = [
        'nombre',
        'aplica_a_todos_edificios',
        'id_condominio',
        'id_categoria',
        'aplica_prorrateo_condominio', // nuevo campo
        'tipo_prorrateo_id',           // nuevo campo
        'consumo',                     // nuevo campo
        'medida',                      // nuevo campo
    ];

    public function condominio()
    {
        return $this->belongsTo(Condominio::class, 'id_condominio');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaGastoComun::class, 'id_categoria');
    }

    public function detalle_gasto_comuns()
    {
        return $this->hasMany(DetalleGastoComun::class, 'id_tipo_gasto');
    }

    public function unidadesServicioExtras()
    {
        return $this->hasMany(UnidadServicioExtra::class, 'id_tipo_gasto');
    }
}
