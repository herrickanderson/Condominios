<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProrrateoValor extends Model
{
    protected $table = 'prorrateo_valores';
    public $timestamps = true;

    protected $fillable = [
        'tipo_prorrateo_id',
        'id_condominio',
        'criterio',
        'valor_criterio',
    ];

    public function tipoProrrateo()
    {
        return $this->belongsTo(TipoProrrateo::class, 'tipo_prorrateo_id', 'id');
    }

    public function condominio()
    {
        return $this->belongsTo(Condominio::class, 'id_condominio');
    }
}
