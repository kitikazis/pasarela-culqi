@php
    $plan     = $transaction->metadata['plan'] ?? null;
    $planName = $plan ? config("plans.{$plan}.name") : '—';
    $credits  = $plan ? config("plans.{$plan}.credits") : null;
    $monto    = number_format($transaction->amount / 100, 2);
    $email    = optional($transaction->user)->email ?? $transaction->customer_email ?? '—';
    $nombre   = $transaction->customer_name ?? optional($transaction->user)->name ?? '—';
    $ref      = $transaction->charge_id ?? $transaction->order_number ?? '—';
@endphp
<div style="font-family:system-ui,Segoe UI,Arial,sans-serif; max-width:540px; margin:0 auto; color:#1a1f36;">
    <h2 style="margin:0 0 .75rem;">💰 Nuevo pago recibido</h2>
    <table cellpadding="7" style="border-collapse:collapse; font-size:14px; width:100%;">
        <tr><td style="color:#6b7280;">Plan</td><td><b>{{ $planName }}</b></td></tr>
        <tr><td style="color:#6b7280;">Monto</td><td><b>S/ {{ $monto }}</b> {{ $transaction->currency }}</td></tr>
        @if ($credits)
            <tr><td style="color:#6b7280;">Créditos</td><td>{{ $credits }} publicaciones</td></tr>
        @endif
        <tr><td style="color:#6b7280;">Usuario</td><td>{{ $email }}</td></tr>
        <tr><td style="color:#6b7280;">Nombre</td><td>{{ $nombre }}</td></tr>
        <tr><td style="color:#6b7280;">Método</td><td>{{ $transaction->payment_method }}</td></tr>
        <tr><td style="color:#6b7280;">Estado</td><td>{{ $transaction->status }}</td></tr>
        <tr><td style="color:#6b7280;">Referencia</td><td>{{ $ref }}</td></tr>
        <tr><td style="color:#6b7280;">Fecha</td><td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td></tr>
    </table>
    <p style="color:#9aa1ad; font-size:12px; margin-top:1.25rem;">
        Aviso automático de {{ config('app.name') }}.
    </p>
</div>
