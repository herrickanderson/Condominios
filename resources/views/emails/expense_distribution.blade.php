<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Distribución de Gasto Común</title>
</head>

<body style="font-family: Arial, sans-serif; font-size: 14px; background-color: #f8f9fa; padding: 20px; color: #333;">
    <div
        style="max-width: 650px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 10px; border: 1px solid #ddd;">
        <div style="background-color: #00695c; color: #fff; padding: 12px 20px; border-radius: 6px 6px 0 0;">
            <h2 style="margin: 0; font-size: 18px;">Distribución de Gasto Común</h2>
        </div>

        <p>Hola <strong>{{ $nombreCompleto }}</strong>,</p>
        <p>Se ha generado la distribución del gasto común: <strong>{{ $mes }}</strong></p>

        <div style="border-left: 4px solid #2e7d32; background-color: #eef7f2; padding: 10px 15px; margin: 15px 0;">
            <p><strong>Condominio:</strong> {{ $condominio->nombre }}</p>
            <p><strong>Monto Total Asignado:</strong> {{ $simbolo }} {{ number_format($montoAsignado, 2) }}</p>
            <p><strong>Periodo:</strong> {{ $mes }}</p>
            <p><strong>Vencimiento:</strong> {{ optional($gasto->fecha_vencimiento)->format('d/m/Y') }}</p>
        </div>

        <h3 style="color: #2e7d32;">🧾 Detalles por Unidad</h3>
        @foreach ($detallesUsuario as $gastoGrupo)
            @foreach ($gastoGrupo as $item)
                <p>• {{ $item['tipo'] }} - {{ $simbolo }} {{ number_format($item['monto'], 2) }}</p>
            @endforeach
        @endforeach

        @if (!empty($extensiones))
            <h3 style="color: #00838f;">📦 Extensiones</h3>
            @foreach ($extensiones as $tipo => $grupo)
                <p><strong>{{ ucfirst($tipo) }}</strong></p>
                @foreach ($grupo as $ext)
                    <p style="margin-left: 10px;">• {{ $ext['datos']->nombre }} ({{ $ext['datos']->area }}
                        {{ $ext['datos']->unidad_medida }})</p>
                    @foreach ($ext['detalles'] as $item)
                        <p style="margin-left: 30px;">▪ {{ $item['tipo'] }} - {{ $simbolo }}
                            {{ number_format($item['monto'], 2) }}</p>
                    @endforeach
                @endforeach
            @endforeach
        @endif

        <h3 style="color: #f9a825;">💳 Formas de Pago</h3>
        @if ($configPago)
            <p><strong>Banco:</strong> {{ $configPago->banco }}</p>
            <p><strong>Cuenta:</strong> {{ $configPago->numero_cuenta }}</p>
            <p><strong>CCI:</strong> {{ $configPago->cci }}</p>
            <p><strong>Propietario:</strong> {{ $configPago->propietario }}</p>
            @if ($configPago->qr_path)
                <p><strong>QR Pago:</strong></p>
                <img src="{{ $awsBaseUrl . '/' . $configPago->qr_path }}" alt="QR Pago"
                    style="width: 150px; height: auto;" />
            @endif
        @endif

        <p style="margin-top: 20px;">Consulta más en <a href="https://condominios.solufacil.com"
                style="color: #00695c;">condominios.solufacil.com</a></p>
        <p style="font-size: 12px; color: #777;">Este correo es informativo. No respondas a este mensaje.</p>
    </div>
</body>

</html>
