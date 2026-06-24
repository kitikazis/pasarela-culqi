<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Registro de una transacción de pago.
 *
 * Reglas de seguridad:
 *  - NUNCA se guardan: card_number, cvv, secret_key ni llaves RSA.
 *  - customer_email se almacena ENCRIPTADO (cast 'encrypted').
 *  - Solo se persisten los últimos 4 dígitos y la marca de la tarjeta,
 *    datos que Culqi devuelve ya enmascarados.
 */
class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'publicacion_id',
        'charge_id',
        'order_number',
        'payment_method',     // card | yape | pagoefectivo
        'amount',             // en céntimos
        'currency',
        'status',             // pending | paid | failed | refunded
        'culqi_response_code',
        'customer_email',
        'customer_name',
        'card_last4',
        'card_brand',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount'         => 'integer',
        'metadata'       => 'array',
        'customer_email' => 'encrypted',
    ];

    /**
     * El email encriptado nunca debe serializarse hacia el frontend.
     */
    protected $hidden = [
        'customer_email',
    ];

    public function getAmountInSolesAttribute(): float
    {
        return round($this->amount / 100, 2);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class, 'publicacion_id');
    }
}
