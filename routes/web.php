<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Autenticación social (Google / Microsoft)
|--------------------------------------------------------------------------
*/
Route::get('/auth/{provider}/redirect', [AuthController::class, 'redirect'])->name('auth.redirect');
Route::get('/auth/{provider}/callback', [AuthController::class, 'callback'])->name('auth.callback');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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

// Verificar estado de una orden tras pagar (multipago: la orden ES el pago)
Route::post('/pago/orden/confirmar', [PaymentController::class, 'confirmOrder'])
    ->middleware('throttle:20,1')
    ->name('pago.orden.confirmar');

// Webhook de Culqi — excluido de CSRF en bootstrap/app.php
Route::post('/culqi/webhook', [PaymentController::class, 'webhook'])->name('culqi.webhook');
