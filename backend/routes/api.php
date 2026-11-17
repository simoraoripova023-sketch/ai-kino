<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PlaybackController;
use App\Http\Controllers\Api\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth routes
    Route::post('/auth/telegram', [AuthController::class, 'telegramAuth']);

    // Catalog routes (public)
    Route::get('/genres', [CatalogController::class, 'genres']);
    Route::get('/works', [CatalogController::class, 'index']);
    Route::get('/works/{id}', [CatalogController::class, 'show']);

    // Webhook route
    Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Playback & Watch progress
        Route::post('/episodes/{id}/play', [PlaybackController::class, 'play']);
        Route::get('/progress', [PlaybackController::class, 'progress']);

        // Payments & Purchases
        Route::post('/purchase-intents', [PaymentController::class, 'createIntent']);
        Route::get('/purchase-intents/{id}', [PaymentController::class, 'show']);
        Route::get('/me/purchases', [PaymentController::class, 'myPurchases']);
    });
});
