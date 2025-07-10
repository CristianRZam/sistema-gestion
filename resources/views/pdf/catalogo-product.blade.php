<!DOCTYPE html>
<html>
<head>
    <style>
        @page :first {
            margin: 0;
        }

        @page {
            margin: 30px 50px 20px 50px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
        }

        .contenido-con-pie {
            padding-bottom: 50px;
        }

        .portada {
            position: relative;
            width: 100vw;
            height: 100vh;
            page-break-after: always;
            padding: 0;
            margin: 0;
            text-align: center;
        }

        .portada img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .encabezado {
            text-align: center;
        }

        .encabezado img {
            width: 100%;
            max-height: 100px;
        }

        .espacio-despues-encabezado {
            height: 5px;
        }

        .pie {
            position: fixed;
            bottom: 0px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
        }

        .pie img {
            height: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: normal;
        }

        .pie {
            position: fixed;
            bottom: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            color: #444;
        }

        .pie-linea-superior {
            height: 2px;
            background: linear-gradient(to right, #007ACC, #00C6FF);
            margin-bottom: 8px;
            border-radius: 4px;
        }

        .pie-contenido {
            text-align: center;
            background-color: #f9f9f9;
            padding: 10px 15px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .pie-datos {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin-bottom: 4px;
            color: #333;
            font-weight: 500;
        }

        .pie-datos span::after {
            content: "•";
            margin-left: 10px;
            color: #999;
        }

        .pie-datos span:last-child::after {
            content: "";
            margin: 0;
        }

        .pie-copyright {
            font-size: 9px;
            color: #777;
            font-style: italic;
        }
    </style>
</head>
<body>

{{-- ✅ Página de portada con imagen de encabezado a pantalla completa --}}
<div class="portada">
    @if (!empty($portadaImagen))
        <img src="{{ $portadaImagen }}" alt="Portada catálogo" />
    @endif
</div>

{{-- ✅ Contenedor con padding solo para páginas de contenido --}}
<div class="contenido-con-pie">

    {{-- ✅ Encabezado solo en la primera página de contenido --}}
    <div class="encabezado">
        @if (!empty($encabezadoImagen))
            <img src="{{ $encabezadoImagen }}" alt="Encabezado catálogo" />
        @endif
    </div>

    <div class="espacio-despues-encabezado"></div>

    {{-- ✅ Contenido agrupado por categoría --}}
    @foreach ($productosAgrupados as $categoria => $productos)
        <h2>{{ $categoria }}</h2>
        <table>
            <thead>
            <tr>
                <th style="text-align: center;">CÓDIGO</th>
                <th style="text-align: center;">DESCRIPCIÓN</th>
                <th style="text-align: center; width: 80px;">PRECIO (S/)</th>
                <th style="text-align: center; width: 100px;">IMAGEN</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($productos as $producto)
                <tr>
                    <td style="text-align: center;">{{ $producto['modelo'] }}</td>
                    <td>
                        <strong class="text-sm text-gray-800">{{ $producto['nombre'] }}</strong><br>
                        <span class="text-xs text-gray-600">{{ $producto['descripcion'] }}</span>
                    </td>
                    <td style="text-align: center;">
                        @if(!empty($producto['precio_promocion']))
                            <span style="text-decoration: line-through; font-size: 10px; color: #b8b8b8">S/ {{ $producto['precio'] }}</span><br>
                            <span>S/ {{ $producto['precio_promocion'] }}</span>
                        @else
                            S/ {{ $producto['precio'] }}
                        @endif
                    </td>

                    <td style="text-align: center;">
                        @if (!empty($producto['imagen_url']))
                            <img src="{{ $producto['imagen_url'] }}" alt="Imagen" width="50">
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endforeach

</div>

{{-- Pie de página moderno y elegante --}}
<div class="pie">
    <div class="pie-linea-superior"></div>
    <div class="pie-contenido">
        <div class="pie-datos">
            <span>{{ $direccionEmpresa }}</span>
            <span>Tel: {{ $telefonoEmpresa }}</span>
            <span>{{ $correoEmpresa }}</span>
            <span>{{ $webEmpresa }}</span>
        </div>
        <div class="pie-copyright">
            © {{ date('Y') }} {{ $nombreEmpresa }} · Todos los derechos reservados.
        </div>
    </div>
</div>


</body>
</html>
