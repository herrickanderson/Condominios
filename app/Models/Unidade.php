<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Unidade extends Model
{
    protected $table = 'unidades';
    protected $primaryKey = 'id_unidad';

    protected $casts = [
        'id_edificio' => 'int',
        'id_asignacion_padre' => 'int'
    ];

    protected $fillable = [
        'nombre_unidad',
        'estado',
        'id_edificio',
        'id_asignacion_padre',
        'area',          // Campo para metros cuadrados
        'unidad_medida', // Campo para la unidad (por ejemplo, "mt2")
        'id_condominio', // Clave foránea para el condominio
        'tipo_unidad',    // ← Asegúrate de incluir este campo
    ];

    public function edificio()
    {
        return $this->belongsTo(Edificio::class, 'id_edificio');
    }
    // Ejemplo de relación con la tabla intermedia
    public function serviciosExtras()
    {
        return $this->hasMany(UnidadServicioExtra::class, 'id_unidad');
    }
    // Relación para obtener el usuario (propietario) asignado a la unidad.
    public function propietario()
    {
        return $this->hasOne(Usuario::class, 'id_unidad');
    }

    public function visitas()
    {
        return $this->hasMany(Visita::class, 'id_unidad');
    }

    public function permisos_mudanzas()
    {
        return $this->hasMany(PermisosMudanza::class, 'id_unidad');
    }

    public function distribucion_gastos()
    {
        return $this->hasMany(DistribucionGasto::class, 'id_unidad');
    }
    public function users()
    {
        // Asumiendo que tu modelo de usuario se llama "Usuario"
        // y que la FK es "id_unidad" en la tabla "users"
        return $this->hasMany(\App\Models\Usuario::class, 'id_unidad');
    }
}
