<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RespuestasEncuestum
 * 
 * @property int $id_respuesta
 * @property int $id_pregunta
 * @property int $id_usuario
 * @property string $respuesta_texto
 * @property Carbon|null $created_at
 * 
 * @property PreguntasEncuestum $preguntas_encuestum
 * @property Usuario $usuario
 *
 * @package App\Models
 */
class RespuestasEncuestum extends Model
{
	protected $table = 'respuestas_encuesta';
	protected $primaryKey = 'id_respuesta';
	public $timestamps = false;

	protected $casts = [
		'id_pregunta' => 'int',
		'id_usuario' => 'int'
	];

	protected $fillable = [
		'id_pregunta',
		'id_usuario',
		'respuesta_texto'
	];

	public function preguntas_encuestum()
	{
		return $this->belongsTo(PreguntasEncuestum::class, 'id_pregunta');
	}

	public function usuario()
	{
		return $this->belongsTo(Usuario::class, 'id_usuario');
	}
}
