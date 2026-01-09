<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Proveedore
 * 
 * @property int $id_proveedor
 * @property string $nombre
 * @property string|null $contacto
 * @property string|null $email
 * @property string|null $telefono
 * @property string|null $otros_datos
 * @property Carbon|null $created_at
 * 
 * @property Collection|Egreso[] $egresos
 *
 * @package App\Models
 */
class Proveedore extends Model
{
	protected $table = 'proveedores';
	protected $primaryKey = 'id_proveedor';
	public $timestamps = false;

	protected $casts = [
		'otros_datos' => 'binary'
	];

	protected $fillable = [
		'nombre',
		'contacto',
		'email',
		'telefono',
		'otros_datos','id_condominio'
	];
    public function condominio()
    {
        return $this->belongsTo(Condominio::class, 'id_condominio');
    }
	public function egresos()
	{
		return $this->hasMany(Egreso::class, 'id_proveedor');
	}
}
