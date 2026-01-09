<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Instalacione
 * 
 * @property int $id_instalacion
 * @property int $id_condominio
 * @property string $nombre
 * @property string $tipo_pago
 * @property string|null $configuracion
 * @property Carbon|null $created_at
 * 
 * @property Condominio $condominio
 * @property Collection|Reserva[] $reservas
 *
 * @package App\Models
 */
class Instalacione extends Model
{
	protected $table = 'instalaciones';
	protected $primaryKey = 'id_instalacion';
	public $timestamps = false;

	protected $casts = [
		'id_condominio' => 'int',
		'configuracion' => 'binary'
	];

	protected $fillable = [
		'id_condominio',
		'nombre',
		'tipo_pago',
		'configuracion'
	];

	public function condominio()
	{
		return $this->belongsTo(Condominio::class, 'id_condominio');
	}

	public function reservas()
	{
		return $this->hasMany(Reserva::class, 'id_instalacion');
	}
}
