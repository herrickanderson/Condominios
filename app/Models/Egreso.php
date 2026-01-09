<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Egreso
 * 
 * @property int $id_egreso
 * @property int $id_condominio
 * @property int $id_proveedor
 * @property string $numero_documento
 * @property Carbon $fecha_documento
 * @property string $concepto
 * @property float $monto
 * @property Carbon $fecha_cobro
 * @property string $forma_cobro
 * @property string|null $estado
 * @property string|null $adjunto
 * @property Carbon|null $created_at
 * 
 * @property Condominio $condominio
 * @property Proveedore $proveedore
 *
 * @package App\Models
 */
class Egreso extends Model
{
	protected $table = 'egresos';
	protected $primaryKey = 'id_egreso';
	public $timestamps = false;

	protected $casts = [
		'id_condominio' => 'int',
		'id_proveedor' => 'int',
		'fecha_documento' => 'datetime',
		'monto' => 'float',
		'fecha_cobro' => 'datetime'
	];

	protected $fillable = [
		'id_condominio',
		'id_proveedor',
		'numero_documento',
		'fecha_documento',
		'concepto',
		'monto',
		'fecha_cobro',
		'forma_cobro',
		'estado',
		'adjunto'
	];

	public function condominio()
	{
		return $this->belongsTo(Condominio::class, 'id_condominio');
	}

	public function proveedore()
	{
		return $this->belongsTo(Proveedore::class, 'id_proveedor');
	}
}
