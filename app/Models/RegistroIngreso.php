<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroIngreso extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fecha_hora_ingreso',
        'ip',
        'navegador',
    ];

    // Relación con usuarios
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}
