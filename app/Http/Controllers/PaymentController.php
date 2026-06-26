<?php

namespace App\Http\Controllers;

use App\Actions\ProcessCulqiWebhook;
use App\Actions\RecordTransaction;
use App\Http\Requests\ChargeRequest;
use App\Http\Requests\ConfirmOrderRequest;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\RefundRequest;
use App\Http\Requests\SaveCardRequest;
use App\Http\Requests\YapeRequest;
use App\Models\Transaction;
use App\Services\CulqiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private CulqiService $culqi,
        private RecordTransaction $record,
    ) {}

    // ─────────────────────────────────────────────────────────────
    //  GET /pago  — Vista de checkout (Culqi Checkout v4)
    // ─────────────────────────────────────────────────────────────
    public function showCheckout(): View
    {
        // La PUBLIC_KEY se inyecta en la vista desde config('culqi.public_key').
        return view('payment.checkout');
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /pago/cargo  — Cargo con tarjeta
    // ─────────────────────────────────────────────────────────────
    public function charge(ChargeRequest $request): JsonResponse
    {
        $data = $request->validated();

        // El precio se resuelve en el backend desde el plan (no se confía del cliente).
        $amount      = $this->resolveAmount($data);
        $description = $this->resolveDescription($data);

        // Nombre del comprador (para Culqi y para la BD).
        $customerName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')) ?: null;

        $result = $this->culqi->createCharge([
            'token'         => $data['token'],
            'amount'        => $amount,
            'currency_code' => $data['currency_code'] ?? 'PEN',
            'email'         => $data['email'],
            'description'   => $description,
            'first_name'    => $data['first_name'] ?? null,
            'last_name'     => $data['last_name'] ?? null,
        ]);

        if (! $result['success']) {
            // Auditoría del intento fallido (sin datos sensibles)
            $this->record->handle([
                'publicacion_id'      => $data['publicacion_id'] ?? null,
                'payment_method'      => 'card',
                'amount'              => $amount,
                'currency'            => $data['currency_code'] ?? 'PEN',
                'status'              => 'failed',
                'culqi_response_code' => $result['code'] ?? null,
                'customer_email'      => $data['email'],
                'customer_name'       => $customerName,
                'description'         => $description,
                'metadata'            => ['plan' => $data['plan'] ?? null],
            ]);

            return response()->json([
                'success' => false,
                'message' => $result['user_message'],
            ], 422);
        }

        $charge = $result['data'];

        $transaction = $this->record->handle([
            'publicacion_id'      => $data['publicacion_id'] ?? null,
            'charge_id'           => $charge->id,
            'payment_method'      => 'card',
            'amount'              => $charge->amount,
            'currency'            => $charge->currency_code ?? 'PEN',
            'status'              => 'paid',
            'culqi_response_code' => $charge->outcome->code ?? null,
            'customer_email'      => $charge->email ?? $data['email'],
            'customer_name'       => $customerName,
            'card_last4'          => $charge->source->last_four ?? null,
            'card_brand'          => $charge->source->iin->card_brand ?? null,
            'description'         => $description,
            'metadata'            => [
                'outcome_type' => $charge->outcome->type ?? null,
                'plan'         => $data['plan'] ?? null,
            ],
        ]);

        // Cobro confirmado en línea (tarjeta) → destacar el anuncio si aplica.
        if ($transaction) {
            event(new \App\Events\PaymentConfirmed($transaction->fresh()));
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Pago procesado exitosamente.',
            'charge_id'  => $charge->id,
            'order_id'   => $transaction?->id,
            'amount'     => round($charge->amount / 100, 2),
            'currency'   => $charge->currency_code ?? 'PEN',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /pago/devolucion  — Devolución (Refund) — solo admin
    // ─────────────────────────────────────────────────────────────
    public function refund(RefundRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->culqi->createRefund($data);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['user_message'],
            ], 422);
        }

        Transaction::where('charge_id', $data['charge_id'])
            ->update(['status' => 'refunded']);

        return response()->json([
            'success'   => true,
            'message'   => 'Devolución procesada exitosamente.',
            'refund_id' => $result['data']->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /pago/guardar-tarjeta  — Cliente + tarjeta (one-click)
    // ─────────────────────────────────────────────────────────────
    public function saveCard(SaveCardRequest $request): JsonResponse
    {
        $data = $request->validated();

        $customer = $this->culqi->createCustomer($data);
        if (! $customer['success']) {
            return response()->json(['success' => false, 'message' => $customer['user_message']], 422);
        }

        $card = $this->culqi->createCard([
            'customer_id' => $customer['data']->id,
            'token_id'    => $data['token'],
        ]);
        if (! $card['success']) {
            return response()->json(['success' => false, 'message' => $card['user_message']], 422);
        }

        // customer_id y card_id son referencias no sensibles para cobros futuros.
        return response()->json([
            'success'     => true,
            'message'     => 'Tarjeta guardada exitosamente.',
            'customer_id' => $customer['data']->id,
            'card_id'     => $card['data']->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /api/payment/yape  — Pago con Yape (solo testing)
    // ─────────────────────────────────────────────────────────────
    public function yape(YapeRequest $request): JsonResponse
    {
        $data = $request->validated();

        // El precio se resuelve en el backend desde el plan (no se confía del cliente).
        $amount       = $this->resolveAmount($data);
        $description  = $this->resolveDescription($data);
        $customerName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')) ?: null;

        $result = $this->culqi->chargeYape(
            $data['phone_number'],
            $data['otp'],
            $amount,
            $data['email'],
            $description,
        );

        if (! $result['success']) {
            $this->record->handle([
                'publicacion_id' => $data['publicacion_id'] ?? null,
                'payment_method' => 'yape',
                'amount'         => $amount,
                'currency'       => 'PEN',
                'status'         => 'failed',
                'customer_email' => $data['email'],
                'customer_name'  => $customerName,
                'description'    => $description,
                'metadata'       => ['failed_step' => $result['failed_step'] ?? null, 'plan' => $data['plan'] ?? null],
            ]);

            return response()->json([
                'success'     => false,
                'message'     => $result['user_message'],
                'failed_step' => $result['failed_step'] ?? null,
            ], 422);
        }

        $charge = $result['data'];

        $transaction = $this->record->handle([
            'publicacion_id'      => $data['publicacion_id'] ?? null,
            'charge_id'           => $charge->id,
            'payment_method'      => 'yape',
            'amount'              => $charge->amount,
            'currency'            => 'PEN',
            'status'              => 'paid',
            'culqi_response_code' => $charge->outcome->code ?? null,
            'customer_email'      => $charge->email ?? $data['email'],
            'customer_name'       => $customerName,
            'description'         => $description,
            'metadata'            => ['outcome_type' => $charge->outcome->type ?? null, 'plan' => $data['plan'] ?? null],
        ]);

        if ($transaction) {
            event(new \App\Events\PaymentConfirmed($transaction->fresh()));
        }

        return response()->json([
            'success'   => true,
            'message'   => 'Pago con Yape procesado exitosamente.',
            'charge_id' => $charge->id,
            'order_id'  => $transaction?->id,
            'amount'    => round($charge->amount / 100, 2),
            'currency'  => 'PEN',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /pago/orden  — Crea una Orden (PagoEfectivo / Cuotéalo)
    // ─────────────────────────────────────────────────────────────
    public function createOrder(CreateOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        $amount      = $this->resolveAmount($data);
        $description = $this->resolveDescription($data);

        $result = $this->culqi->createOrder([
            'amount'         => $amount,
            'currency_code'  => 'PEN',
            'description'    => $description,
            'order_number'   => 'ord-' . now()->format('YmdHis') . '-' . random_int(100, 999),
            'expiration_date' => time() + 60 * 60 * 24, // 24 horas
            'client_details' => [
                'first_name'   => $data['first_name'],
                'last_name'    => $data['last_name'],
                'email'        => $data['email'],
                'phone_number' => $data['phone_number'],
            ],
        ]);

        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['user_message']], 422);
        }

        $order = $result['data'];

        // Registro local de la orden pendiente (ligada al anuncio y usuario).
        $this->record->handle([
            'publicacion_id' => $data['publicacion_id'] ?? null,
            'order_number'   => $order->order_number ?? null,
            'payment_method' => 'pagoefectivo',
            'amount'         => $amount,
            'currency'       => 'PEN',
            'status'         => 'pending',
            'customer_email' => $data['email'],
            'description'    => $description,
            'metadata'       => ['order_id' => $order->id, 'plan' => $data['plan'] ?? null],
        ]);

        // El frontend usa este order->id en Culqi.settings({ order })
        return response()->json([
            'success'  => true,
            'order_id' => $order->id,
            'amount'   => round($amount / 100, 2),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /pago/orden/confirmar  — Verifica el estado de una orden
    // ─────────────────────────────────────────────────────────────
    // En el flujo multipago, LA ORDEN es el pago (no se carga token aparte).
    // Tras pagar en el checkout, el front llama aquí para conocer el estado real.
    public function confirmOrder(ConfirmOrderRequest $request): JsonResponse
    {
        $orderId = $request->validated()['order_id'];

        $result = $this->culqi->getOrder($orderId);
        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['user_message']], 422);
        }

        $order = $result['data'];
        $state = $order->state ?? 'unknown';            // created | pending | paid | expired | deleted
        $paid  = $state === 'paid';

        $transaction = Transaction::where('metadata->order_id', $orderId)->first();

        if ($transaction) {
            $wasPaid = $transaction->status === 'paid';
            $transaction->update(['status' => $paid ? 'paid' : 'pending']);

            // Solo dispara el evento la primera vez que pasa a "paid".
            if ($paid && ! $wasPaid) {
                event(new \App\Events\PaymentConfirmed($transaction->fresh()));
            }
        }

        return response()->json([
            'success'  => true,
            'paid'     => $paid,
            'state'    => $state,
            'order_id' => $orderId,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Resolución segura de monto y descripción desde el plan
    // ─────────────────────────────────────────────────────────────

    /** Si viene un plan, el monto se toma de config/plans.php (no del cliente). */
    private function resolveAmount(array $data): int
    {
        if (! empty($data['plan'])) {
            return (int) config("plans.{$data['plan']}.amount");
        }

        return (int) $data['amount'];
    }

    private function resolveDescription(array $data): string
    {
        if (! empty($data['plan'])) {
            return config("plans.{$data['plan']}.name") . ' — ' . config("plans.{$data['plan']}.description");
        }

        return $data['description'] ?? 'Pago';
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /culqi/webhook  — Notificaciones de Culqi
    // ─────────────────────────────────────────────────────────────
    public function webhook(\Illuminate\Http\Request $request, ProcessCulqiWebhook $processor): JsonResponse
    {
        // El procesamiento (idempotencia, anti-spoofing, auditoría) vive en el Action.
        try {
            $result = $processor->handle($request->all());
            Log::info('Culqi webhook', [
                'result'   => $result,
                'type'     => $request->input('type'),
                'resource' => $request->input('data.id'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Culqi webhook error', ['message' => $e->getMessage()]);
        }

        // SIEMPRE 200 de inmediato, para que Culqi no reintente en bucle.
        return response()->json(['received' => true], 200);
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /api/transaction/{id}  — Consulta de una transacción (solo testing)
    // ─────────────────────────────────────────────────────────────
    public function show(string $id): JsonResponse
    {
        // Solo por charge_id (cadena aleatoria), nunca por id interno enumerable.
        $tx = Transaction::where('charge_id', $id)->first();

        if (! $tx) {
            return response()->json(['success' => false, 'message' => 'Transacción no encontrada.'], 404);
        }

        return response()->json([
            'success'        => true,
            'order_id'       => $tx->id,
            'charge_id'      => $tx->charge_id,
            'amount'         => $tx->amount_in_soles,
            'currency'       => $tx->currency,
            'status'         => $tx->status,
            'payment_method' => $tx->payment_method,
            'card_last4'     => $tx->card_last4,
            'card_brand'     => $tx->card_brand,
            'created_at'     => $tx->created_at,
        ]);
    }
}
