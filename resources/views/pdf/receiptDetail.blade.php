<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Comprobante de Detalles</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #d1f0e4;
            padding: 10px 20px;
            border-bottom: 2px solid #3c8c5a;
        }

        .header h1 {
            font-size: 18px;
            color: #2c6d43;
            margin: 0;
        }

        .section {
            padding: 15px 20px;
        }

        h2 {
            font-size: 14px;
            color: #3c8c5a;
            margin-top: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
            font-size: 12px;
        }

        .footer {
            padding: 10px 20px;
            border-top: 2px solid #3c8c5a;
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $condominio->nombre }}</h1>
        <p>{{ $condominio->direccion }}</p>
        <p>Doc: {{ $condominio->rut }}</p>
    </div>

    <div class="section">
        <p><strong>Copropietario:</strong> {{ $user->name }} {{ $user->apellidos }}</p>
        <p><strong>Torre:</strong> {{ $user->unidad->edificio->nombre ?? 'N/A' }}</p>
        <p><strong>Unidad:</strong> {{ $user->unidad->nombre_unidad ?? 'N/A' }}</p>
        <p><strong>Gasto Común:</strong> {{ $gasto->descripcion }}</p>
        <p><strong>Periodo:</strong> {{ optional($gasto->fecha_periodo)->format('F Y') }}</p>
        <p><strong>Fecha Vencimiento:</strong> {{ optional($gasto->fecha_vencimiento)->format('d/m/Y') }}</p>
        <p><strong>Monto Total:</strong> {{ $simbolo }} {{ number_format($montoAsignadoUsuario, 2) }}</p>
    </div>

    <div class="section">
        <h2>🧾 Detalles por Unidad</h2>
        <table>
            <thead>
                <tr>
                    <th>Tipo de Gasto</th>
                    <th>Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detallesUsuario as $gastoGrupo)
                    @foreach ($gastoGrupo as $item)
                        <tr>
                            <td>{{ $item['tipo'] }}</td>
                            <td>{{ $simbolo }} {{ number_format($item['monto'], 2) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        @if (!empty($extensiones))
            <h2>📦 Extensiones</h2>
            @foreach ($extensiones as $tipo => $grupo)
                @foreach ($grupo as $ext)
                    <p><strong>{{ ucfirst($tipo) }}:</strong> {{ $ext['datos']->nombre }} ({{ $ext['datos']->area }}
                        {{ $ext['datos']->unidad_medida }})</p>
                    <table>
                        <thead>
                            <tr>
                                <th>Tipo de Gasto</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ext['detalles'] as $item)
                                <tr>
                                    <td>{{ $item['tipo'] }}</td>
                                    <td>{{ $simbolo }} {{ number_format($item['monto'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @endforeach
        @endif

        <h2>💳 Formas de Pago</h2>
        @if ($configPago)
            <p><strong>Banco:</strong> {{ $configPago->banco }}</p>
            <p><strong>N° Cuenta:</strong> {{ $configPago->numero_cuenta }}</p>
            <p><strong>CCI:</strong> {{ $configPago->cci }}</p>
            <p><strong>Propietario:</strong> {{ $configPago->propietario }}</p>
        @endif
    </div>

    <div class="footer">
        Total General: {{ $simbolo }} {{ number_format($montoAsignadoUsuario, 2) }}
    </div>
</body>

</html>
