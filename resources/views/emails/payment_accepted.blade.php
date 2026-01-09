<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Pago Aceptado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #16a34a;
            /* Verde */
            padding: 15px;
            text-align: center;
            color: #ffffff;
        }

        .content {
            background-color: #ffffff;
            padding: 20px;
        }

        .footer {
            font-size: 14px;
            color: #999999;
            text-align: center;
            margin-top: 20px;
        }

        h1,
        h2,
        h3 {
            margin-bottom: 10px;
            font-weight: bold;
        }

        ul {
            list-style-type: none;
            padding-left: 0;
        }

        li {
            margin-bottom: 5px;
        }

        .highlight {
            color: #16a34a;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>¡Pago Aceptado!</h1>
    </div>
    <div class="container">
        <div class="content">
            <p>Hola <strong>{{ $usuario->name }} {{ $usuario->apellidos }}</strong>,</p>

            <p style="color: #16a34a; font-weight: bold;">¡Tu pago ha sido aceptado!</p>
            <ul>
                <li><span class="highlight">Condominio:</span> {{ $condominio->nombre }}</li>
                <li><span class="highlight">Monto Pagado:</span> S/ {{ number_format($montoTotal, 2) }}</li>
            </ul>

            <p>Adjuntamos tu comprobante de ingreso en PDF.</p>
            <p>Si tienes cualquier consulta, no dudes en comunicarte con nosotros o con tu administrador de condominio.
            </p>

            <p>¡Gracias por tu atención!</p>
        </div>
        <div class="footer">
            <p><em>{{ config('app.name') }}</em></p>
        </div>
    </div>
</body>

</html>
