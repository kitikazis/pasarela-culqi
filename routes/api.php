<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [PaymentController::class, 'health']);

Route::prefix('payment')->group(function () {
    Route::post('/charge', [PaymentController::class, 'charge']);
    Route::post('/yape',   [PaymentController::class, 'yapeCharge']);
    Route::get('/{id}',    [PaymentController::class, 'show']);
});
