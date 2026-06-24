<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Recibo de pago</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;background:#f6f7fb;padding:24px;color:#1f2937;">
  <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:8px;padding:20px;border:1px solid #e6e9ef;">
    <h2 style="margin:0 0 12px;color:#111827;">Gracias por tu compra</h2>
    <p style="margin:0 0 18px;color:#6b7280;">Adjuntamos el comprobante de tu compra en Anuncialo.pe</p>

    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#374151;"> 
      <tr>
        <td style="padding:8px 6px;border-top:1px solid #eef2f6;font-weight:600;width:40%;">Cliente</td>
        <td style="padding:8px 6px;border-top:1px solid #eef2f6;">{{ $transaction->customer_name }}</td>
      </tr>
      <tr>
        <td style="padding:8px 6px;border-top:1px solid #eef2f6;font-weight:600;">Nº de Orden</td>
        <td style="padding:8px 6px;border-top:1px solid #eef2f6;">{{ $transaction->order_number }}</td>
      </tr>
      <tr>
        <td style="padding:8px 6px;border-top:1px solid #eef2f6;font-weight:600;">Plan</td>
        <td style="padding:8px 6px;border-top:1px solid #eef2f6;">{{ $planDetails['name'] ?? '-' }}</td>
      </tr>
      <tr>
        <td style="padding:8px 6px;border-top:1px solid #eef2f6;font-weight:600;">Créditos</td>
        <td style="padding:8px 6px;border-top:1px solid #eef2f6;">{{ $planDetails['credits'] ?? '-' }}</td>
      </tr>
      <tr>
        <td style="padding:8px 6px;border-top:1px solid #eef2f6;font-weight:600;">Monto</td>
        <td style="padding:8px 6px;border-top:1px solid #eef2f6;">S/ {{ number_format($transaction->amount, 2) }}</td>
      </tr>
    </table>

    <p style="color:#6b7280;font-size:13px;margin-top:18px;">Si tienes dudas, responde este correo o visita nuestro centro de ayuda.</p>
    <div style="margin-top:20px;font-size:12px;color:#9ca3af;">© {{ date('Y') }} Anuncialo.pe</div>
  </div>
</body>
</html>
