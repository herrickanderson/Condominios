<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante de Ingreso</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.2;
        }

        /* Encabezado Principal */
        .header {
            background-color: #e0f0e9;
            padding: 5px 10px; /* Se redujo el padding */
            display: flex;
            justify-content: space-between;
            align-items: center; /* Centrado verticalmente */
            border-bottom: 2px solid #3c8c5a;
        }

        .header .condominio-info {
            line-height: 1.2;
        }

        .header .condominio-info h1 {
            margin: 0 0 2px 0;
            font-size: 14px; /* Tamaño reducido */
            text-transform: uppercase;
            color: #2c6d43;
        }

        .header .condominio-info p {
            margin: 0;
            font-size: 12px; /* Tamaño reducido */
        }

        .header .folio-info {
            text-align: right;
        }

        .header .folio-info h2 {
            margin: 0;
            font-size: 16px; /* Tamaño reducido */
            text-transform: uppercase;
            color: #3c8c5a;
        }

        .header .folio-info p {
            margin: 2px 0;
            font-size: 12px; /* Tamaño reducido */
        }

        /* Contenedor global */
        .container {
            padding: 10px 15px;
        }

        /* Sección de datos en dos columnas */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .info-grid p {
            margin: 4px 0;
            font-size: 13px;
        }

        .info-grid p strong {
            color: #2c6d43;
        }

        /* Bloque de descripción */
        .description-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 15px;
        }

        .description-table th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 5px;
            border: 1px solid #ccc;
            font-size: 13px;
            color: #2c6d43;
        }

        .description-table td {
            padding: 5px;
            border: 1px solid #ccc;
            font-size: 13px;
        }

        /* Título de secciones (Forma de Pago, etc.) */
        .section-title {
            font-size: 14px; /* Ajustado */
            text-transform: uppercase;
            color: #3c8c5a;
            margin: 15px 0 5px;
            border-bottom: 1px solid #ccc;
            display: inline-block;
            padding-bottom: 2px;
        }

        /* Pie con total y firma */
        .footer {
            padding: 5px 10px; /* Se redujo el padding */
            display: flex;
            justify-content: space-between;
            align-items: center; /* Ajustado para centrar */
            border-top: 2px solid #3c8c5a;
        }

        .footer .total {
            font-size: 14px;
            font-weight: bold;
            color: #3c8c5a;
        }

        .footer .firma {
            text-align: right;
            font-size: 13px;
        }

        .footer .firma span {
            display: inline-block;
            margin-top: 20px; /* Se redujo el espacio para la firma */
            border-top: 1px solid #333;
            padding-top: 3px;
        }
    </style>
</head>
<body>
    <!-- Encabezado Principal -->
    <div class="header">
        <div class="condominio-info">
            <h1>{{ $condominio->nombre ?? 'Nombre del Condominio' }}</h1>
            <p>{{ $condominio->direccion ?? 'Dirección del Condominio' }}</p>
            <p>Doc: {{ $condominio->rut ?? '' }}</p>
        </div>
        <div class="folio-info">
            <h2>Comprobante de Ingreso</h2>
            @if($pago)
                <p><strong>Fecha:</strong>
                    {{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d-m-Y') }}
                </p>
                <p><strong>Folio N°:</strong> {{ $pago->id_pago }}</p>
            @else
                <!-- Si no hay $pago, muestra algo genérico o vacío -->
                <p><strong>Fecha:</strong> -</p>
                <p><strong>Folio N°:</strong> -</p>
            @endif
        </div>
    </div>

    <div class="container">
        <!-- Primera fila de información (Copropietario, Residente) / (Torre, Unidad) -->
        <div class="info-grid">
            <div>
                <p><strong>Copropietario:</strong> {{ $nombrePropietario }}</p>
                <p><strong>Residente:</strong> {{ $nombreResidente }}</p>
            </div>
            <div>
                @if($pago && $pago->usuario && $pago->usuario->unidad && $pago->usuario->unidad->edificio)
                    <p><strong>Torre:</strong> {{ $pago->usuario->unidad->edificio->nombre }}</p>
                    <p><strong>Unidad:</strong> {{ $pago->usuario->unidad->nombre_unidad }}</p>
                @else
                    <!-- Si no hay pago o no se puede acceder a la unidad -->
                    <p><strong>Torre:</strong> N/A</p>
                    <p><strong>Unidad:</strong> N/A</p>
                @endif
            </div>
        </div>

        <!-- Segunda fila de información (Gasto Común / Periodo) -->
        <div class="info-grid">
            <div>
                <p><strong>Gasto Común:</strong> {{ $descripcionGasto }}</p>
            </div>
            <div>
                @php
                    $fechaPeriodo = $gastoComun && $gastoComun->fecha_periodo
                        ? $gastoComun->fecha_periodo->format('m-Y')
                        : '';
                @endphp
                <p><strong>Periodo:</strong> {{ $fechaPeriodo }}</p>
            </div>
        </div>

        <!-- Tabla de descripción -->
        <table class="description-table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $descripcionGasto }}</td>
                    <td>S/ {{ number_format($montoTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Sección de FORMA DE PAGO -->
        <h3 class="section-title">Forma de Pago</h3>
        @if($pago)
            <p><strong>Método:</strong> {{ $pago->metodo_pago }}</p>
            @if (!empty($pago->banco))
                <p><strong>Banco:</strong> {{ $pago->banco }}</p>
            @endif
            @if (!empty($pago->numero_transaccion))
                <p><strong>N° de Transacción:</strong> {{ $pago->numero_transaccion }}</p>
            @endif
            @if (!empty($pago->observacion))
                <p><strong>Observación:</strong> {{ $pago->observacion }}</p>
            @endif
        @else
            <!-- Si no hay pago, mostrar algo opcional o nada -->
            <p><em>No se registró pago asociado.</em></p>
        @endif
    </div>

    <!-- Footer con Monto Pagado y Firma -->
    <div class="footer">
        <div class="total">
            Monto Pagado: S/ {{ number_format($montoTotal, 2) }}
        </div>
        <div class="firma">
            <span>Firma Administrador</span>
        </div>
    </div>
</body>
</html>
