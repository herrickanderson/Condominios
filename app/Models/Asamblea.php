<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Asamblea
 * 
 * @property int $id_asamblea
 * @property int $id_condominio
 * @property Carbon $fecha
 * @property string $tipo_asamblea
 * @property string|null $documento_acta
 * @property string|null $descripcion
 * @property Carbon|null $created_at
 * 
 * @property Condominio $condominio
 *
 * @package App\Models
 */
class Asamblea extends Model
{
	protected $table = 'asambleas';
	protected $primaryKey = 'id_asamblea';
	public $timestamps = false;

	protected $casts = [
		'id_condominio' => 'int',
		'fecha' => 'datetime'
	];

	protected $fillable = [
		'id_condominio',
		'fecha',
		'tipo_asamblea',
		'documento_acta',
		'descripcion'
	];

	public function condominio()
	{
		return $this->belongsTo(Condominio::class, 'id_condominio');
	}
}
