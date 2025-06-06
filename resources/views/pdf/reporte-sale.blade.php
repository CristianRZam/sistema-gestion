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
    <tr><td style="width: 50px !important;"><strong>Fecha:</strong></td><td>{{ $fecha }}</td></tr>
    <tr><td style="width: 50px !important;"><strong>Hora :</strong></td><td>{{ $hora }}</td></tr>
    <tr><td style="width: 50px !important;"><strong>Usuario:</strong></td><td>{{ $usuario }}</td></tr>
</table>

<table>
    <thead>
    <tr>
        @foreach($headings as $heading)
            <th>{{ $heading }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($rows as $row)
        <tr>
            <td>{{ $row['nro'] }}</td>
            <td>{{ $row['fecha'] }}</td>
            <td style="text-align: left !important;">{{ $row['cliente'] }}</td>
            <td style="text-align: left !important;">{{ $row['vendedor'] }}</td>
            <td>{{ $row['total'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
