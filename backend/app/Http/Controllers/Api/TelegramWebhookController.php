<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\TelegramBotService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected TelegramBotService $botService;
    protected PaymentService $paymentService;

    public function __construct(TelegramBotService $botService, PaymentService $paymentService)
    {
        $this->botService = $botService;
        $this->paymentService = $paymentService;
    }

    /**
     * Entry point for Telegram Bot Webhook
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. Verify webhook secret
        $secret = $request->header('X-Telegram-Bot-Api-Secret-Token');
        if (!$this->botService->verifyWebhookSecret($secret)) {
            Log::warning('Telegram webhook invalid secret token received');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $update = $request->all();
        $updateId = $update['update_id'] ?? null;

        if (!$updateId) {
            return response()->json(['ok' => false, 'error' => 'Missing update_id'], 400);
        }

        // 2. Replay guard (idempotency)
        if (!$this->botService->recordUpdate($update)) {
            // Already processed or in progress
            return response()->json(['ok' => true, 'status' => 'duplicate_ignored']);
        }

        try {
            // 3. Handle pre_checkout_query (Must respond within 10s)
            if (isset($update['pre_checkout_query'])) {
                $query = $update['pre_checkout_query'];
                $preCheckoutResult = $this->paymentService->handlePreCheckout($query);

                $this->botService->answerPreCheckoutQuery(
                    $query['id'],
                    $preCheckoutResult['ok'],
                    $preCheckoutResult['error_message'] ?? null
                );

                $this->botService->markUpdateProcessed($updateId);
                return response()->json(['ok' => true]);
            }

            // 4. Handle successful_payment
            if (isset($update['message']['successful_payment'])) {
                $paymentData = $update['message']['successful_payment'];
                $fromId = $update['message']['from']['id'] ?? null;

                if ($fromId) {
                    $this->paymentService->handleSuccessfulPayment($paymentData, $fromId);

                    // Send confirmation message to user
                    $botToken = config('services.telegram.bot_token');
                    $miniAppUrl = config('services.telegram.mini_app_url', 'https://aikino.app');

                    Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $fromId,
                        'text' => "🎉 To'lovingiz muvaffaqiyatli qabul qilindi!\nMini App orqali tomoshani boshlashingiz mumkin.",
                        'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [
                                    ['text' => '🎬 AI Kinoni ochish', 'web_app' => ['url' => $miniAppUrl]],
                                ],
                            ],
                        ]),
                    ]);
                }

                $this->botService->markUpdateProcessed($updateId);
                return response()->json(['ok' => true]);
            }

            // 5. Handle bot messages (e.g. /start)
            if (isset($update['message']['text'])) {
                $chatId = $update['message']['chat']['id'];
                $text = trim($update['message']['text']);
                $from = $update['message']['from'];

                // Register/update user
                User::updateOrCreate(
                    ['telegram_id' => $from['id']],
                    [
                        'first_name' => $from['first_name'] ?? 'User',
                        'username' => $from['username'] ?? null,
                        'language' => substr($from['language_code'] ?? 'uz', 0, 2),
                        'last_seen_at' => now(),
                    ]
                );

                if (str_starts_with($text, '/start')) {
                    $botToken = config('services.telegram.bot_token');
                    $miniAppUrl = config('services.telegram.mini_app_url', 'https://aikino.app');

                    Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => "Salom! AI Kino 2.0 platformasiga xush kelibsiz.\nFaqat sun'iy intellekt tomonidan yaratilgan eksklyuziv filmlar va seriallarni quyidagi tugma orqali tomosha qiling:",
                        'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [
                                    ['text' => '🍿 Mini Appni ochish', 'web_app' => ['url' => $miniAppUrl]],
                                ],
                            ],
                        ]),
                    ]);
                }

                $this->botService->markUpdateProcessed($updateId);
                return response()->json(['ok' => true]);
            }

            $this->botService->markUpdateProcessed($updateId);
            return response()->json(['ok' => true]);
        } catch (Exception $e) {
            Log::error("Webhook error: " . $e->getMessage());
            $this->botService->markUpdateProcessed($updateId, 'failed', $e->getMessage());
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
