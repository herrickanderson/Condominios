<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PermisosMudanza
 * 
 * @property int $id_permiso
 * @property int $id_unidad
 * @property int $id_usuario
 * @property Carbon $fecha_solicitada
 * @property Carbon|null $fecha_aprobacion
 * @property string|null $horario
 * @property bool|null $aprobado
 * @property string|null $observaciones
 * @property Carbon|null $created_at
 * 
 * @property Unidade $unidade
 * @property Usuario $usuario
 *
 * @package App\Models
 */
class PermisosMudanza extends Model
{
	protected $table = 'permisos_mudanza';
	protected $primaryKey = 'id_permiso';
	public $timestamps = false;

	protected $casts = [
		'id_unidad' => 'int',
		'id_usuario' => 'int',
		'fecha_solicitada' => 'datetime',
		'fecha_aprobacion' => 'datetime',
		'aprobado' => 'bool'
	];

	protected $fillable = [
		'id_unidad',
		'id_usuario',
		'fecha_solicitada',
		'fecha_aprobacion',
		'horario',
		'aprobado',
		'observaciones'
	];

	public function unidade()
	{
		return $this->belongsTo(Unidade::class, 'id_unidad');
	}

	public function usuario()
	{
		return $this->belongsTo(Usuario::class, 'id_usuario');
	}
}
