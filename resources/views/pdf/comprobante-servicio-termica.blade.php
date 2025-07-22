<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante</title>
    <style>
        * {
            box-sizing: border-box;
        }
        html, body {
            width: 80mm;
            margin: 0 auto;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            background: #fff;
            color: #000;
        }

        .content {
            padding: 0 10px;
        }

        .center {
            text-align: center;
        }

        .item {
            margin-bottom: 4px;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 2px 0;
            word-break: break-word;
        }

        .totales td {
            padding: 2px 0;
        }

        @media print {
            body {
                zoom: 1;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
<div class="content">

    <div class="center">
        <strong>Tienda PRUEBAS</strong><br>
        Av. Pruebas 123<br>
        RUC: 123456789<br>
        <strong>Nota de servicio</strong><br>
        N° {{ str_pad($orden->id, 6, '0', STR_PAD_LEFT) }}<br>
        <div class="line"></div>
    </div>

    <div class="item">Cliente: {{ $orden->customer->nombre ?? '--' }}</div>
    <div class="item">Fecha: {{ $orden->fecha->format('d/m/Y') }}</div>

    <div class="line"></div>

    <table>
        <thead>
        <tr>
            <th style="text-align:left;">Servicio</th>
            <th style="text-align:center;">Cant</th>
            <th style="text-align:right;">P. Unit.</th>
            <th style="text-align:right;">Subt.</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($servicios as $item)
            <tr>
                <td>{{ $item['nombre'] }}</td>
                <td style="text-align:center;">{{ $item['cantidad'] }}</td>
                <td style="text-align:right;">S/ {{ number_format($item['precio_unitario'], 2) }}</td>
                <td style="text-align:right;">S/ {{ number_format($item['subtotal'], 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <table class="totales">
        <tr>
            <td style="text-align:left;">Subtotal:</td>
            <td style="text-align:right;">S/ {{ number_format($orden->total, 2) }}</td>
        </tr>
        <tr>
            <td style="text-align:left;">Descuento:</td>
            <td style="text-align:right;">S/ {{ number_format($orden->descuento, 2) }}</td>
        </tr>
        <tr>
            <td style="text-align:left;"><strong>Total:</strong></td>
            <td style="text-align:right;"><strong>S/ {{ number_format($orden->total - $orden->descuento, 2) }}</strong></td>
        </tr>
        <!--<tr>
            <td style="text-align:left;">Pagado con:</td>
            <td style="text-align:right;">S/ {{ number_format($orden->pago_con ?? ($orden->total - $orden->descuento), 2) }}</td>
        </tr>
        <tr>
            <td style="text-align:left;">Vuelto:</td>
            <td style="text-align:right;">S/ {{ number_format($orden->vuelto ?? 0, 2) }}</td>
        </tr-->
    </table>

    <div class="line"></div>

    <div class="center" style="margin-top: 8px;">¡Gracias por su preferencia!</div>

</div>
</body>
</html>
