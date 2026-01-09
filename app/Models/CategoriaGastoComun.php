<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaGastoComun extends Model
{
    protected $table = 'categoria_gasto_comun';
    protected $primaryKey = 'id_categoria';

    protected $fillable = ['nombre', 'id_condominio'];

    public function tipos()
    {
        return $this->hasMany(TipoGastoComun::class, 'id_categoria');
    }

    public function condominio()
    {
        return $this->belongsTo(Condominio::class, 'id_condominio');
    }
}
