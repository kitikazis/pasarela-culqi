<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use App\Mail\PaymentReceivedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Al confirmarse un pago, envía un correo de aviso a los administradores
 * (config('app.admins') → ADMIN_EMAILS). Nunca rompe el flujo de pago si el
 * correo falla.
 */
class NotifyAdminOnPayment
{
    public function handle(PaymentConfirmed $event): void
    {
        $admins = (array) config('app.admins', []);
        if (empty($admins)) {
            return; // Sin destinatarios (ADMIN_EMAILS vacío): no se envía.
        }

        try {
            Mail::to($admins)->send(new PaymentReceivedMail($event->transaction));
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar el correo de aviso de pago', [
                'transaction_id' => $event->transaction->id,
                'error'          => $e->getMessage(),
            ]);
        }
    }
}
