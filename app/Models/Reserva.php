<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Reserva
 * 
 * @property int $id_reserva
 * @property int $id_instalacion
 * @property int $id_usuario
 * @property Carbon $fecha_hora_reserva
 * @property interval $duracion
 * @property string|null $estado
 * @property Carbon|null $created_at
 * 
 * @property Instalacione $instalacione
 * @property Usuario $usuario
 *
 * @package App\Models
 */
class Reserva extends Model
{
	protected $table = 'reservas';
	protected $primaryKey = 'id_reserva';
	public $timestamps = false;

	protected $casts = [
		'id_instalacion' => 'int',
		'id_usuario' => 'int',
		'fecha_hora_reserva' => 'datetime',
		'duracion' => 'interval'
	];

	protected $fillable = [
		'id_instalacion',
		'id_usuario',
		'fecha_hora_reserva',
		'duracion',
		'estado'
	];

	public function instalacione()
	{
		return $this->belongsTo(Instalacione::class, 'id_instalacion');
	}

	public function usuario()
	{
		return $this->belongsTo(Usuario::class, 'id_usuario');
	}
}
