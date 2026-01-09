<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HistorialIncidencia
 * 
 * @property int $id_historial
 * @property int $id_incidencia
 * @property string $mensaje
 * @property Carbon|null $fecha
 * @property int $remitente
 * 
 * @property Incidencia $incidencia
 * @property Usuario $usuario
 *
 * @package App\Models
 */
class HistorialIncidencia extends Model
{
	protected $table = 'historial_incidencias';
	protected $primaryKey = 'id_historial';
	public $timestamps = false;

	protected $casts = [
		'id_incidencia' => 'int',
		'fecha' => 'datetime',
		'remitente' => 'int'
	];

	protected $fillable = [
		'id_incidencia',
		'mensaje',
		'fecha',
		'remitente'
	];

	public function incidencia()
	{
		return $this->belongsTo(Incidencia::class, 'id_incidencia');
	}

	public function usuario()
	{
		return $this->belongsTo(Usuario::class, 'remitente');
	}
}
