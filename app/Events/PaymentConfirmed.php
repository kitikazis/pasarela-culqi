<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Se dispara cuando un pago queda CONFIRMADO (paid), sin importar el método.
 *
 * Este es el punto de extensión (seam) para la lógica de negocio:
 * aquí se engancha luego "destacar el anuncio", enviar correo, etc.
 * (Paso #2 del roadmap — conectar el pago con el negocio.)
 */
class PaymentConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(public Transaction $transaction) {}
}
