<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\DistribucionGasto;
use App\Models\DetalleGastoComun;
use App\Models\Unidade;

class DistribucionGastoController extends Controller
{
    /**
     * Muestra la lista de distribuciones generadas, filtrando por el condominio del usuario.
     */
    public function index(Request $request)
    {
        $userCondominio = Auth::user()->id_condominio;

        // Cargamos distribuciones y filtramos por condominio en el gasto común
        $distribuciones = DistribucionGasto::with([
            'detalle_gasto_comun.gastos_comune',
            'detalle_gasto_comun.tipo_gasto_comun',
            'unidad'
        ])
            ->whereHas('detalle_gasto_comun.gastos_comune', function ($query) use ($userCondominio) {
                $query->where('id_condominio', $userCondominio);
            })
            ->paginate(15);

        return Inertia::render('DistribucionGasto/Index', [
            'distribuciones' => $distribuciones,
        ]);
    }

    /**
     * Muestra el formulario para generar la distribución.
     */
    public function create()
    {
        $userCondominio = Auth::user()->id_condominio;

        // 1. Lista de GASTOS del condominio con estado 'Pendiente'
        $gastos = \App\Models\GastosComune::where('id_condominio', $userCondominio)
            ->where('estado_pago', 'Pendiente')
            ->get();

        // 2. Lista de DETALLES que aún no tengan distribución
        //    (Si quieres verlos todos, comenta ->doesntHave('distribucion_gastos'))
        $detalles = DetalleGastoComun::with(['gastos_comune', 'tipo_gasto_comun'])
            ->whereHas('gastos_comune', function ($q) use ($userCondominio) {
                $q->where('id_condominio', $userCondominio);
            })
            ->doesntHave('distribucion_gastos')
            ->get();

        // 3. Lista de UNIDADES (cargamos el propietario para el prorrateo)
        $unidades = Unidade::with('propietario')
            ->where('id_condominio', $userCondominio)
            ->get();

        return Inertia::render('DistribucionGasto/Create', [
            'gastos'   => $gastos,
            'detalles' => $detalles,
            'unidades' => $unidades,
        ]);
    }

    /**
     * Calcula y almacena la distribución de un detalle de gasto común.
     */
    public function store(Request $request)
    {
        $userCondominio = Auth::user()->id_condominio;

        $validated = $request->validate([
            'id_detalle' => 'required|exists:detalle_gasto_comun,id_detalle',
            // Para gasto individual se puede enviar id_unidad
            'id_unidad'  => 'nullable|exists:unidades,id_unidad',
        ]);

        // 1. Obtenemos el detalle y verificamos su condominio
        $detalle = DetalleGastoComun::with(['gastos_comune', 'tipo_gasto_comun'])
            ->findOrFail($validated['id_detalle']);

        if ($detalle->gastos_comune->id_condominio !== $userCondominio) {
            abort(403, 'Acceso no autorizado.');
        }

        // 2. Lógica para "gasto individual"
        if (!$detalle->tipo_gasto_comun->aplica_a_todos_edificios && !$detalle->id_edificio) {
            // Significa que se asigna a una sola unidad
            if (empty($validated['id_unidad'])) {
                return redirect()->back()->withErrors([
                    'id_unidad' => 'Debe seleccionar la unidad para este gasto individual.'
                ]);
            }
            $unidad = Unidade::with('propietario')
                ->where('id_unidad', $validated['id_unidad'])
                ->where('id_condominio', $userCondominio)
                ->first();
            if (!$unidad) {
                abort(403, 'La unidad no pertenece a este condominio.');
            }

            // Asignamos el monto completo
            DistribucionGasto::create([
                'id_detalle'     => $detalle->id_detalle,
                'id_unidad'      => $unidad->id_unidad,
                'monto_asignado' => $detalle->monto_detalle,
            ]);
        } else {
            // 3. Gasto masivo (torre o condominio completo)
            //    Determinamos qué unidades participan
            if ($detalle->id_edificio) {
                // Solo las unidades del edificio
                $unidades = Unidade::with('propietario')
                    ->where('id_edificio', $detalle->id_edificio)
                    ->where('id_condominio', $userCondominio)
                    ->get();
            } else {
                // Todas las unidades del condominio
                $unidades = Unidade::with('propietario')
                    ->where('id_condominio', $userCondominio)
                    ->get();
            }

            // 4. Tomamos el tipo de prorrateo del "primer propietario" encontrado
            //    (Asumiendo que todas las unidades comparten el mismo prorrateo)
            $prorrateoType = 1; // Por defecto: 1 = M²
            $primeraUnidad = $unidades->first();
            if ($primeraUnidad && $primeraUnidad->propietario) {
                $prorrateoType = $primeraUnidad->propietario->id_tipo_prorrateo;
            }

            // 5. Hacemos la distribución según prorrateo
            if ($prorrateoType == 1) {
                // Por área (m²)
                $totalArea = $unidades->sum('area');
                if ($totalArea <= 0) {
                    return redirect()->back()->withErrors([
                        'error' => 'El área total de las unidades es cero, no se puede distribuir.'
                    ]);
                }
                foreach ($unidades as $unidad) {
                    $montoAsignado = round($detalle->monto_detalle * ($unidad->area / $totalArea), 2);
                    DistribucionGasto::create([
                        'id_detalle'     => $detalle->id_detalle,
                        'id_unidad'      => $unidad->id_unidad,
                        'monto_asignado' => $montoAsignado,
                    ]);
                }
            } elseif ($prorrateoType == 2) {
                // Equitativo
                $countUnits = $unidades->count();
                if ($countUnits <= 0) {
                    return redirect()->back()->withErrors([
                        'error' => 'No hay unidades para distribuir.'
                    ]);
                }
                $montoPorUnidad = round($detalle->monto_detalle / $countUnits, 2);
                foreach ($unidades as $unidad) {
                    DistribucionGasto::create([
                        'id_detalle'     => $detalle->id_detalle,
                        'id_unidad'      => $unidad->id_unidad,
                        'monto_asignado' => $montoPorUnidad,
                    ]);
                }
            } else {
                // Otros métodos si fuera el caso
                return redirect()->back()->withErrors([
                    'error' => 'El método de prorrateo no está implementado.'
                ]);
            }
        }

        return redirect()->route('distribucion_gasto.index')
            ->with('success', 'Distribución generada correctamente.');
    }

    // Métodos show, edit, update y destroy según se requiera.
    public function show($id)
    {
        // Implementar si es necesario
    }

    public function edit($id)
    {
        // Implementar si es necesario
    }

    public function update(Request $request, $id)
    {
        // Implementar si es necesario
    }

    public function destroy($id)
    {
        // Implementar si es necesario
    }
}
