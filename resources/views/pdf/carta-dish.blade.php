<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuestro Menú</title>
    <style>
        /* Reset y fondo */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            height: 100%;
            width: 100%;
            font-family: 'DejaVu Sans', sans-serif;
            background: #000;
            color: #fff;
        }

        .container {
            padding: 20px 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 28px;
            color: #f59e0b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .menu-section {
            margin-bottom: 30px;
        }

        .menu-section h2 {
            font-size: 20px;
            color: #f59e0b;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .menu-item {
            width: 100%;
            display: table;
            table-layout: fixed;
            padding: 6px 0;
            font-size: 14px;
            border-bottom: 1px dashed #555;
        }

        .menu-item-row {
            display: table-row;
        }

        .menu-name,
        .menu-fill,
        .menu-price {
            display: table-cell;
            vertical-align: top;
        }

        .menu-name {
            width: 30%;
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
        }

        .menu-fill {
            width: 40%;
            text-align: center;
            color: #999;
        }

        .menu-price {
            width: 30%;
            text-align: right;
            white-space: nowrap;
            font-weight: bold;
            color: #f59e0b;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Nuestro Menú</h1>
    </div>

    @foreach($categoriasConPlatillos as $categoria => $platillos)
        <div class="menu-section">
            <h2>{{ $categoria }}</h2>

            @foreach($platillos as $p)
                <div class="menu-item">
                    <div class="menu-item-row">
                        <div class="menu-name">{{ $p['nombre'] }}</div>
                        <div class="menu-price">S/ {{ number_format($p['precio_promocion'] ?? $p['precio'], 2) }}</div>
                    </div>
                </div>
            @endforeach

        </div>
    @endforeach

</div>
</body>
</html>
