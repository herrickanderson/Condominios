<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Role
 *
 * @property int $id_rol
 * @property string $nombre
 * @property string|null $descripcion
 *
 * @property Collection|Usuario[] $usuarios
 *
 * @package App\Models
 */
class Role extends Model
{
	protected $table = 'roles';
	protected $primaryKey = 'id_rol';
	public $timestamps = false;

	protected $fillable = [
		'nombre',
		'descripcion'
	];


	public function usuarios()
	{
		return $this->belongsToMany(Usuario::class, 'usuario_roles', 'id_rol', 'id_usuario', 'id_rol');
	}
}
