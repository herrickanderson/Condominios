<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Incidencia
 * 
 * @property int $id_incidencia
 * @property int $id_usuario_origen
 * @property int $id_usuario_destino
 * @property string $tipo_mensaje
 * @property string $descripcion
 * @property string|null $estado
 * @property Carbon|null $fecha_registro
 * 
 * @property Usuario $usuario
 * @property Collection|HistorialIncidencia[] $historial_incidencias
 *
 * @package App\Models
 */
class Incidencia extends Model
{
	protected $table = 'incidencias';
	protected $primaryKey = 'id_incidencia';
	public $timestamps = false;

	protected $casts = [
		'id_usuario_origen' => 'int',
		'id_usuario_destino' => 'int',
		'fecha_registro' => 'datetime'
	];

	protected $fillable = [
		'id_usuario_origen',
		'id_usuario_destino',
		'tipo_mensaje',
		'descripcion',
		'estado',
		'fecha_registro'
	];

	public function usuario()
	{
		return $this->belongsTo(Usuario::class, 'id_usuario_destino');
	}

	public function historial_incidencias()
	{
		return $this->hasMany(HistorialIncidencia::class, 'id_incidencia');
	}
}
