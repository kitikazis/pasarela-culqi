<?php

use App\Http\Controllers\AdController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// Toda la API limitada a 120 peticiones/min por usuario (o IP si es invitado).
Route::middleware('throttle:api')->group(function () {

    // Anuncios públicos (desde la BD).
    // OJO: la ruta NO puede contener "ads" porque los bloqueadores (Brave Shields,
    // uBlock, EasyList) cortan cualquier URL con esa palabra. Por eso es /publicaciones.
    Route::get('/publicaciones', [AdController::class, 'index']);

    // Estado del servicio (BD + Culqi). Oculta detalles de conexión salvo en debug.
    Route::get('/health', [HealthController::class, 'index']);

    /*
    |----------------------------------------------------------------------
    | API de PRUEBA — solo fuera de producción
    |----------------------------------------------------------------------
    | Estas rutas duplican el flujo de pago SIN CSRF para probarlo desde
    | Postman. En PRODUCCIÓN no se registran (el checkout real usa /pago/*
    | con CSRF). Así se elimina la superficie de ataque (cargos/IDOR).
    */
    if (! app()->isProduction()) {

        Route::prefix('payment')->group(function () {
            Route::post('/charge', [PaymentController::class, 'charge'])->middleware('throttle:10,1');
            Route::post('/yape', [PaymentController::class, 'yape'])->middleware('throttle:10,1');
            Route::post('/refund', [PaymentController::class, 'refund'])->middleware('throttle:10,1');
            Route::post('/save-card', [PaymentController::class, 'saveCard'])->middleware('throttle:10,1');
            Route::post('/order', [PaymentController::class, 'createOrder'])->middleware('throttle:10,1');
            Route::post('/order/confirm', [PaymentController::class, 'confirmOrder'])->middleware('throttle:20,1');
        });

        // Consulta local de una transacción por charge_id (cadena aleatoria, no enumerable).
        Route::get('/transaction/{id}', [PaymentController::class, 'show']);
    }
});
