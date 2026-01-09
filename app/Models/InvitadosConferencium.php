<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class InvitadosConferencium
 * 
 * @property int $id
 * @property int $id_conferencia
 * @property int $id_usuario
 * @property string|null $estado_invitacion
 * @property Carbon|null $created_at
 * 
 * @property Conferencia $conferencia
 * @property Usuario $usuario
 *
 * @package App\Models
 */
class InvitadosConferencium extends Model
{
	protected $table = 'invitados_conferencia';
	public $timestamps = false;

	protected $casts = [
		'id_conferencia' => 'int',
		'id_usuario' => 'int'
	];

	protected $fillable = [
		'id_conferencia',
		'id_usuario',
		'estado_invitacion'
	];

	public function conferencia()
	{
		return $this->belongsTo(Conferencia::class, 'id_conferencia');
	}

	public function usuario()
	{
		return $this->belongsTo(Usuario::class, 'id_usuario');
	}
}
