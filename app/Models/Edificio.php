<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Edificio
 *
 * @property int $id_edificio
 * @property string $nombre
 * @property int $id_condominio
 *
 * @property Condominio $condominio
 * @property Collection|Unidade[] $unidades
 *
 * @package App\Models
 */
class Edificio extends Model
{
	protected $table = 'edificios';
	protected $primaryKey = 'id_edificio';
	public $timestamps = false;

	protected $casts = [
		'id_condominio' => 'int'
	];

    protected $fillable = [
        'nombre',
        'id_condominio',
        'aplica_prorrateo',
        'tipo_prorrateo_id'
    ];

	public function condominio()
	{
		//return $this->belongsTo(Condominio::class, 'id_condominio');
        return $this->belongsTo(Condominio::class, 'id_condominio', 'id_condominio');

	}

	public function unidades()
	{
		return $this->hasMany(Unidade::class, 'id_edificio');
	}
}
