<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Páginas (URLs limpias, sin .html)
|--------------------------------------------------------------------------
| Se sirven SIEMPRE a través de Laravel (PageController) para inyectarles
| cache-busting automático en sus assets (styles.css?v=..., *.js?v=...) y
| cabeceras no-cache. Así cada usuario ve el último cambio sin limpiar caché
| ni entrar en incógnito. (Requiere "DirectoryIndex index.php" en .htaccess
| para que la home tampoco se sirva como index.html estático.)
*/
Route::get('/',                 [PageController::class, 'show'])->defaults('file', 'index.html');
Route::get('/publicar',         [PageController::class, 'show'])->defaults('file', 'publicar.html')->name('publicar');
Route::get('/mis-anuncios',     [PageController::class, 'show'])->defaults('file', 'mis-anuncios.html')->name('mis-anuncios');
Route::get('/completar-perfil', [PageController::class, 'show'])->defaults('file', 'completar-perfil.html')->name('completar-perfil');

/*
|--------------------------------------------------------------------------
| Autenticación social (Google / Microsoft)
|--------------------------------------------------------------------------
*/
Route::get('/auth/{provider}/redirect', [AuthController::class, 'redirect'])->name('auth.redirect');
Route::get('/auth/{provider}/callback', [AuthController::class, 'callback'])->name('auth.callback');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Usuario autenticado (sesión web) — para que el frontend muestre nombre/foto.
Route::get('/me', [AuthController::class, 'me'])->middleware('throttle:per-user')->name('me');

// Anuncios del usuario logueado (sesión web).
Route::get('/mis-anuncios/datos', [App\Http\Controllers\AdController::class, 'mine'])
    ->middleware('throttle:per-user')
    ->name('ads.mine');

// Publicar un anuncio nuevo (sesión web).
Route::post('/anuncios', [App\Http\Controllers\AdController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('ads.store');

// Activar/desactivar un anuncio propio (la propiedad se valida en el controlador).
Route::patch('/anuncios/{ad}', [App\Http\Controllers\AdController::class, 'update'])
    ->middleware('throttle:per-user')
    ->name('ads.update');

// Eliminar un anuncio propio (soft delete → Papelera).
Route::delete('/anuncios/{ad}', [App\Http\Controllers\AdController::class, 'destroy'])
    ->middleware('throttle:per-user')
    ->name('ads.destroy');

// Papelera: anuncios eliminados por el usuario (restaurables 30 días).
Route::get('/mis-anuncios/papelera', [App\Http\Controllers\AdController::class, 'trashed'])
    ->middleware('throttle:per-user')
    ->name('ads.trashed');

// Restaurar un anuncio de la Papelera.
Route::patch('/anuncios/{id}/restaurar', [App\Http\Controllers\AdController::class, 'restore'])
    ->middleware('throttle:per-user')
    ->name('ads.restore');

/*
|--------------------------------------------------------------------------
| Pasarela de pago Culqi
|--------------------------------------------------------------------------
*/

// Vista de checkout (Culqi Checkout v4)
Route::get('/pago', [PaymentController::class, 'showCheckout'])->name('checkout');

// Cargo con tarjeta — requiere sesión (la compra acredita al usuario).
Route::post('/pago/cargo', [PaymentController::class, 'charge'])
    ->middleware(['throttle:5,1', 'auth'])
    ->name('pago.cargo');

// Devolución — solo administradores (config('app.admins')).
Route::post('/pago/devolucion', [PaymentController::class, 'refund'])
    ->middleware(['throttle:10,1', 'admin'])
    ->name('pago.devolucion');

// Guardar tarjeta (cliente + card para cobros one-click) — requiere sesión.
Route::post('/pago/guardar-tarjeta', [PaymentController::class, 'saveCard'])
    ->middleware(['throttle:5,1', 'auth'])
    ->name('pago.guardar_tarjeta');

// Crear orden (habilita PagoEfectivo / Cuotéalo en el Checkout) — requiere sesión.
Route::post('/pago/orden', [PaymentController::class, 'createOrder'])
    ->middleware(['throttle:10,1', 'auth'])
    ->name('pago.orden');

// Verificar estado de una orden tras pagar (multipago: la orden ES el pago)
Route::post('/pago/orden/confirmar', [PaymentController::class, 'confirmOrder'])
    ->middleware('throttle:20,1')
    ->name('pago.orden.confirmar');

// Webhook de Culqi — excluido de CSRF en bootstrap/app.php
Route::post('/culqi/webhook', [PaymentController::class, 'webhook'])->name('culqi.webhook');
