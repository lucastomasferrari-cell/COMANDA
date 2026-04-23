<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte anti-fraude</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; color: #1e272e; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; background: #fafafa; }
        h1 { color: #2c3e50; }
        .card { background: #fff; padding: 12px 16px; margin: 8px 0; border-radius: 8px;
                border-left: 4px solid #95a5a6; }
        .card.alert { border-left-color: #e74c3c; background: #fff5f5; }
        .card strong { font-size: 1.1em; }
        .muted { color: #7f8c8d; font-size: 0.9em; }
    </style>
</head>
<body>
<div class="container">
    <h1>Reporte anti-fraude — {{ $summary['date'] ?? 'hoy' }}</h1>
    <p class="muted">Período: {{ $summary['period']['from'] ?? '' }} → {{ $summary['period']['to'] ?? '' }}</p>

    <div class="card">
        <strong>Ventas totales:</strong> {{ number_format($summary['cards']['sales_total'] ?? 0, 2) }}
    </div>

    <div class="card {{ ($summary['cards']['voids']['alert'] ?? false) ? 'alert' : '' }}">
        <strong>Voids:</strong>
        {{ $summary['cards']['voids']['count'] ?? 0 }} items
        ({{ number_format($summary['cards']['voids']['amount'] ?? 0, 2) }} —
        {{ $summary['cards']['voids']['ratio_percent'] ?? 0 }}% de ventas)
        @if($summary['cards']['voids']['alert'] ?? false)
            <div class="muted">Supera el umbral del 3%.</div>
        @endif
    </div>

    <div class="card {{ ($summary['cards']['discounts']['alert'] ?? false) ? 'alert' : '' }}">
        <strong>Descuentos:</strong>
        {{ $summary['cards']['discounts']['count'] ?? 0 }} aplicados
        ({{ number_format($summary['cards']['discounts']['amount'] ?? 0, 2) }} —
        {{ $summary['cards']['discounts']['ratio_percent'] ?? 0 }}% de ventas)
        @if($summary['cards']['discounts']['alert'] ?? false)
            <div class="muted">Supera el umbral del 10%.</div>
        @endif
    </div>

    <div class="card {{ ($summary['cards']['open_items']['alert'] ?? false) ? 'alert' : '' }}">
        <strong>Ítems sueltos:</strong>
        {{ $summary['cards']['open_items']['count'] ?? 0 }} items
        ({{ number_format($summary['cards']['open_items']['amount'] ?? 0, 2) }})
    </div>

    <div class="card">
        <strong>Aprobaciones pendientes:</strong> {{ $summary['cards']['pending_approvals'] ?? 0 }}
    </div>

    <div class="card">
        <strong>Cambios de forma de pago post-cobro:</strong> {{ $summary['cards']['payment_method_changes'] ?? 0 }}
    </div>

    <div class="card">
        <strong>Órdenes reabiertas:</strong> {{ $summary['cards']['orders_reopened'] ?? 0 }}
    </div>

    @if(!empty($summary['top_voiders']))
        <h3>Top 3 usuarios con más anulaciones</h3>
        <ul>
            @foreach($summary['top_voiders'] as $voider)
                <li>{{ $voider['name'] }} — {{ $voider['count'] }} voids</li>
            @endforeach
        </ul>
    @endif

    <p class="muted" style="margin-top: 20px;">
        Reporte generado automáticamente por COMANDA. Si creés que hay
        algo raro, revisalo en Admin → Reportes → Anti-fraude.
    </p>
</div>
</body>
</html>
