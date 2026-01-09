<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ExtencionUsuario extends Pivot
{
    protected $table = 'extenciones_usuarios';

    protected $fillable = [
        'user_id',
        'id_extencion'
    ];
}
