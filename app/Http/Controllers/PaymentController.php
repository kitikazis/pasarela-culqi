<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChargeRequest;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\RefundRequest;
use App\Http\Requests\YapeRequest;
use App\Models\Transaction;
use App\Services\CulqiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private CulqiService $culqi) {}

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

        $result = $this->culqi->createCharge([
            'token'         => $data['token'],
            'amount'        => $amount,
            'currency_code' => $data['currency_code'] ?? 'PEN',
            'email'         => $data['email'],
            'description'   => $description,
        ]);

        if (! $result['success']) {
            // Auditoría del intento fallido (sin datos sensibles)
            Transaction::create([
                'payment_method'      => 'card',
                'amount'              => $amount,
                'currency'            => $data['currency_code'] ?? 'PEN',
                'status'              => 'failed',
                'culqi_response_code' => $result['code'] ?? null,
                'customer_email'      => $data['email'],
                'description'         => $description,
                'metadata'            => ['plan' => $data['plan'] ?? null],
            ]);

            return response()->json([
                'success' => false,
                'message' => $result['user_message'],
            ], 422);
        }

        $charge = $result['data'];

        $transaction = Transaction::create([
            'charge_id'           => $charge->id,
            'payment_method'      => 'card',
            'amount'              => $charge->amount,
            'currency'            => $charge->currency_code ?? 'PEN',
            'status'              => 'paid',
            'culqi_response_code' => $charge->outcome->code ?? null,
            'customer_email'      => $charge->email ?? $data['email'],
            'card_last4'          => $charge->source->last_four ?? null,
            'card_brand'          => $charge->source->iin->card_brand ?? null,
            'description'         => $description,
            'metadata'            => [
                'outcome_type' => $charge->outcome->type ?? null,
                'plan'         => $data['plan'] ?? null,
            ],
        ]);

        return response()->json([
            'success'    => true,
            'message'    => 'Pago procesado exitosamente.',
            'charge_id'  => $charge->id,
            'order_id'   => $transaction->id,
            'amount'     => round($charge->amount / 100, 2),
            'currency'   => $charge->currency_code ?? 'PEN',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /pago/devolucion  — Devolución (Refund)
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
    public function saveCard(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token'        => ['required', 'string', 'starts_with:tkn_live_,tkn_test_'],
            'email'        => ['required', 'email'],
            'first_name'   => ['required', 'string', 'max:50'],
            'last_name'    => ['required', 'string', 'max:50'],
            'phone_number' => ['required', 'string', 'max:15'],
        ]);

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
    //  POST /api/payment/yape  — Pago con Yape
    // ─────────────────────────────────────────────────────────────
    public function yape(YapeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->culqi->chargeYape(
            $data['phone_number'],
            $data['otp'],
            (int) $data['amount'],
            $data['email'],
            $data['description'] ?? null,
        );

        if (! $result['success']) {
            Transaction::create([
                'payment_method' => 'yape',
                'amount'         => $data['amount'],
                'currency'       => 'PEN',
                'status'         => 'failed',
                'customer_email' => $data['email'],
                'description'    => $data['description'] ?? null,
                'metadata'       => ['failed_step' => $result['failed_step'] ?? null],
            ]);

            return response()->json([
                'success'     => false,
                'message'     => $result['user_message'],
                'failed_step' => $result['failed_step'] ?? null,
            ], 422);
        }

        $charge = $result['data'];

        $transaction = Transaction::create([
            'charge_id'           => $charge->id,
            'payment_method'      => 'yape',
            'amount'              => $charge->amount,
            'currency'            => 'PEN',
            'status'              => 'paid',
            'culqi_response_code' => $charge->outcome->code ?? null,
            'customer_email'      => $charge->email ?? $data['email'],
            'description'         => $data['description'] ?? null,
            'metadata'            => ['outcome_type' => $charge->outcome->type ?? null],
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Pago con Yape procesado exitosamente.',
            'charge_id' => $charge->id,
            'order_id'  => $transaction->id,
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

        // Registro local de la orden pendiente
        Transaction::create([
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
    public function webhook(Request $request): JsonResponse
    {
        $event = $request->all();
        $type  = $event['type'] ?? 'unknown';

        // Validación anti-spoofing: NO confiamos en el payload.
        // Re-consultamos el recurso directamente a Culqi con la llave secreta.
        $resourceId = $event['data']['id'] ?? null;

        if ($resourceId && str_starts_with($resourceId, 'chr_')) {
            $verified = $this->culqi->getCharge($resourceId);

            if ($verified['success']) {
                $charge = $verified['data'];
                $status = ($charge->outcome->type ?? null) === 'venta_exitosa' ? 'paid' : 'failed';

                Transaction::where('charge_id', $charge->id)->update([
                    'status'              => $status,
                    'culqi_response_code' => $charge->outcome->code ?? null,
                ]);

                Log::info('Culqi webhook procesado', ['type' => $type, 'charge_id' => $charge->id]);
            } else {
                Log::warning('Culqi webhook no verificable', ['type' => $type, 'resource' => $resourceId]);
            }
        }

        // Siempre respondemos 200 de inmediato.
        return response()->json(['received' => true], 200);
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /api/health  — Estado de BD y Culqi
    // ─────────────────────────────────────────────────────────────
    public function health(): JsonResponse
    {
        $connection = config('database.default');
        $db         = config("database.connections.{$connection}");

        $database = [
            'ok'         => false,
            'connection' => $connection,
            'host'       => $db['host'] ?? null,
            'port'       => $db['port'] ?? null,
            'database'   => $db['database'] ?? null,
            'username'   => $db['username'] ?? null,
            'message'    => null,
        ];

        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $database['ok']      = true;
            $database['message'] = 'Conexión exitosa';
        } catch (\Throwable $e) {
            // Mensaje claro según el tipo de error de MySQL
            $database['message'] = $this->dbErrorHint($e->getMessage());
            // Solo en modo debug se incluye el error técnico completo
            if (config('app.debug')) {
                $database['error_raw'] = $e->getMessage();
            }
        }

        $culqi = $this->culqi->ping();

        return response()->json([
            'status'    => ($database['ok'] && $culqi['ok']) ? 'ok' : 'degraded',
            'database'  => $database,
            'culqi'     => $culqi,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /** Traduce errores comunes de MySQL a una pista accionable. */
    private function dbErrorHint(string $error): string
    {
        return match (true) {
            str_contains($error, 'Access denied')              => 'Usuario o contraseña incorrectos, o el usuario no está asignado a la base de datos.',
            str_contains($error, 'Unknown database')           => 'La base de datos no existe con ese nombre.',
            str_contains($error, 'timed out'),
            str_contains($error, 'Connection timed out')        => 'Timeout: el servidor no responde. Probablemente el puerto 3306 está bloqueado o falta autorizar tu IP en Remote MySQL.',
            str_contains($error, 'Connection refused')          => 'Conexión rechazada: el host/puerto es incorrecto o el servidor no acepta conexiones remotas.',
            str_contains($error, 'No such host'),
            str_contains($error, 'getaddrinfo'),
            str_contains($error, 'name or service not known')   => 'No se pudo resolver el host. Verifica DB_HOST.',
            str_contains($error, "Host '")                      => 'Tu IP NO está autorizada en el servidor. Habilita Remote MySQL en cPanel con tu IP pública.',
            default                                             => 'No se pudo conectar a la base de datos.',
        };
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /api/transaction/{id}  — Consulta local (no expone email)
    // ─────────────────────────────────────────────────────────────
    public function show(string $id): JsonResponse
    {
        $tx = Transaction::where('charge_id', $id)->orWhere('id', $id)->first();

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
