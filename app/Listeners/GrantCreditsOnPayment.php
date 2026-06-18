<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use Illuminate\Support\Facades\Log;

/**
 * Al confirmarse un pago, suma al usuario los créditos de publicación del plan
 * comprado (config/plans.php). El usuario usa esos créditos para publicar.
 */
class GrantCreditsOnPayment
{
    public function handle(PaymentConfirmed $event): void
    {
        $transaction = $event->transaction;

        // El pago debe estar ligado al usuario que compró.
        $user = $transaction->user;
        if (! $user) {
            Log::warning('Pago confirmado sin usuario: no se pudieron otorgar créditos', [
                'transaction_id' => $transaction->id,
            ]);
            return;
        }

        $planKey = $transaction->metadata['plan'] ?? null;
        $credits = $planKey ? (int) config("plans.{$planKey}.credits") : 0;
        if ($credits <= 0) {
            return;
        }

        $user->addCredits($credits);

        Log::info('Créditos de publicación otorgados por pago', [
            'user_id'        => $user->id,
            'plan'           => $planKey,
            'credits'        => $credits,
            'saldo'          => $user->publish_credits,
            'transaction_id' => $transaction->id,
        ]);
    }
}
