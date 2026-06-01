<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de eventos recibidos de Culqi (webhooks).
 * - event_id ÚNICO → idempotencia (no procesar el mismo evento dos veces).
 * - payload → auditoría / debug.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->nullable()->unique();   // id del evento de Culqi
            $table->string('type')->nullable();                  // charge.* | order.* ...
            $table->string('resource_id')->nullable();           // chr_... | ord_...
            $table->string('status')->default('received');       // received | processed | ignored | failed
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('resource_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};
