<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Pasarela de pago Culqi
|--------------------------------------------------------------------------
*/

// Vista de checkout (Culqi Checkout v4)
Route::get('/pago', [PaymentController::class, 'showCheckout'])->name('checkout');

// Cargo con tarjeta — Rate limit 5 req/min por IP. Añadir 'auth' si aplica.
Route::post('/pago/cargo', [PaymentController::class, 'charge'])
    ->middleware('throttle:5,1')
    ->name('pago.cargo');

// Devolución — En producción protéjase con 'auth' y permisos de administrador.
Route::post('/pago/devolucion', [PaymentController::class, 'refund'])
    ->middleware('throttle:10,1')
    ->name('pago.devolucion');

// Guardar tarjeta (cliente + card para cobros one-click)
Route::post('/pago/guardar-tarjeta', [PaymentController::class, 'saveCard'])
    ->middleware('throttle:5,1')
    ->name('pago.guardar_tarjeta');

// Crear orden (habilita PagoEfectivo / Cuotéalo en el Checkout)
Route::post('/pago/orden', [PaymentController::class, 'createOrder'])
    ->middleware('throttle:10,1')
    ->name('pago.orden');

// Webhook de Culqi — excluido de CSRF en bootstrap/app.php
Route::post('/culqi/webhook', [PaymentController::class, 'webhook'])->name('culqi.webhook');
