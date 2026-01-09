<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Encuesta
 * 
 * @property int $id_encuesta
 * @property string $nombre
 * @property string|null $descripcion
 * @property string $tipo
 * @property Carbon $fecha_inicio
 * @property Carbon $fecha_fin
 * @property Carbon|null $created_at
 * 
 * @property Collection|PreguntasEncuestum[] $preguntas_encuesta
 *
 * @package App\Models
 */
class Encuesta extends Model
{
	protected $table = 'encuestas';
	protected $primaryKey = 'id_encuesta';
	public $timestamps = false;

	protected $casts = [
		'fecha_inicio' => 'datetime',
		'fecha_fin' => 'datetime'
	];

	protected $fillable = [
		'nombre',
		'descripcion',
		'tipo',
		'fecha_inicio',
		'fecha_fin', 'id_condominio'
	];
    public function condominio()
    {
        return $this->belongsTo(Condominio::class, 'id_condominio');
    }
	public function preguntas_encuesta()
	{
		return $this->hasMany(PreguntasEncuestum::class, 'id_encuesta');
	}
}
