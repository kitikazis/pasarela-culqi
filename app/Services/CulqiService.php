<?php

namespace App\Services;

use Culqi\Culqi;
use Illuminate\Support\Facades\Log;

/**
 * Capa de servicio sobre el SDK oficial culqi/culqi-php (API v2.0).
 *
 * Seguridad:
 *  - La SECRET_KEY solo se usa aquí (backend). Nunca se expone ni se loggea.
 *  - Todas las operaciones de escritura envían $encryption_params (RSA+AES)
 *    cuando CULQI_RSA_ID y CULQI_RSA_PUBLIC_KEY están definidos en .env.
 *  - Los métodos devuelven arrays limpios y normalizados, NUNCA el objeto
 *    crudo de Culqi hacia capas superiores que puedan exponerlo.
 *  - El logging registra solo type/code/merchant_message; jamás datos de tarjeta.
 *
 * El SDK devuelve un objeto (stdClass) en éxito y un string en error;
 * el método handle() unifica ese comportamiento.
 */
class CulqiService
{
    /** Instancia del SDK autenticada con la llave PÚBLICA (solo tokens). */
    private function publicClient(): Culqi
    {
        return new Culqi(['api_key' => config('culqi.public_key')]);
    }

    /** Instancia del SDK autenticada con la llave SECRETA (cargos, etc.). */
    private function secretClient(): Culqi
    {
        return new Culqi(['api_key' => config('culqi.secret_key')]);
    }

    /**
     * Parámetros de encriptación RSA. Se activan solo si ambas variables
     * existen en .env; de lo contrario se retorna [] y el SDK no encripta.
     */
    private function rsaParams(): array
    {
        $rsaId  = config('culqi.rsa_id');
        $rsaKey = config('culqi.rsa_public_key');

        if (! empty($rsaId) && ! empty($rsaKey)) {
            return ['rsa_id' => $rsaId, 'rsa_public_key' => $rsaKey];
        }

        return [];
    }

    // ─────────────────────────────────────────────────────────────
    //  CARGOS (Charges)
    // ─────────────────────────────────────────────────────────────

    /**
     * Crea un cargo a una tarjeta tokenizada.
     *
     * @param array $data   token, amount, currency_code, email, description
     * @param array $auth3ds  Parámetros authentication_3DS (opcional, reintento 3DS)
     */
    public function createCharge(array $data, array $auth3ds = []): array
    {
        $payload = [
            'amount'        => (int) $data['amount'],
            'currency_code' => $data['currency_code'] ?? config('culqi.default_currency'),
            'email'         => $data['email'],
            'source_id'     => $data['token'],
            'description'   => $data['description'] ?? 'Pago',
            'capture'       => true,
        ];

        if (! empty($auth3ds)) {
            $payload['authentication_3DS'] = $auth3ds;
        }

        $response = $this->secretClient()->Charges->create($payload, $this->rsaParams());

        return $this->handle($response, 'charge.create');
    }

    /** Consulta un cargo por su ID (chr_...). Usado por el webhook para verificar. */
    public function getCharge(string $chargeId): array
    {
        $response = $this->secretClient()->Charges->get($chargeId);

        return $this->handle($response, 'charge.get');
    }

    // ─────────────────────────────────────────────────────────────
    //  DEVOLUCIONES (Refunds)
    // ─────────────────────────────────────────────────────────────

    public function createRefund(array $data): array
    {
        $payload = [
            'amount'    => (int) $data['amount'],
            'charge_id' => $data['charge_id'],
            'reason'    => $data['reason'],
        ];

        $response = $this->secretClient()->Refunds->create($payload, $this->rsaParams());

        return $this->handle($response, 'refund.create');
    }

    // ─────────────────────────────────────────────────────────────
    //  CLIENTES Y TARJETAS (one-click / recurrente)
    // ─────────────────────────────────────────────────────────────

    public function createCustomer(array $data): array
    {
        $payload = [
            'first_name'   => $data['first_name'],
            'last_name'    => $data['last_name'],
            'email'        => $data['email'],
            'address'      => $data['address']      ?? '-',
            'address_city' => $data['address_city'] ?? 'Lima',
            'country_code' => $data['country_code'] ?? config('culqi.country_code'),
            'phone_number' => $data['phone_number'],
        ];

        $response = $this->secretClient()->Customers->create($payload, $this->rsaParams());

        return $this->handle($response, 'customer.create');
    }

    public function createCard(array $data): array
    {
        $payload = [
            'customer_id' => $data['customer_id'],
            'token_id'    => $data['token_id'],
        ];

        $response = $this->secretClient()->Cards->create($payload, $this->rsaParams());

        return $this->handle($response, 'card.create');
    }

    // ─────────────────────────────────────────────────────────────
    //  ÓRDENES (PagoEfectivo)
    // ─────────────────────────────────────────────────────────────

    public function createOrder(array $data): array
    {
        $payload = [
            'amount'         => (int) $data['amount'],
            'currency_code'  => $data['currency_code'] ?? config('culqi.default_currency'),
            'description'    => $data['description'] ?? 'Orden de pago',
            'order_number'   => $data['order_number'],
            'expiration_date' => $data['expiration_date'] ?? (time() + 60 * 60 * 24), // 24h
            'client_details' => $data['client_details'],
            'confirm'        => false,
        ];

        $response = $this->secretClient()->Orders->create($payload, $this->rsaParams());

        return $this->handle($response, 'order.create');
    }

    // ─────────────────────────────────────────────────────────────
    //  YAPE  (token con llave pública  +  cargo con llave secreta)
    // ─────────────────────────────────────────────────────────────

    public function createYapeToken(string $phoneNumber, string $otp, int $amount): array
    {
        $response = $this->publicClient()->Tokens->createYape([
            'number_phone' => $phoneNumber,
            'otp'          => $otp,
            'amount'       => $amount,
        ]);

        return $this->handle($response, 'yape.token');
    }

    /**
     * Flujo Yape completo: genera el token y ejecuta el cargo.
     * Devuelve 'failed_step' indicando dónde falló (token_yape | cargo).
     */
    public function chargeYape(string $phone, string $otp, int $amount, string $email, ?string $description = null): array
    {
        $token = $this->createYapeToken($phone, $otp, $amount);
        if (! $token['success']) {
            return ['success' => false, 'failed_step' => 'token_yape', 'user_message' => $token['user_message']];
        }

        $charge = $this->createCharge([
            'token'         => $token['data']->id,
            'amount'        => $amount,
            'currency_code' => 'PEN',
            'email'         => $email,
            'description'   => $description,
        ]);
        if (! $charge['success']) {
            return ['success' => false, 'failed_step' => 'cargo', 'user_message' => $charge['user_message']];
        }

        return ['success' => true, 'data' => $charge['data'], 'token_id' => $token['data']->id];
    }

    // ─────────────────────────────────────────────────────────────
    //  TOKEN DE TARJETA  — SOLO TESTING (en producción lo genera el frontend)
    // ─────────────────────────────────────────────────────────────

    public function createToken(array $data): array
    {
        $response = $this->publicClient()->Tokens->create([
            'card_number'      => $data['card_number'],
            'cvv'              => $data['cvv'],
            'expiration_month' => $data['expiration_month'],
            'expiration_year'  => $data['expiration_year'],
            'email'            => $data['email'],
        ]);

        return $this->handle($response, 'token.create');
    }

    // ─────────────────────────────────────────────────────────────
    //  HEALTH CHECK
    // ─────────────────────────────────────────────────────────────

    public function ping(): array
    {
        try {
            $response = $this->secretClient()->Charges->all(['limit' => 1]);

            if (is_object($response)) {
                return ['ok' => true, 'message' => 'Credenciales válidas'];
            }

            $decoded = is_string($response) ? json_decode($response, true) : null;

            return [
                'ok'      => false,
                'message' => $decoded['user_message'] ?? 'Llave secreta inválida o sin conexión',
            ];
        } catch (\Throwable $e) {
            Log::error('Culqi ping error', ['message' => $e->getMessage()]);

            return ['ok' => false, 'message' => 'Sin conexión a Culqi'];
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  NORMALIZACIÓN DE RESPUESTA
    // ─────────────────────────────────────────────────────────────

    /**
     * El SDK devuelve stdClass en éxito y string (JSON o texto) en error.
     * Aquí se unifica a ['success' => bool, 'data'|'user_message' => ...].
     * El logging NUNCA incluye datos sensibles, solo metadatos del error.
     */
    private function handle(mixed $response, string $context): array
    {
        if (is_object($response) && isset($response->object) && $response->object !== 'error') {
            return ['success' => true, 'data' => $response];
        }

        $decoded = is_string($response) ? json_decode($response, true) : (array) $response;
        $decoded = is_array($decoded) ? $decoded : [];

        Log::error('Culqi API error', [
            'context'          => $context,
            'type'             => $decoded['type']             ?? null,
            'code'             => $decoded['code']             ?? null,
            'merchant_message' => $decoded['merchant_message'] ?? null,
        ]);

        return [
            'success'      => false,
            'user_message' => $decoded['user_message'] ?? 'No se pudo procesar la solicitud. Intenta nuevamente.',
            'code'         => $decoded['code'] ?? null,
        ];
    }
}
