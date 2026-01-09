<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UsuarioRole
 * 
 * @property int $id_usuario
 * @property int $id_rol
 * 
 * @property Usuario $usuario
 * @property Role $role
 *
 * @package App\Models
 */
class UsuarioRole extends Model
{
    protected $table = 'usuario_roles';
    public $incrementing = false;
    public $timestamps = false;

    // IMPORTANTE: Agrega $fillable para permitir asignación masiva
    protected $fillable = [
        'id_usuario',
        'id_rol',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_rol');
    }
}
