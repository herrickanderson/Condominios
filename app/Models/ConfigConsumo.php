<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigConsumo extends Model
{
    use HasFactory;

    protected $table = 'config_consumos';
    protected $primaryKey = 'id_config';
    public $timestamps = true;

    protected $fillable = [
        'id_condominio',
        'id_tipo_gasto',
        'precio',          // Precio en soles
        'precio_dolares',  // Precio en dólares
    ];

    // Relación con Condominio
    public function condominio()
    {
        return $this->belongsTo(Condominio::class, 'id_condominio', 'id_condominio');
    }

    // Relación con TipoGastoComun
    public function tipoGasto()
    {
        return $this->belongsTo(TipoGastoComun::class, 'id_tipo_gasto', 'id_tipo_gasto');
    }
}
