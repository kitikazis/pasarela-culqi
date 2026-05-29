<?php

namespace App\Validators;

class PaymentValidator
{
    private array $errors = [];

    // ── Tarjeta ────────────────────────────────────────────────

    public function validateCharge(array $data): bool
    {
        $this->errors = [];

        if (empty($data['token_id']) || !str_starts_with($data['token_id'], 'tkn_')) {
            $this->errors['token_id'] = 'El token de pago es inválido o no tiene el formato correcto.';
        }

        $this->validateCommonFields($data);

        return empty($this->errors);
    }

    // ── Yape ───────────────────────────────────────────────────
public function validateYapeCharge(array $data): bool
{
    $this->errors = [];

    if (empty($data['phone_number']) || !preg_match('/^9\d{8}$/', $data['phone_number'])) {
        $this->errors['phone_number'] = 'El número de celular debe tener 9 dígitos y empezar con 9.';
    }

    if (empty($data['otp']) || !preg_match('/^\d{6}$/', $data['otp'])) {
        $this->errors['otp'] = 'El OTP debe ser un código de 6 dígitos numéricos.';
    }

    $this->validateCommonFields($data);

    return empty($this->errors);
}

    // ── Campos comunes ─────────────────────────────────────────

    private function validateCommonFields(array $data): void
    {
        if (empty($data['amount']) || !is_numeric($data['amount']) || (int)$data['amount'] < 100) {
            $this->errors['amount'] = 'El monto mínimo de pago es S/1.00 (100 céntimos).';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Ingresa un correo electrónico válido.';
        }

        if (!empty($data['currency']) && !in_array($data['currency'], ['PEN', 'USD'], true)) {
            $this->errors['currency'] = 'La moneda debe ser PEN o USD.';
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}