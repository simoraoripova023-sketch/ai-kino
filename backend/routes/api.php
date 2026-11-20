<?php

use App\Http\Controllers\Api\Admin\AdminAnalyticsController;
use App\Http\Controllers\Api\Admin\AdminEpisodeController;
use App\Http\Controllers\Api\Admin\AdminWorkController;
use App\Http\Controllers\Api\Admin\UploadSessionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PlaybackController;
use App\Http\Controllers\Api\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth routes
    Route::post('/auth/telegram', [AuthController::class, 'telegramAuth']);
    Route::post('/auth/dev-login', [AuthController::class, 'devLogin']);

    // Catalog & Stream routes (public)
    Route::get('/genres', [CatalogController::class, 'genres']);
    Route::get('/works', [CatalogController::class, 'index']);
    Route::get('/works/{id}', [CatalogController::class, 'show']);
    Route::get('/episodes/{id}/stream', [PlaybackController::class, 'stream']);

    // Webhook route
    Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);

    // Authenticated user routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Playback & Watch progress
        Route::post('/episodes/{id}/play', [PlaybackController::class, 'play']);
        Route::get('/progress', [PlaybackController::class, 'progress']);

        // Payments & Purchases
        Route::post('/purchase-intents', [PaymentController::class, 'createIntent']);
        Route::get('/purchase-intents/{id}', [PaymentController::class, 'show']);
        Route::post('/purchase-intents/{id}/simulate-pay', [PaymentController::class, 'simulatePay']);
        Route::get('/me/purchases', [PaymentController::class, 'myPurchases']);

        // Referrals & Rewards
        Route::get('/referrals/stats', [\App\Http\Controllers\Api\ReferralController::class, 'stats']);
        Route::post('/referrals/claim', [\App\Http\Controllers\Api\ReferralController::class, 'claim']);

        // Admin routes (RBAC check)
        Route::middleware('admin')->prefix('admin')->group(function () {
            // Works management
            Route::get('/works', [AdminWorkController::class, 'index']);
            Route::post('/works', [AdminWorkController::class, 'store']);
            Route::get('/works/{id}', [AdminWorkController::class, 'show']);
            Route::patch('/works/{id}', [AdminWorkController::class, 'update']);
            Route::delete('/works/{id}', [AdminWorkController::class, 'destroy']);
            Route::post('/works/{id}/publish', [AdminWorkController::class, 'publish']);
            Route::post('/works/{id}/hide', [AdminWorkController::class, 'hide']);

            // Episodes management
            Route::get('/works/{workId}/episodes', [AdminEpisodeController::class, 'index']);
            Route::post('/works/{workId}/episodes', [AdminEpisodeController::class, 'store']);
            Route::patch('/episodes/{id}', [AdminEpisodeController::class, 'update']);
            Route::delete('/episodes/{id}', [AdminEpisodeController::class, 'destroy']);
            Route::post('/episodes/{id}/publish', [AdminEpisodeController::class, 'publish']);
            Route::post('/episodes/{id}/hide', [AdminEpisodeController::class, 'hide']);

            // Upload sessions
            Route::post('/uploads/sessions', [UploadSessionController::class, 'createSession']);
            Route::get('/uploads/sessions', [UploadSessionController::class, 'index']);

            // Analytics & Audit
            Route::get('/analytics/overview', [AdminAnalyticsController::class, 'overview']);
            Route::get('/payments', [AdminAnalyticsController::class, 'payments']);
            Route::get('/audit-logs', [AdminAnalyticsController::class, 'auditLogs']);
        });
    });
});
