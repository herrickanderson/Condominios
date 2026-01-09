<?php

namespace App\Http\Controllers;

use App\Models\RegistroIngreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistroIngresoController extends Controller
{
    public function registrarIngreso(Request $request)
    {
        RegistroIngreso::create([
            'user_id' => Auth::id(),
            'ip' => $request->ip(),
            'navegador' => $request->header('User-Agent'),
        ]);

        return response()->json(['mensaje' => 'Ingreso registrado']);
    }

    public function listarIngresos()
    {
        $ingresos = RegistroIngreso::with('usuario')->orderBy('fecha_hora_ingreso', 'desc')->get();
        return response()->json($ingresos);
    }
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(RegistroIngreso $registroIngreso)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RegistroIngreso $registroIngreso)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RegistroIngreso $registroIngreso)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RegistroIngreso $registroIngreso)
    {
        //
    }
}
