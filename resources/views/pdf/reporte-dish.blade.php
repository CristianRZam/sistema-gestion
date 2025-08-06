<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 10px; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #999; padding: 6px; text-align: center; }
        th { background-color: #00cbe2; color: white; }

        .meta {
            font-size: 10px;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .meta td {
            border: none;
            padding: 0 2px;
            line-height: 1;
            vertical-align: top;
            text-align: left;
        }

        .meta td:first-child {
            font-weight: bold;
            white-space: nowrap;
            padding-right: 2px;
        }

        .meta td:last-child {
            padding-left: 2px;
        }
    </style>
</head>
<body>

<h2>{{ $title }}</h2>

<table class="meta">
    <tr><td style="width: 50px;"><strong>Fecha:</strong></td><td>{{ $fecha }}</td></tr>
    <tr><td style="width: 50px;"><strong>Hora :</strong></td><td>{{ $hora }}</td></tr>
    <tr><td style="width: 50px;"><strong>Usuario:</strong></td><td>{{ $usuario }}</td></tr>
</table>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Precio</th>
        <th>Categoría</th>
        <th>Estado</th>
    </tr>
    </thead>
    <tbody>
    @foreach($rows as $row)
        <tr>
            <td>{{ $row['nro'] }}</td>
            <td style="text-align: left;">{{ $row['nombre'] }}</td>
            <td style="text-align: left;">{{ $row['descripcion'] }}</td>
            <td>S/ {{ number_format($row['precio'], 2) }}</td>
            <td>{{ $row['categoria'] }}</td>
            <td>
                @if ($row['estado'])
                    Disponible
                @else
                    No disponible
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
