<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;
    public $planDetails;

    public function __construct($transaction, $planDetails)
    {
        $this->transaction = $transaction;
        $this->planDetails = $planDetails;
    }

    public function build()
    {
        return $this->subject('Tu compra en Anuncialo.pe')
                    ->view('emails.payment-receipt')
                    ->with([
                        'transaction' => $this->transaction,
                        'planDetails' => $this->planDetails,
                    ]);
    }
}
