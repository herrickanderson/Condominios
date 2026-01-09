<?php

namespace App\Http\Controllers;

use App\Models\Edificio;
use App\Models\Unidade;
use App\Models\User;
use App\Models\GastosComune;
use App\Models\DistribucionGasto;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VigilanciaController extends Controller
{
    /**
     * Muestra la vista de vigilancia, con la información de torres, unidades y estado de pago.
     */
    public function index()
    {
        $user = Auth::user();
        $condominioId = $user->id_condominio;
    
        // Edificios
        $edificios = \App\Models\Edificio::where('id_condominio', $condominioId)->get();
    
        // Último gasto vencido
        $ultimoGasto = \App\Models\GastosComune::where('id_condominio', $condominioId)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<=', now())
            ->orderBy('fecha_vencimiento', 'desc')
            ->first();
    
        // Estructura final
        $data = [];
    
        foreach ($edificios as $edificio) {
            $unidades = \App\Models\Unidade::where('id_edificio', $edificio->id_edificio)->get();
            $unidadesData = [];
    
            foreach ($unidades as $unidad) {
                // Arrendatario (rol=3)
                $arrendatario = \App\Models\Usuario::where('id_unidad', $unidad->id_unidad)
                    ->whereHas('roles', function ($q) {
                        $q->where('roles.id_rol', 3);
                    })
                    ->first();
    
                // Propietario (rol=4)
                $propietario = \App\Models\Usuario::where('id_unidad', $unidad->id_unidad)
                    ->whereHas('roles', function ($q) {
                        $q->where('roles.id_rol', 4);
                    })
                    ->first();
    
                // Determinación de estado (verde o rojo)
                $status = 'green'; 
                if ($ultimoGasto) {
                    $detalleIds = $ultimoGasto->detalle_gasto_comuns()->pluck('id_detalle');
                    $distribucion = \App\Models\DistribucionGasto::whereIn('id_detalle', $detalleIds)
                        ->where('id_unidad', $unidad->id_unidad)
                        ->sum('monto_asignado');
    
                    if ($distribucion > 0) {
                        $pagos = \App\Models\Pago::where('id_gasto', $ultimoGasto->id_gasto)
                            ->where(function ($query) use ($arrendatario, $propietario) {
                                if ($arrendatario) {
                                    $query->where('id_usuario', $arrendatario->id);
                                } elseif ($propietario) {
                                    $query->where('id_usuario', $propietario->id);
                                }
                            })
                            ->where('estado', 'aceptado')
                            ->sum('monto');
    
                        if ($pagos < $distribucion) {
                            $status = 'red';
                        }
                    }
                }
    
                $unidadesData[] = [
                    'unidad'       => $unidad->nombre_unidad,
                    'arrendatario' => $arrendatario ? ($arrendatario->name . ' ' . $arrendatario->apellidos) : null,
                    'propietario'  => $propietario ? ($propietario->name . ' ' . $propietario->apellidos) : null,
                    'status'       => $status,
                ];
            }
    
            $data[] = [
                'edificio' => $edificio->nombre,
                'unidades' => $unidadesData,
            ];
        }
    
        return \Inertia\Inertia::render('Vigilancia/Index', [
            'data'        => $data,
            'ultimoGasto' => $ultimoGasto, // Enviamos el último gasto para mostrar su fecha de vencimiento
        ]);
    }
    
}
