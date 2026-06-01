<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use Illuminate\Support\Facades\Log;

/**
 * Lógica de negocio (#2): al confirmarse un pago, destaca el anuncio asociado
 * durante los días del plan comprado.
 */
class FeatureAdOnPayment
{
    public function handle(PaymentConfirmed $event): void
    {
        $transaction = $event->transaction;

        // El pago debe estar ligado a un anuncio.
        $ad = $transaction->ad;
        if (! $ad) {
            return;
        }

        // Los días vienen del plan comprado (config/plans.php).
        $planKey = $transaction->metadata['plan'] ?? null;
        $days    = $planKey ? (int) config("plans.{$planKey}.days") : 0;
        if ($days <= 0) {
            return;
        }

        $ad->feature($days);

        Log::info('Anuncio destacado por pago', [
            'ad_id'          => $ad->id,
            'plan'           => $planKey,
            'dias'           => $days,
            'featured_until' => $ad->featured_until?->toIso8601String(),
            'transaction_id' => $transaction->id,
        ]);
    }
}
