<?php
namespace App\Services;

use App\Exceptions\CulqiException;

class CulqiService
{
    private string $privateKey;
    private string $publicKey;  // ← AGREGAR
    private string $baseUrl;

    public function __construct(array $culqiConfig)
    {
        $this->privateKey = $culqiConfig['private_key'];
        $this->publicKey  = $culqiConfig['public_key'];  // ← AGREGAR
        $this->baseUrl    = rtrim($culqiConfig['base_url'], '/');
    }

    // ── Token Yape (usa LLAVE PÚBLICA) ─────────────────────────

    public function createYapeToken(string $phoneNumber, string $otp, int $amount): array
    {
        return $this->post('/tokens', [
            'number_phone'        => $phoneNumber,
            'otp'                 => $otp,
            'amount'              => $amount,
            'payment_method_type' => 'yape',
            'metadata'            => ['negocio' => 'Bitácora de mantenimiento'],
        ], usePublicKey: true);
    }

    // ── Cargo con Yape (usa LLAVE PRIVADA) ─────────────────────

    public function createYapeCharge(array $data): array
    {
        return $this->post('/charges', [
            'amount'        => (int) $data['amount'],
            'currency_code' => 'PEN',
            'email'         => $data['email'],
            'source_id'     => $data['yape_token'],
            'description'   => $data['description'] ?? 'Pago de mantenimiento',
        ]);
    }

    // ── Cargo con tarjeta (usa LLAVE PRIVADA) ──────────────────

    public function createCharge(array $data): array
    {
        return $this->post('/charges', [
            'amount'        => (int) $data['amount'],
            'currency_code' => $data['currency'] ?? 'PEN',
            'email'         => $data['email'],
            'source_id'     => $data['token_id'],
            'description'   => $data['description'] ?? 'Pago de mantenimiento',
        ]);
    }

    // ── Consultar cargo ────────────────────────────────────────

    public function getCharge(string $chargeId): array
    {
        return $this->get("/charges/{$chargeId}");
    }

    // ── Health check ───────────────────────────────────────────

    public function ping(): array
    {
        try {
            $ch       = $this->buildCurl('/charges?limit=1', 'GET');
            $body     = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($curlErr) {
                return ['status' => 'error', 'message' => 'No se puede alcanzar Culqi: ' . $curlErr];
            }

            $decoded = json_decode($body, true);

            if ($httpCode === 401) {
                return ['status' => 'error', 'http_status' => $httpCode, 'message' => 'Credenciales de Culqi inválidas.'];
            }

            if ($httpCode === 200) {
                return ['status' => 'ok', 'http_status' => $httpCode, 'message' => 'Culqi API responde correctamente y credenciales válidas.'];
            }

            return ['status' => 'error', 'http_status' => $httpCode, 'message' => 'Respuesta inesperada: ' . ($decoded['user_message'] ?? 'Error desconocido')];

        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => 'No se puede alcanzar Culqi: ' . $e->getMessage()];
        }
    }

    // ── HTTP Helpers ───────────────────────────────────────────

    private function post(string $endpoint, array $payload, bool $usePublicKey = false): array
    {
        $ch = $this->buildCurl($endpoint, 'POST', $payload, $usePublicKey);
        return $this->execute($ch, $endpoint);
    }

    private function get(string $endpoint, bool $usePublicKey = false): array
    {
        $ch = $this->buildCurl($endpoint, 'GET', null, $usePublicKey);
        return $this->execute($ch, $endpoint);
    }

    private function buildCurl(string $endpoint, string $method, ?array $payload = null, bool $usePublicKey = false): \CurlHandle
    {
        $ch  = curl_init($this->baseUrl . $endpoint);
        $key = $usePublicKey ? $this->publicKey : $this->privateKey;  // ← elegir llave

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $key,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        return $ch;
    }

    private function execute(\CurlHandle $ch, string $endpoint): array
    {
        $body     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            throw new CulqiException('No se pudo conectar con Culqi.', 503);
        }

        $decoded = json_decode($body, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return $decoded;
        }

        $userMessage = $decoded['user_message'] ?? $decoded['merchant_message'] ?? 'Error al procesar el pago.';

        error_log(sprintf(
            '[Culqi Error] endpoint=%s http=%d code=%s msg=%s',
            $endpoint,
            $httpCode,
            $decoded['code'] ?? 'unknown',
            $decoded['merchant_message'] ?? $userMessage
        ));

        throw new CulqiException($userMessage, $httpCode, $decoded);
    }
}