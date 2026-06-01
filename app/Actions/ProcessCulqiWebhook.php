<?php

namespace App\Actions;

use App\Events\PaymentConfirmed;
use App\Models\Transaction;
use App\Models\WebhookEvent;
use App\Services\CulqiService;

/**
 * Procesa un evento de webhook de Culqi de forma segura.
 *
 * Garantías:
 *  - IDEMPOTENCIA: cada event_id se procesa una sola vez (Culqi reintenta).
 *  - ANTI-SPOOFING: no confía en el payload; re-consulta el recurso a Culqi
 *    con la llave secreta para confirmar el estado real.
 *  - AUDITORÍA: guarda cada evento en webhook_events.
 *  - Dispara PaymentConfirmed cuando un pago queda confirmado (seam de negocio).
 */
class ProcessCulqiWebhook
{
    public function __construct(private CulqiService $culqi) {}

    /** @return string  duplicate | processed | ignored */
    public function handle(array $payload): string
    {
        $eventId    = $payload['id']         ?? null;
        $type       = $payload['type']       ?? 'unknown';
        $resourceId = $payload['data']['id'] ?? null;

        // 1) Idempotencia — no reprocesar el mismo evento.
        if ($eventId && WebhookEvent::where('event_id', $eventId)->exists()) {
            return 'duplicate';
        }

        // 2) Registrar el evento (auditoría) con payload saneado.
        $record = WebhookEvent::create([
            'event_id'    => $eventId,
            'type'        => $type,
            'resource_id' => $resourceId,
            'status'      => 'received',
            'payload'     => $this->sanitize($payload),
        ]);

        // 3) Verificar el recurso REAL en Culqi (anti-spoofing) y resolver.
        $resolved = $this->verifyAndResolve($resourceId);

        if ($resolved === null) {
            $record->update(['status' => 'ignored', 'processed_at' => now()]);
            return 'ignored';
        }

        [$transaction, $paid] = $resolved;

        if ($transaction) {
            $transaction->update([
                'status' => $paid ? 'paid' : $transaction->status,
            ]);

            if ($paid) {
                // Punto de extensión para la lógica de negocio (#2).
                event(new PaymentConfirmed($transaction->fresh()));
            }
        }

        $record->update(['status' => 'processed', 'processed_at' => now()]);

        return 'processed';
    }

    /**
     * Confirma el recurso directamente con Culqi y localiza su transacción.
     *
     * @return array{0: ?Transaction, 1: bool}|null  [transacción, pagado] o null si no aplica
     */
    private function verifyAndResolve(?string $resourceId): ?array
    {
        if (! $resourceId) {
            return null;
        }

        // Cargo (tarjeta / Yape)
        if (str_starts_with($resourceId, 'chr_')) {
            $result = $this->culqi->getCharge($resourceId);
            if (! $result['success']) {
                return null;
            }
            $charge = $result['data'];
            $paid   = ($charge->outcome->type ?? null) === 'venta_exitosa';

            return [Transaction::where('charge_id', $charge->id)->first(), $paid];
        }

        // Orden (PagoEfectivo / billeteras / Cuotéalo)
        if (str_starts_with($resourceId, 'ord_')) {
            $result = $this->culqi->getOrder($resourceId);
            if (! $result['success']) {
                return null;
            }
            $order = $result['data'];
            $paid  = ($order->state ?? null) === 'paid';

            return [Transaction::where('metadata->order_id', $resourceId)->first(), $paid];
        }

        return null;
    }

    /** Nunca persistir datos sensibles, aunque Culqi normalmente no los envía. */
    private function sanitize(array $payload): array
    {
        if (isset($payload['data']['source']['card_number'])) {
            $payload['data']['source']['card_number'] = '***';
        }
        if (isset($payload['data']['source']['cvv'])) {
            $payload['data']['source']['cvv'] = '***';
        }

        return $payload;
    }
}
