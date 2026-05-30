<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API — testing / servidor-a-servidor (sin CSRF)
|--------------------------------------------------------------------------
| El checkout de producción usa las rutas web /pago/* con CSRF.
| Estas rutas /api/* permiten probar todo el flujo desde Postman.
| En producción, protéjalas con autenticación de API (Sanctum/token).
*/

// Estado del servicio (BD + Culqi)
Route::get('/health', [PaymentController::class, 'health']);

Route::prefix('payment')->group(function () {
    // Cargo con tarjeta (requiere un token tkn_ generado por Culqi)
    Route::post('/charge', [PaymentController::class, 'charge'])->middleware('throttle:10,1');

    // Pago con Yape (token + cargo en un solo paso)
    Route::post('/yape', [PaymentController::class, 'yape'])->middleware('throttle:10,1');

    // Devolución
    Route::post('/refund', [PaymentController::class, 'refund'])->middleware('throttle:10,1');

    // Guardar cliente + tarjeta (one-click)
    Route::post('/save-card', [PaymentController::class, 'saveCard'])->middleware('throttle:10,1');

    // Crear orden (PagoEfectivo / Cuotéalo)
    Route::post('/order', [PaymentController::class, 'createOrder'])->middleware('throttle:10,1');
});

// Consulta local de una transacción por charge_id o id interno
Route::get('/transaction/{id}', [PaymentController::class, 'show']);
