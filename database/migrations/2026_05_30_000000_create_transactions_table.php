<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->string('charge_id')->nullable();
            $table->string('order_number')->nullable();
            $table->string('payment_method')->default('card'); // card | yape | pagoefectivo

            $table->unsignedBigInteger('amount');              // céntimos
            $table->string('currency', 3)->default('PEN');

            $table->string('status')->default('pending');      // pending | paid | failed | refunded
            $table->string('culqi_response_code')->nullable();

            // PII: se guarda encriptado desde el modelo (cast 'encrypted')
            $table->text('customer_email')->nullable();

            // Datos NO sensibles que Culqi devuelve ya enmascarados
            $table->string('card_last4', 4)->nullable();
            $table->string('card_brand')->nullable();

            $table->text('description')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices para consultas frecuentes
            $table->index('charge_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
