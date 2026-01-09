<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class Usuario
 *
 * @property int $id_usuario
 * @property string $nombres
 * @property string $apellidos
 * @property string $email
 * @property string|null $telefono
 * @property string $rut
 * @property string $contraseña
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|Role[] $roles
 * @property Collection|Pago[] $pagos
 * @property Collection|Reserva[] $reservas
 * @property Collection|Incidencia[] $incidencias
 * @property Collection|HistorialIncidencia[] $historial_incidencias
 * @property Collection|Conferencia[] $conferencias
 * @property Collection|InvitadosConferencium[] $invitados_conferencia
 * @property Collection|RespuestasEncuestum[] $respuestas_encuesta
 * @property Collection|Visita[] $visitas
 * @property Collection|PermisosMudanza[] $permisos_mudanzas
 *
 * @package App\Models
 */

class Usuario extends Authenticatable
{
    // Cambia el nombre de la tabla a 'users'
    protected $table = 'users';

    // Si el primary key en la tabla 'users' es 'id' (el predeterminado de Laravel), actualízalo:
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'apellidos',
        'email',
        'telefono',
        'rut',
        'password',
        'estado',
        'id_condominio',
        'id_tipo_prorrateo',
        'id_unidad', // <--- Agregar aquí
        // NUEVOS CAMPOS
        'tipo_documento',
        'numero_documento',
        'tiene_extencion',
    ];
    // En App\Models\Usuario.php

    public function tipoProrrateo()
    {
        //return $this->belongsTo(tipoProrrateo::class, 'id_tipo_prorrateo', 'id');
        return $this->belongsTo(TipoProrrateo::class, 'id_tipo_prorrateo');
    }
    /**
     * Obtiene los roles asociados al usuario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'usuario_roles',
            'id_usuario',
            'id_rol'
        );
    }

    public function condominio()
    {
        return $this->belongsTo(Condominio::class, 'id_condominio');
    }
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_usuario');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_usuario');
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class, 'id_usuario_destino');
    }

    public function historial_incidencias()
    {
        return $this->hasMany(HistorialIncidencia::class, 'remitente');
    }

    public function conferencias()
    {
        return $this->hasMany(Conferencia::class, 'id_moderador');
    }

    public function invitados_conferencia()
    {
        return $this->hasMany(InvitadosConferencium::class, 'id_usuario');
    }

    public function respuestas_encuesta()
    {
        return $this->hasMany(RespuestasEncuestum::class, 'id_usuario');
    }

    public function visitas()
    {
        return $this->hasMany(Visita::class, 'id_usuario');
    }

    public function permisos_mudanzas()
    {
        return $this->hasMany(PermisosMudanza::class, 'id_usuario');
    }
    public function unidad()
    {
        return $this->belongsTo(Unidade::class, 'id_unidad');
    }
    public function hasRole($roleName)
    {
        return $this->roles()->where('nombre', $roleName)->exists();
    }


    // app/Models/Usuario.php
    public function extenciones()
    {
        return $this->belongsToMany(
            Extencion::class,
            'extenciones_usuarios',  // nombre de la tabla pivot
            'user_id',               // FK en la tabla pivot que referencia a la PK de 'users'
            'id_extencion'           // FK en la tabla pivot que referencia a la PK de 'extenciones'
        )->with('edificio', 'serviciosExtras');
    }
}
