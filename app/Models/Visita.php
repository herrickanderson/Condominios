<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Visita
 * 
 * @property int $id_visita
 * @property int $id_unidad
 * @property int $id_usuario
 * @property string|null $lista_invitados
 * @property Carbon $fecha_visita
 * @property bool|null $validado
 * @property Carbon|null $created_at
 * 
 * @property Unidade $unidade
 * @property Usuario $usuario
 *
 * @package App\Models
 */
class Visita extends Model
{
	protected $table = 'visitas';
	protected $primaryKey = 'id_visita';
	public $timestamps = false;

	protected $casts = [
		'id_unidad' => 'int',
		'id_usuario' => 'int',
		'fecha_visita' => 'datetime',
		'validado' => 'bool'
	];

	protected $fillable = [
		'id_unidad',
		'id_usuario',
		'lista_invitados',
		'fecha_visita',
		'validado'
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
