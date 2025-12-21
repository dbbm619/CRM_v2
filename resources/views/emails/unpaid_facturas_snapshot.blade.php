{{--
    Vista de correo para el "Snapshot" diario de facturas no pagadas.
    - Muestra: número de factura, nombre del cliente, fecha de la factura y monto.
    - Incluye totales en el encabezado y un resumen al final.
    - No incluye archivos adjuntos; todo el detalle aparece en el cuerpo del correo.
--}}
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <h2>Snapshot de facturas no pagadas - {{ $date }}</h2>

    {{-- Totales generales del snapshot --}}
    <p><strong>Total facturas:</strong> {{ $totalCount }}</p>
    <p><strong>Monto total:</strong> {{ number_format($totalAmount, 2) }}</p>

    {{-- Tabla con el detalle de cada factura pendiente --}}
    <table border="1" cellpadding="4" cellspacing="0">
        <thead>
            <tr>
                <th>Número factura</th>
                <th>Cliente</th>
                <th>Fecha factura</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            {{-- Recorre cada registro del snapshot y lo muestra en una fila --}}
            @foreach ($snapshots as $s)
                <tr>
                    <td>{{ $s->numero_factura }}</td>
                    <td>{{ $s->cliente_nombre }}</td>
                    <td>{{ $s->factura_fecha ? \Carbon\Carbon::parse($s->factura_fecha)->format('d/m/Y') : '-' }}</td>
                    <td style="text-align: right;">{{ number_format($s->monto, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 12px;"><strong>Resumen:</strong> {{ $totalCount }} factura(s) — Monto total: {{ number_format($totalAmount, 2) }}</p>


</body>
</html>
