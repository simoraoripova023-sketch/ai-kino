<?php

namespace App\Services;

use App\Models\TelegramUpdate;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    protected string $botToken;
    protected string $apiUrl;

    public function __construct(?string $botToken = null)
    {
        $this->botToken = $botToken ?? config('services.telegram.bot_token', '');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Verify incoming webhook secret token header
     */
    public function verifyWebhookSecret(?string $headerSecret): bool
    {
        $expected = config('services.telegram.webhook_secret', '');
        if (empty($expected)) {
            return true; // If not configured in dev/testing, allow
        }
        return hash_equals($expected, (string) $headerSecret);
    }

    /**
     * Record update for idempotency replay guard
     */
    public function recordUpdate(array $update): bool
    {
        $updateId = $update['update_id'] ?? null;
        if (!$updateId) {
            return false;
        }

        // Determine kind
        $kind = 'unknown';
        if (isset($update['message'])) $kind = 'message';
        elseif (isset($update['callback_query'])) $kind = 'callback_query';
        elseif (isset($update['pre_checkout_query'])) $kind = 'pre_checkout_query';
        elseif (isset($update['successful_payment'])) $kind = 'successful_payment';

        $existing = TelegramUpdate::find($updateId);
        if ($existing) {
            return false; // Already recorded (duplicate)
        }

        TelegramUpdate::create([
            'update_id' => $updateId,
            'kind' => $kind,
            'received_at' => now(),
            'status' => 'received',
        ]);

        return true;
    }

    /**
     * Mark update as processed
     */
    public function markUpdateProcessed(int $updateId, string $status = 'processed', ?string $error = null): void
    {
        TelegramUpdate::where('update_id', $updateId)->update([
            'status' => $status,
            'processed_at' => now(),
            'error' => $error,
        ]);
    }

    /**
     * Send or copy video message to user chat with protect_content=true
     */
    public function deliverVideo(int $chatId, int $storageChatId, int $storageMessageId, ?array $replyMarkup = null): array
    {
        $params = [
            'chat_id' => $chatId,
            'from_chat_id' => $storageChatId,
            'message_id' => $storageMessageId,
            'protect_content' => true,
        ];

        if ($replyMarkup) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }

        try {
            $response = Http::post("{$this->apiUrl}/copyMessage", $params);
            $data = $response->json();

            if (!$response->successful() || !($data['ok'] ?? false)) {
                $errCode = $data['error_code'] ?? $response->status();
                $errDesc = $data['description'] ?? 'Bot API xatosi';

                // Check 429 retry-after
                if ($errCode === 429) {
                    $retryAfter = $data['parameters']['retry_after'] ?? 5;
                    Log::warning("Telegram 429 Rate limited. Retry after: {$retryAfter}s");
                    return ['ok' => false, 'code' => 'RATE_LIMITED', 'retry_after' => $retryAfter];
                }

                if ($errCode === 403) {
                    Log::warning("Telegram bot blocked by user: {$chatId}");
                    return ['ok' => false, 'code' => 'BOT_BLOCKED', 'description' => $errDesc];
                }

                return ['ok' => false, 'code' => 'ERROR', 'description' => $errDesc];
            }

            return ['ok' => true, 'result' => $data['result'] ?? []];
        } catch (Exception $e) {
            Log::error("Video delivery exception: " . $e->getMessage());
            return ['ok' => false, 'code' => 'EXCEPTION', 'description' => $e->getMessage()];
        }
    }

    /**
     * Create Stars (XTR) invoice link for Telegram Mini App openInvoice
     */
    public function createInvoiceLink(string $title, string $description, string $payload, int $amountStars): array
    {
        $params = [
            'title' => $title,
            'description' => $description,
            'payload' => $payload,
            'currency' => 'XTR',
            'prices' => [
                [
                    'label' => $title,
                    'amount' => $amountStars,
                ],
            ],
        ];

        try {
            $response = Http::post("{$this->apiUrl}/createInvoiceLink", $params);
            $data = $response->json();
            return [
                'ok' => $response->successful() && ($data['ok'] ?? false),
                'link' => $data['result'] ?? null,
                'raw' => $data,
            ];
        } catch (Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send Stars (XTR) invoice to chat
     */
    public function sendStarsInvoice(int $chatId, string $title, string $description, string $payload, int $amountStars): array
    {
        $params = [
            'chat_id' => $chatId,
            'title' => $title,
            'description' => $description,
            'payload' => $payload,
            'currency' => 'XTR',
            'provider_token' => '', // Empty for Telegram Stars
            'prices' => [
                [
                    'label' => $title,
                    'amount' => $amountStars, // Integer XTR
                ],
            ],
        ];

        try {
            $response = Http::post("{$this->apiUrl}/sendInvoice", $params);
            return $response->json() ?? ['ok' => false];
        } catch (Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Answer pre-checkout query within 10 seconds
     */
    public function answerPreCheckoutQuery(string $preCheckoutQueryId, bool $ok, ?string $errorMessage = null): array
    {
        $params = [
            'pre_checkout_query_id' => $preCheckoutQueryId,
            'ok' => $ok,
        ];

        if (!$ok && $errorMessage) {
            $params['error_message'] = $errorMessage;
        }

        try {
            $response = Http::post("{$this->apiUrl}/answerPreCheckoutQuery", $params);
            return $response->json() ?? ['ok' => false];
        } catch (Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
