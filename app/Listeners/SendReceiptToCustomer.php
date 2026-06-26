<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use App\Mail\PaymentReceiptMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReceiptToCustomer
{
    public function handle(PaymentConfirmed $event): void
    {
        $transaction = $event->transaction;

        $planCode = null;
        if (is_array($transaction->metadata) && isset($transaction->metadata['plan'])) {
            $planCode = $transaction->metadata['plan'];
        } elseif (is_object($transaction->metadata) && isset($transaction->metadata->plan)) {
            $planCode = $transaction->metadata->plan;
        }

        $planDetails = $planCode ? (config('plans')[$planCode] ?? null) : null;

        // Usa el correo del usuario logueado; si no hay, cae al customer_email de la transacción.
        $email = optional($transaction->user)->email ?: $transaction->customer_email;

        try {
            if ($email) {
                Mail::to($email)
                    ->send(new PaymentReceiptMail($transaction, $planDetails));
            }
        } catch (\Throwable $e) {
            Log::error('SendReceiptToCustomer failed: ' . $e->getMessage(), ['transaction_id' => $transaction->id ?? null]);
        }
    }
}
