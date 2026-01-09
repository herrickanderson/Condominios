<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoProrrateoCondominio extends Model
{
    protected $table = 'tipo_prorrateo_condominios';

    protected $fillable = [
        'id_tipo_prorrateo',
        'id_condominio',
        'estado',
    ];

    public function condominio()
    {
        return $this->belongsTo(Condominio::class, 'id_condominio');
    }

    public function tipoProrrateo()
    {
        return $this->belongsTo(TipoProrrateo::class, 'id_tipo_prorrateo');
    }
}
