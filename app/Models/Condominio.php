<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // Se agregó este use

/**
 * Class Condominio
 *
 * @property int $id_condominio
 * @property string $nombre
 * @property string $rut
 * @property string $direccion
 * @property string|null $telefono
 * @property string|null $email
 * @property string|null $logo
 * @property string|null $firma
 * @property Carbon|null $fecha_contable_inicial
 * @property float|null $fondo_reserva
 * @property string|null $datos_bancarios
 * @property string|null $mandato_khipu
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|Edificio[] $edificios
 * @property Collection|GastosComune[] $gastos_comunes
 * @property Collection|Egreso[] $egresos
 * @property Collection|Instalacione[] $instalaciones
 * @property Collection|Asamblea[] $asambleas
 * @property Collection|Carta[] $cartas
 *
 * @package App\Models
 */
class Condominio extends Model
{
    protected $table = 'condominios';
    protected $primaryKey = 'id_condominio';

    protected $casts = [
        'fecha_contable_inicial' => 'datetime',
        'fondo_reserva' => 'float'
    ];

    protected $fillable = [
        'nombre',
        'rut',
        'direccion',
        'telefono',
        'email',
        'logo',
        'firma',
        'fecha_contable_inicial',
        'fondo_reserva',
        'datos_bancarios',
        'mandato_khipu',
        'latitude',    // nuevo campo
        'longitude'    // nuevo campo
    ];
    // En el modelo Condominio.php
    protected $appends = ['logo_url', 'firma_url'];
    // Accessors para obtener la URL completa de logo y firma desde S3

    /**
     * Obtiene la URL completa del logo desde S3.
     *
     * @return string|null
     */
    public function getLogoUrlAttribute()
    {
        if (!$this->logo) {
            return null;
        }
        $disk = Storage::disk('s3');
        /** @var \Illuminate\Contracts\Filesystem\Cloud $disk */
        return $disk->url($this->logo);
    }

    /**
     * Obtiene la URL completa de la firma desde S3.
     *
     * @return string|null
     */
    public function getFirmaUrlAttribute()
    {
        if (!$this->firma) {
            return null;
        }
        /** @var \Illuminate\Contracts\Filesystem\Cloud $disk */
        $disk = Storage::disk('s3');
        return $disk->url($this->firma);
    }

    public function gastos_comunes()
    {
        return $this->hasMany(GastosComune::class, 'id_condominio');
    }

    public function egresos()
    {
        return $this->hasMany(Egreso::class, 'id_condominio');
    }

    public function instalaciones()
    {
        return $this->hasMany(Instalacione::class, 'id_condominio');
    }

    public function asambleas()
    {
        return $this->hasMany(Asamblea::class, 'id_condominio');
    }

    public function cartas()
    {
        return $this->hasMany(Carta::class, 'id_condominio');
    }
}
