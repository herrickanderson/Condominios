<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Conferencia
 * 
 * @property int $id_conferencia
 * @property string $asunto
 * @property Carbon $fecha_inicio
 * @property Carbon|null $fecha_fin
 * @property int $id_moderador
 * @property string|null $tipo
 * @property string|null $grabacion
 * @property Carbon|null $created_at
 * 
 * @property Usuario $usuario
 * @property Collection|InvitadosConferencium[] $invitados_conferencia
 *
 * @package App\Models
 */
class Conferencia extends Model
{
	protected $table = 'conferencias';
	protected $primaryKey = 'id_conferencia';
	public $timestamps = false;

	protected $casts = [
		'fecha_inicio' => 'datetime',
		'fecha_fin' => 'datetime',
		'id_moderador' => 'int'
	];

	protected $fillable = [
		'asunto',
		'fecha_inicio',
		'fecha_fin',
		'id_moderador',
		'tipo',
		'grabacion','id_condominio'
	];

    public function condominio()
    {
        return $this->belongsTo(Condominio::class, 'id_condominio');
    }
	public function usuario()
	{
		return $this->belongsTo(Usuario::class, 'id_moderador');
	}

	public function invitados_conferencia()
	{
		return $this->hasMany(InvitadosConferencium::class, 'id_conferencia');
	}
}
