<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extencion extends Model
{
    use HasFactory;

    protected $table = 'extenciones';
    protected $primaryKey = 'id_extencion';

    protected $fillable = [
        'nombre',
        'estado',
        'id_edificio',
        'id_condominio',
        'area',
        'unidad_medida',
        'tipo_extencion', // Puede ser "Estacionamiento" o "Bodega", etc.
        'cobro_unico'   // 0 o 1, aplicable para "Estacionamiento"
    ];

    // Relación con Edificio (asumiendo que tienes el modelo Edificio)
    public function edificio()
    {
        return $this->belongsTo(Edificio::class, 'id_edificio', 'id_edificio');
    }

    // Relación con Condominio (asumiendo que tienes el modelo Condominio)
    public function condominio()
    {
        return $this->belongsTo(Condominio::class, 'id_condominio', 'id_condominio');
    }

    // Relación con los servicios extra
    public function serviciosExtras()
    {
        return $this->hasMany(ExtencionServicioExtra::class, 'id_extencion', 'id_extencion');
    }



    // Relación muchos a muchos con Usuarios a través del pivote extenciones_usuarios
    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'extenciones_usuarios', 'id_extencion', 'user_id');
    }
}
