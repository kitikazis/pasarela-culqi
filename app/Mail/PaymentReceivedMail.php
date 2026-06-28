<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso al administrador de que se recibió un pago.
 */
class PaymentReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Transaction $transaction) {}

    public function envelope(): Envelope
    {
        $monto = number_format($this->transaction->amount / 100, 2);

        return new Envelope(
            subject: ' Nuevo pago en ' . config('app.name') . " — S/ {$monto}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-received');
    }
}
