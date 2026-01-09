<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PreguntasEncuestum
 * 
 * @property int $id_pregunta
 * @property int $id_encuesta
 * @property string $texto
 * @property string $tipo_respuesta
 * @property bool|null $obligatoria
 * @property Carbon|null $created_at
 * 
 * @property Encuesta $encuesta
 * @property Collection|RespuestasEncuestum[] $respuestas_encuesta
 *
 * @package App\Models
 */
class PreguntasEncuestum extends Model
{
	protected $table = 'preguntas_encuesta';
	protected $primaryKey = 'id_pregunta';
	public $timestamps = false;

	protected $casts = [
		'id_encuesta' => 'int',
		'obligatoria' => 'bool'
	];

	protected $fillable = [
		'id_encuesta',
		'texto',
		'tipo_respuesta',
		'obligatoria'
	];

	public function encuesta()
	{
		return $this->belongsTo(Encuesta::class, 'id_encuesta');
	}

	public function respuestas_encuesta()
	{
		return $this->hasMany(RespuestasEncuestum::class, 'id_pregunta');
	}
}
