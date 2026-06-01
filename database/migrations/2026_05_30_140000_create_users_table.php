<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Usuarios autenticados vía OAuth (Google / Microsoft).
 * No usan contraseña: el login es por proveedor externo (Socialite).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('avatar')->nullable();
            $table->string('provider')->nullable();      // google | microsoft
            $table->string('provider_id')->nullable();   // id del usuario en el proveedor
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();       // OAuth-only: normalmente null
            $table->rememberToken();
            $table->timestamps();

            $table->index(['provider', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
