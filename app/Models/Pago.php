<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Pago
 *
 * @property int $id_pago
 * @property int $id_gasto
 * @property int $id_usuario
 * @property float $monto
 * @property Carbon $fecha_pago
 * @property string $metodo_pago
 * @property string|null $referencia
 * @property Carbon|null $created_at
 *
 * @property GastosComune $gastos_comune
 * @property Usuario $usuario
 *
 * @package App\Models
 */
class Pago extends Model
{
    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';
    public $timestamps = false;

    protected $casts = [
        'id_gasto' => 'int',
        'id_usuario' => 'int',
        'monto' => 'float',
        'fecha_pago' => 'datetime'
    ];

    protected $fillable = [
        'id_gasto',
        'id_usuario',
        'monto',
        'fecha_pago',
        'metodo_pago',
        'referencia',
        'archivo',
        'nombre_archivo',
        'estado',
        'observacion',
        'observacion_admin'
    ];

    public function getRouteKeyName() { return 'id_pago'; }
    public function gastos_comune()
    {
        return $this->belongsTo(GastosComune::class, 'id_gasto');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    // app/Models/Pago.php
    public function distribucionesPagadas()
    {
        // Ejemplo de hasManyThrough (complejo), o cualquier relación que conecte
        // Pago -> (id_gasto) -> GastosComune -> (id_gasto) -> DetalleGastoComun -> (id_detalle) -> DistribucionGasto
        return $this->hasManyThrough(
            DistribucionGasto::class,
            DetalleGastoComun::class,
            'id_gasto',      // foreignKey en detalle_gasto_comun
            'id_detalle',    // foreignKey en distribucion_gasto
            'id_gasto',      // localKey en pagos
            'id_detalle'     // localKey en detalle_gasto_comun
        );
    }
}
