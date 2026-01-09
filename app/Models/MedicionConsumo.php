<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicionConsumo extends Model
{
    // Opcional: si el nombre de la tabla no sigue la convención plural, especificarlo:
        protected $table = 'mediciones_consumo';

        protected $fillable = [
            'id_condominio',
            'id_unidad',
            'id_tipo_gasto',
            'fecha_medicion',
            'lectura_anterior',
            'lectura_actual',
            'consumo',
            'id_user',
            'status',
            'observacion',
            'id_extencion',
            'archivo', // nuevo campo
        ];

        // Relaciones
        public function condominio()
        {
            return $this->belongsTo(Condominio::class, 'id_condominio', 'id_condominio');
        }

        public function unidad()
        {
            return $this->belongsTo(Unidade::class, 'id_unidad', 'id_unidad');
        }

        public function tipoGasto()
        {
            return $this->belongsTo(TipoGastoComun::class, 'id_tipo_gasto', 'id_tipo_gasto');
        }

        public function user()
        {
            return $this->belongsTo(Usuario::class, 'id_user');
        }
}
