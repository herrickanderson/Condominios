<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TipoProrrateo
 *
  * @property int $id
 * @property string $descripcion
 *
 * @package App\Models
 */
class TipoProrrateo extends Model
{
	protected $table = 'tipo_prorrateo';
	protected $primaryKey = 'id';
	public $timestamps = false;

	protected $fillable = [
		'descripcion', 'id_condominio'
	];
    public function condominio()
    {
        return $this->belongsTo(Condominio::class, 'id_condominio');
    }
    public function prorrateoValores()
{
    return $this->hasMany(ProrrateoValor::class, 'tipo_prorrateo_id');
}
}
