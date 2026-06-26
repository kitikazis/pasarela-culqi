<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Recibo de pago — Anuncialo.pe</title>
</head>
<body style="margin:0;padding:0;background:#f4f5f7;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">

  {{-- Preheader: texto de vista previa en la bandeja (oculto en el cuerpo) --}}
  <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#f4f5f7;">
    Comprobante de tu compra en Anuncialo.pe — {{ $planDetails['name'] ?? 'Plan' }} por S/ {{ number_format($transaction->amount_in_soles, 2) }}.
  </div>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f5f7;">
    <tr>
      <td align="center" style="padding:24px 12px;">

        <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 6px 24px rgba(17,24,39,.06);font-family:Arial,Helvetica,sans-serif;">

          {{-- Barra de acento de marca --}}
          <tr><td style="height:6px;background:#FF7A00;line-height:6px;font-size:0;">&nbsp;</td></tr>

          {{-- Logo --}}
          <tr>
            <td align="center" style="padding:28px 24px 4px;">
              <img src="{{ config('app.url') }}/assets/logo.png" alt="Anuncialo.pe" height="40" style="height:40px;width:auto;display:block;border:0;outline:none;text-decoration:none;">
            </td>
          </tr>

          {{-- Sello de pago exitoso --}}
          <tr>
            <td align="center" style="padding:14px 24px 0;">
              <div style="width:56px;height:56px;line-height:56px;border-radius:50%;background:#ECFDF3;color:#10B981;font-size:30px;font-weight:bold;text-align:center;">&#10003;</div>
            </td>
          </tr>

          {{-- Título --}}
          <tr>
            <td align="center" style="padding:16px 32px 4px;">
              <h1 style="margin:0;font-size:22px;line-height:1.3;color:#111827;font-weight:bold;">¡Gracias por tu compra!</h1>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding:4px 40px 8px;">
              <p style="margin:0;font-size:14px;color:#6B7280;line-height:1.55;">Tu pago se procesó correctamente. Este es el comprobante de tu compra en Anuncialo.pe.</p>
            </td>
          </tr>

          {{-- Monto destacado --}}
          <tr>
            <td style="padding:16px 32px 6px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FFF4E6;border-radius:12px;">
                <tr>
                  <td align="center" style="padding:18px;">
                    <div style="font-size:12px;color:#9A6300;letter-spacing:.5px;text-transform:uppercase;font-weight:bold;">Monto pagado</div>
                    <div style="font-size:32px;color:#E66900;font-weight:bold;margin-top:6px;line-height:1;">S/ {{ number_format($transaction->amount_in_soles, 2) }}</div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- Detalle --}}
          <tr>
            <td style="padding:14px 32px 4px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size:14px;color:#374151;">
                <tr>
                  <td style="padding:12px 0;border-bottom:1px solid #EEF1F5;color:#6B7280;">Cliente</td>
                  <td align="right" style="padding:12px 0;border-bottom:1px solid #EEF1F5;color:#111827;font-weight:bold;">{{ $transaction->customer_name ?: '—' }}</td>
                </tr>
                <tr>
                  <td style="padding:12px 0;border-bottom:1px solid #EEF1F5;color:#6B7280;">Plan</td>
                  <td align="right" style="padding:12px 0;border-bottom:1px solid #EEF1F5;color:#111827;font-weight:bold;">{{ $planDetails['name'] ?? '—' }}</td>
                </tr>
                <tr>
                  <td style="padding:12px 0;border-bottom:1px solid #EEF1F5;color:#6B7280;">Créditos</td>
                  <td align="right" style="padding:12px 0;border-bottom:1px solid #EEF1F5;color:#111827;font-weight:bold;">{{ isset($planDetails['credits']) ? $planDetails['credits'] . ' publicaciones' : '—' }}</td>
                </tr>
                <tr>
                  <td style="padding:12px 0;border-bottom:1px solid #EEF1F5;color:#6B7280;">Nº de Orden</td>
                  <td align="right" style="padding:12px 0;border-bottom:1px solid #EEF1F5;color:#111827;font-family:'Courier New',monospace;font-size:13px;">{{ $transaction->order_number ?? $transaction->charge_id }}</td>
                </tr>
                <tr>
                  <td style="padding:12px 0;color:#6B7280;">Fecha</td>
                  <td align="right" style="padding:12px 0;color:#111827;">{{ optional($transaction->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- Botón --}}
          <tr>
            <td align="center" style="padding:18px 32px 6px;">
              <a href="{{ config('app.url') }}" style="display:inline-block;background:#FF7A00;color:#ffffff;text-decoration:none;font-weight:bold;font-size:14px;padding:13px 30px;border-radius:10px;">Ir a Anuncialo.pe</a>
            </td>
          </tr>

          {{-- Ayuda --}}
          <tr>
            <td align="center" style="padding:6px 40px 22px;">
              <p style="margin:0;font-size:12px;color:#9CA3AF;line-height:1.6;">¿Tienes alguna duda? Responde este correo y con gusto te ayudamos.</p>
            </td>
          </tr>

          {{-- Pie --}}
          <tr>
            <td align="center" style="background:#FAFAFB;border-top:1px solid #EEF1F5;padding:18px 24px;">
              <p style="margin:0;font-size:12px;color:#9CA3AF;line-height:1.5;">© {{ date('Y') }} Anuncialo.pe · Todos los derechos reservados</p>
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>
</html>
