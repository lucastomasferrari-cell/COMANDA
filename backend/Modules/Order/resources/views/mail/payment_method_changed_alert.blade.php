<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Cambio de forma de pago</title></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, sans-serif; color: #1e272e;">
<div style="max-width: 540px; margin: 20px auto; padding: 20px; background: #fff5f5; border-left: 4px solid #e74c3c;">
    <h2 style="color: #c0392b;">Alerta: cambio de forma de pago post-cobro</h2>
    <p>Se modificó la forma de pago de una orden ya cobrada.</p>
    <table style="width: 100%; border-collapse: collapse; margin-top: 12px;">
        <tr><td style="padding: 4px 0;"><strong>Orden</strong></td><td>#{{ $order->reference_no }}</td></tr>
        <tr><td style="padding: 4px 0;"><strong>Pago ID</strong></td><td>{{ $paymentId }}</td></tr>
        <tr><td style="padding: 4px 0;"><strong>Método anterior</strong></td><td>{{ $oldMethod }}</td></tr>
        <tr><td style="padding: 4px 0;"><strong>Método nuevo</strong></td><td>{{ $newMethod }}</td></tr>
        <tr><td style="padding: 4px 0;"><strong>Aprobador</strong></td><td>#{{ $approverUserId }}</td></tr>
    </table>
    <h3 style="margin-top: 20px;">Motivo</h3>
    <p style="background: #fff; padding: 10px; border-radius: 6px; border: 1px solid #ffcccc;">
        {{ $reason }}
    </p>
    <p style="color: #7f8c8d; font-size: 0.85em; margin-top: 20px;">
        El cambio quedó registrado en audit_logs con is_fiscal=true (retención 10 años).
    </p>
</div>
</body>
</html>
