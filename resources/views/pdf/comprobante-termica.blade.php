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

        thead tr {
            border-bottom: 1px solid #000;
        }

        th, td {
            padding: 2px 0;
            word-break: break-word;
        }

        th {
            text-align: left;
        }

        td {
            vertical-align: top;
        }

        td .cantidad,
        td .precio {
            display: block;
            font-weight: bold;
        }

        .cantidad {
            text-align: center;
        }

        .precio {
            text-align: right;
        }

        .totales {
            margin-top: 8px;
        }

        .totales .item {
            display: flex;
            justify-content: space-between;
        }

        /* Vista previa mejorada */
        @media screen {
            body {
                zoom: 1.3;
                padding: 10px 0;
            }
        }

        /* Estilo para impresión */
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
        <strong>Nota de venta</strong><br>
        N° 001-000123<br>
        <div class="line"></div>
    </div>


    <div class="item">Cliente: {{ $venta->customer->nombre ?? '--' }}</div>
    <div class="item">Dirección: {{ $venta->customer->direccion ?? '--' }}</div>
    <div class="item">Fecha: {{ $venta->fecha_venta->format('d/m/Y') }}</div>

    <div class="line"></div>

    <table>
        <thead>
        <tr>
            <th>Producto</th>
            <th style="text-align: center;">Cant</th>
            <th style="text-align: right;">P. Unit.</th>
            <th style="text-align: right;">Subtotal</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($productos as $producto)
            <tr>
                <td>{{ $producto['nombre'] }}</td>
                <td class="cantidad" style="text-align:center;">{{ $producto['cantidad'] }}</td>
                <td class="precio" style="text-align:right;">S/ {{ number_format($producto['precio_unitario'], 2) }}</td>
                <td class="precio" style="text-align:right;">S/ {{ number_format($producto['subtotal'], 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <!-- Totales alineados con la columna de precios -->
    <table style="width: 100%; border-collapse: collapse;">
        <tbody>
        <tr>
            <td style="text-align: left; padding: 2px 0;">Subtotal:</td>
            <td style="text-align: right; padding: 2px 0; width: 40%;">S/ {{ number_format($venta->total, 2) }}</td>
        </tr>
        <tr>
            <td style="text-align: left; padding: 2px 0;">Descuento:</td>
            <td style="text-align: right; padding: 2px 0;">S/ {{ number_format($venta->descuento, 2) }}</td>
        </tr>
        <tr>
            <td style="text-align: left; padding: 2px 0;"><strong>Total:</strong></td>
            <td style="text-align: right; padding: 2px 0;"><strong>S/ {{ number_format($venta->total - $venta->descuento, 2) }}</strong></td>
        </tr>
        </tbody>
    </table>

    <div class="line"></div>

    <!-- Totales alineados con la columna de precios -->
    <table style="width: 100%; border-collapse: collapse;">
        <tbody>
        <tr>
            <td style="text-align: left; padding: 2px 0;">Pagado con:</td>
            <td style="text-align: right; padding: 2px 0;">S/ {{ number_format($venta->pago_con, 2) }}</td>
        </tr>
        <tr>
            <td style="text-align: left; padding: 2px 0;">Vuelto:</td>
            <td style="text-align: right; padding: 2px 0;">S/ {{ number_format($venta->vuelto, 2) }}</td>
        </tr>
        </tbody>
    </table>

    <div class="center" style="margin-top: 8px;">¡Gracias por su compra!</div>

</div>
</body>
</html>
