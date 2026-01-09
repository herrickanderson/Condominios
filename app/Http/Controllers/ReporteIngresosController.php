<?php

namespace App\Http\Controllers;

use App\Models\RegistroIngreso;
use App\Models\Usuario;
use App\Models\Condominio;
use App\Models\Edificio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class ReporteIngresosController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Solo superadmin
        if (!$user->roles->contains('id_rol', 1)) {
            abort(403, 'Acceso denegado');
        }

        // Filtros
        $fechaInicio  = $request->input('fecha_inicio', Carbon::now()->subDays(30)->toDateString());
        $fechaFin     = $request->input('fecha_fin', Carbon::now()->toDateString());
        $condominioId = $request->input('condominio_id');
        $usuarioId    = $request->input('usuario_id');
        $edificioId   = $request->input('edificio_id'); // NUEVO FILTRO
        $pagina       = $request->input('page', 1);

        if ($usuarioId === '' || $usuarioId === 'null') {
            $usuarioId = null;
        }
        if ($edificioId === '' || $edificioId === 'null') {
            $edificioId = null;
        }

        // Obtener lista de condominios
        $condominios = Condominio::orderBy('nombre')->get();

        // Obtener lista de edificios (torres) según condominio si se selecciona
        if ($condominioId) {
            $edificios = Edificio::where('id_condominio', $condominioId)->orderBy('nombre')->get();
        } else {
            $edificios = Edificio::orderBy('nombre')->get();
        }

        // Consulta de ingresos con relaciones y filtros
        $ingresos = RegistroIngreso::with(['usuario.unidad.edificio', 'usuario'])
            ->when($fechaInicio && $fechaFin, function($q) use ($fechaInicio, $fechaFin) {
                return $q->whereBetween('fecha_hora_ingreso', [
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
            })
            ->when($condominioId, function($q) use ($condominioId) {
                return $q->whereHas('usuario', function($sub) use ($condominioId) {
                    $sub->where('id_condominio', $condominioId);
                });
            })
            ->when($usuarioId, function($q) use ($usuarioId) {
                return $q->where('user_id', $usuarioId);
            })
            ->when($edificioId, function($q) use ($edificioId) {
                return $q->whereHas('usuario.unidad.edificio', function($sub) use ($edificioId) {
                    $sub->where('id_edificio', $edificioId);
                });
            })
            ->latest()
            ->paginate(10)
            ->appends($request->all());

        // Usuarios filtrados por condominio
        $usuarios = Usuario::when($condominioId, function ($q) use ($condominioId) {
                return $q->where('id_condominio', $condominioId);
            })
            ->orderBy('name')
            ->get();

        // Estadísticas para gráficos, incluyendo el nuevo filtro de edificio
        $porDia = RegistroIngreso::selectRaw("DATE(fecha_hora_ingreso) as dia, COUNT(*) as total")
            ->when($fechaInicio && $fechaFin, function($q) use ($fechaInicio, $fechaFin) {
                return $q->whereBetween('fecha_hora_ingreso', [
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
            })
            ->when($condominioId, function($q) use ($condominioId) {
                return $q->whereHas('usuario', function($sub) use ($condominioId) {
                    $sub->where('id_condominio', $condominioId);
                });
            })
            ->when($edificioId, function($q) use ($edificioId) {
                return $q->whereHas('usuario.unidad.edificio', function($sub) use ($edificioId) {
                    $sub->where('id_edificio', $edificioId);
                });
            })
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $porHora = RegistroIngreso::selectRaw("EXTRACT(HOUR FROM fecha_hora_ingreso) as hora, COUNT(*) as total")
            ->when($fechaInicio && $fechaFin, function($q) use ($fechaInicio, $fechaFin) {
                return $q->whereBetween('fecha_hora_ingreso', [
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
            })
            ->when($condominioId, function($q) use ($condominioId) {
                return $q->whereHas('usuario', function($sub) use ($condominioId) {
                    $sub->where('id_condominio', $condominioId);
                });
            })
            ->when($edificioId, function($q) use ($edificioId) {
                return $q->whereHas('usuario.unidad.edificio', function($sub) use ($edificioId) {
                    $sub->where('id_edificio', $edificioId);
                });
            })
            ->groupBy('hora')
            ->orderBy('hora')
            ->get();

        $topUsuarios = RegistroIngreso::selectRaw("user_id, COUNT(*) as total")
            ->when($fechaInicio && $fechaFin, function($q) use ($fechaInicio, $fechaFin) {
                return $q->whereBetween('fecha_hora_ingreso', [
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
            })
            ->when($condominioId, function($q) use ($condominioId) {
                return $q->whereHas('usuario', function($sub) use ($condominioId) {
                    $sub->where('id_condominio', $condominioId);
                });
            })
            ->when($edificioId, function($q) use ($edificioId) {
                return $q->whereHas('usuario.unidad.edificio', function($sub) use ($edificioId) {
                    $sub->where('id_edificio', $edificioId);
                });
            })
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->with('usuario')
            ->take(5)
            ->get();

        $navegadores = RegistroIngreso::selectRaw("navegador, COUNT(*) as total")
            ->when($fechaInicio && $fechaFin, function($q) use ($fechaInicio, $fechaFin) {
                return $q->whereBetween('fecha_hora_ingreso', [
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
            })
            ->when($condominioId, function($q) use ($condominioId) {
                return $q->whereHas('usuario', function($sub) use ($condominioId) {
                    $sub->where('id_condominio', $condominioId);
                });
            })
            ->when($edificioId, function($q) use ($edificioId) {
                return $q->whereHas('usuario.unidad.edificio', function($sub) use ($edificioId) {
                    $sub->where('id_edificio', $edificioId);
                });
            })
            ->groupBy('navegador')
            ->orderByDesc('total')
            ->get();

        $data = [
            'condominios' => $condominios,
            'edificios'   => $edificios,
            'usuarios'    => $usuarios,
            'ingresos'    => $ingresos,
            'porDia'      => $porDia,
            'porHora'     => $porHora,
            'topUsuarios' => $topUsuarios,
            'navegadores' => $navegadores,
            'filtros'     => [
                'fecha_inicio'  => $fechaInicio,
                'fecha_fin'     => $fechaFin,
                'condominio_id' => $condominioId,
                'usuario_id'    => $usuarioId,
                'edificio_id'   => $edificioId,
                'page'          => $pagina,
            ],
        ];

        // Si la petición espera JSON, se retorna en ese formato para evitar redirección completa
        if ($request->wantsJson()) {
            return response()->json($data);
        }

        // De lo contrario, se utiliza Inertia (actualmente la ruta se usa para carga inicial)
        return Inertia::render('Reporte/Ingresos', $data);
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getFilteredData($request);
        $filename = 'ingresos_' . now()->format('Ymd_His') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $output = $this->generateExcelOutput($data);

        return response($output, 200, $headers);
    }

    public function exportCsv(Request $request)
    {
        $data = $this->getFilteredData($request);
        $filename = 'ingresos_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $output = $this->generateCsvOutput($data);

        return response($output, 200, $headers);
    }

    private function generateExcelOutput($data)
    {
        $html = '<html><head><meta charset="UTF-8"></head><body>';
        $html .= '<table border="1" style="border-collapse: collapse; width: 100%;">';
        $html .= '<tr style="background-color: #4CAF50; color: white;">';
        $html .= '<th style="padding: 8px;">Usuario</th>';
        $html .= '<th style="padding: 8px;">Condominio</th>';
        $html .= '<th style="padding: 8px;">Unidad</th>';
        $html .= '<th style="padding: 8px;">Fecha</th>';
        $html .= '<th style="padding: 8px;">Hora</th>';
        $html .= '<th style="padding: 8px;">IP</th>';
        $html .= '<th style="padding: 8px;">Navegador</th>';
        $html .= '</tr>';

        foreach ($data as $item) {
            $html .= '<tr>';
            $html .= '<td style="padding: 8px;">' . $item->usuario->name . ' ' . $item->usuario->apellidos . '</td>';
            $html .= '<td style="padding: 8px;">' . $item->usuario->id_condominio . '</td>';
            $html .= '<td style="padding: 8px;">' . ($item->usuario->unidad->nombre_unidad ?? '') . '</td>';
            $html .= '<td style="padding: 8px;">' . Carbon::parse($item->fecha_hora_ingreso)->format('Y-m-d') . '</td>';
            $html .= '<td style="padding: 8px;">' . Carbon::parse($item->fecha_hora_ingreso)->format('H:i:s') . '</td>';
            $html .= '<td style="padding: 8px;">' . $item->ip . '</td>';
            $html .= '<td style="padding: 8px;">' . $item->navegador . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table></body></html>';
        return $html;
    }

    private function generateCsvOutput($data)
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['Usuario', 'Condominio', 'Unidad', 'Fecha', 'Hora', 'IP', 'Navegador']);

        foreach ($data as $item) {
            fputcsv($output, [
                $item->usuario->name . ' ' . $item->usuario->apellidos,
                $item->usuario->id_condominio,
                $item->usuario->unidad->nombre_unidad ?? '',
                Carbon::parse($item->fecha_hora_ingreso)->format('Y-m-d'),
                Carbon::parse($item->fecha_hora_ingreso)->format('H:i:s'),
                $item->ip,
                $item->navegador,
            ]);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }

    private function getFilteredData(Request $request)
    {
        $fechaInicio  = $request->input('fecha_inicio', now()->subDays(30)->toDateString());
        $fechaFin     = $request->input('fecha_fin', now()->toDateString());
        $condominioId = $request->input('condominio_id');
        $usuarioId    = $request->input('usuario_id');

        if ($usuarioId === '' || $usuarioId === 'null') {
            $usuarioId = null;
        }

        return RegistroIngreso::with(['usuario.unidad'])
            ->when($fechaInicio && $fechaFin, function ($q) use ($fechaInicio, $fechaFin) {
                return $q->whereBetween('fecha_hora_ingreso', [
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59'
                ]);
            })
            ->when($condominioId, function ($q) use ($condominioId) {
                return $q->whereHas('usuario', function ($q) use ($condominioId) {
                    return $q->where('id_condominio', $condominioId);
                });
            })
            ->when($usuarioId, function ($q) use ($usuarioId) {
                return $q->where('user_id', $usuarioId);
            })
            ->get();
    }
}
