<?php

namespace App\Http\Controllers;

use App\Exceptions\CulqiException;
use App\Models\Payment;
use App\Services\CulqiService;
use App\Validators\PaymentValidator;

class PaymentController
{
    public function __construct(
        private readonly CulqiService    $culqiService,
        private readonly Payment         $paymentModel,
        private readonly PaymentValidator $validator
    ) {}

    // ── GET /health ────────────────────────────────────────────

    public function health(): void
    {
        $dbStatus    = $this->checkDatabase();
        $culqiStatus = $this->culqiService->ping();

        $allOk   = $dbStatus['status'] === 'ok' && $culqiStatus['status'] === 'ok';
        $httpCode = $allOk ? 200 : 503;

        $this->json([
            'database'  => $dbStatus,
            'culqi'     => $culqiStatus,
            'timestamp' => date('c'),
        ], $httpCode);
    }

    // ── POST /payment/charge (tarjeta) ─────────────────────────

    public function charge(): void
    {
        $data = $this->getJsonBody();

        if (!$this->validator->validateCharge($data)) {
            $this->json([
                'success' => false,
                'errors'  => $this->validator->getErrors(),
            ], 422);
            return;
        }

        // Registrar en BD como pending
        $paymentId = $this->paymentModel->create([
            'token_id'       => $data['token_id'],
            'amount'         => $data['amount'],
            'currency'       => $data['currency'] ?? 'PEN',
            'payment_method' => 'card',
            'email'          => $data['email'],
            'description'    => $data['description'] ?? null,
        ]);

        try {
            $charge = $this->culqiService->createCharge($data);

            $this->paymentModel->updateStatus($paymentId, 'paid', $charge['id'], $charge);

            $this->json([
                'success'    => true,
                'message'    => 'Pago procesado exitosamente.',
                'payment_id' => $paymentId,
                'charge_id'  => $charge['id'],
                'amount'     => (int)$data['amount'] / 100,
                'currency'   => $data['currency'] ?? 'PEN',
            ], 200);

        } catch (CulqiException $e) {
            $this->paymentModel->updateStatus($paymentId, 'failed', null, $e->getCulqiError());

            $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getHttpStatus());
        }
    }

    // ── POST /payment/yape ─────────────────────────────────────

   // ── POST /payment/yape ─────────────────────────────────────

public function yapeCharge(): void
{
    $data = $this->getJsonBody();

    // Validar entrada
    if (!$this->validator->validateYapeCharge($data)) {
        $this->json([
            'success' => false,
            'errors'  => $this->validator->getErrors(),
        ], 422);
        return;
    }

    // Registrar como pending
    $paymentId = $this->paymentModel->create([
        'amount'         => $data['amount'],
        'currency'       => 'PEN',
        'payment_method' => 'yape',
        'email'          => $data['email'],
        'description'    => $data['description'] ?? 'Pago de mantenimiento via Yape',
    ]);

    try {
        // Paso 1: Generar token Yape con número + OTP
    $yapeToken = $this->culqiService->createYapeToken(
    $data['phone_number'],
    $data['otp'],
    (int) $data['amount']  // ← pasar el monto
);

        // Paso 2: Ejecutar el cargo con el token obtenido
        $charge = $this->culqiService->createYapeCharge([
            'amount'      => $data['amount'],
            'email'       => $data['email'],
            'description' => $data['description'] ?? 'Pago de mantenimiento via Yape',
            'yape_token'  => $yapeToken['id'],  // ← id del token generado
        ]);

        // Guardar éxito
        $this->paymentModel->updateStatus(
            $paymentId,
            'paid',
            $charge['id'],
            $charge
        );

        $this->json([
            'success'    => true,
            'message'    => 'Pago con Yape procesado exitosamente.',
            'payment_id' => $paymentId,
            'charge_id'  => $charge['id'],
            'amount'     => (int)$data['amount'] / 100,
            'currency'   => 'PEN',
        ], 200);

    } catch (CulqiException $e) {
        $this->paymentModel->updateStatus($paymentId, 'failed', null, $e->getCulqiError());

        $this->json([
            'success' => false,
            'message' => $e->getMessage(),
            'step'    => isset($yapeToken) ? 'cargo' : 'token_yape', // ← indica en qué paso falló
        ], $e->getHttpStatus());
    }
}



    // ── GET /payment/{id} ──────────────────────────────────────
    public function show(string $id): void
    {
        $payment = $this->paymentModel->findById($id);

        if (!$payment) {
            $this->json(['success' => false, 'message' => 'Pago no encontrado.'], 404);
            return;
        }

        $this->json([
            'id'             => $payment['id'],
            'amount'         => (int)$payment['amount'] / 100,
            'currency'       => $payment['currency'],
            'status'         => $payment['status'],
            'payment_method' => $payment['payment_method'],
            'charge_id'      => $payment['culqi_charge_id'],
            'created_at'     => $payment['created_at'],
        ]);
    }

    // ── Helpers ────────────────────────────────────────────────

    private function getJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?? [];
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function checkDatabase(): array
    {
        try {
            $this->paymentModel->ping();
            return ['status' => 'ok', 'message' => 'Conexión activa'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}