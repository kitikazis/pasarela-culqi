<?php

namespace App\Actions;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Punto único para registrar una transacción en la BD.
 *
 * Responsabilidades:
 *  - Inyecta el user_id del usuario autenticado si no viene en los datos.
 *  - Da atomicidad "suave": si el INSERT falla DESPUÉS de un cobro exitoso en
 *    Culqi, no revienta la respuesta al cliente (ya pagó) pero deja un log
 *    CRÍTICO con el charge_id para reconciliar manualmente.
 *
 * Centralizar aquí evita duplicar Transaction::create() en cada método de pago
 * y garantiza que TODA transacción quede ligada al usuario cuando hay sesión.
 */
class RecordTransaction
{
    public function handle(array $attributes): ?Transaction
    {
        // Liga la transacción al usuario autenticado (si lo hay) salvo que ya venga.
        $attributes['user_id'] ??= Auth::id();

        try {
            return Transaction::create($attributes);
        } catch (\Throwable $e) {
            Log::critical('No se pudo registrar la transacción (posible cobro sin registro local)', [
                'charge_id'      => $attributes['charge_id'] ?? null,
                'order_number'   => $attributes['order_number'] ?? null,
                'payment_method' => $attributes['payment_method'] ?? null,
                'amount'         => $attributes['amount'] ?? null,
                'status'         => $attributes['status'] ?? null,
                'error'          => $e->getMessage(),
            ]);

            return null;
        }
    }
}
