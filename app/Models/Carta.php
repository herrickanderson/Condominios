<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Carta
 * 
 * @property int $id_carta
 * @property int $id_condominio
 * @property string $nombre_carta
 * @property Carbon $fecha_emision
 * @property string $contenido
 * @property string|null $adjuntos
 * @property Carbon|null $created_at
 * 
 * @property Condominio $condominio
 *
 * @package App\Models
 */
class Carta extends Model
{
	protected $table = 'cartas';
	protected $primaryKey = 'id_carta';
	public $timestamps = false;

	protected $casts = [
		'id_condominio' => 'int',
		'fecha_emision' => 'datetime',
		'adjuntos' => 'binary'
	];

	protected $fillable = [
		'id_condominio',
		'nombre_carta',
		'fecha_emision',
		'contenido',
		'adjuntos'
	];

	public function condominio()
	{
		return $this->belongsTo(Condominio::class, 'id_condominio');
	}
}
