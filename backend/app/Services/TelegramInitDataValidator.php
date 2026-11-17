<?php

namespace App\Services;

use Exception;

class TelegramInitDataValidator
{
    protected string $botToken;
    protected int $maxAgeSeconds;

    public function __construct(?string $botToken = null, int $maxAgeSeconds = 300)
    {
        $this->botToken = $botToken ?? config('services.telegram.bot_token', '');
        $this->maxAgeSeconds = $maxAgeSeconds;
    }

    /**
     * Validates raw initData string received from Telegram WebApp
     *
     * @param string $initData
     * @return array
     * @throws Exception
     */
    public function validate(string $initData): array
    {
        if (empty($initData)) {
            throw new Exception('initData parametri bo\'sh', 401);
        }

        if (empty($this->botToken)) {
            throw new Exception('Telegram Bot Token sozlanmagan', 500);
        }

        parse_str($initData, $data);

        if (!isset($data['hash'])) {
            throw new Exception('initData ichida hash mavjud emas', 401);
        }

        $receivedHash = $data['hash'];
        unset($data['hash']);

        // Check auth_date for expiration (default: 5 minutes)
        if (!isset($data['auth_date'])) {
            throw new Exception('initData ichida auth_date mavjud emas', 401);
        }

        $authDate = (int) $data['auth_date'];
        $now = time();

        if (abs($now - $authDate) > $this->maxAgeSeconds) {
            throw new Exception('initData muddati o\'tgan (expired)', 401);
        }

        // Sort data by keys alphabetically
        ksort($data);

        $dataCheckArr = [];
        foreach ($data as $key => $value) {
            $dataCheckArr[] = "{$key}={$value}";
        }
        $dataCheckString = implode("\n", $dataCheckArr);

        // Telegram standard: HMAC-SHA256 with key "WebAppData" against bot token
        $secretKey = hash_hmac('sha256', $this->botToken, 'WebAppData', true);
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        // Constant-time hash comparison
        if (!hash_equals($calculatedHash, $receivedHash)) {
            throw new Exception('Noto\'g\'ri Telegram imzosi (invalid hash)', 401);
        }

        // Parse user data if present
        $user = null;
        if (isset($data['user'])) {
            $user = json_decode($data['user'], true);
        }

        return [
            'valid' => true,
            'user' => $user,
            'auth_date' => $authDate,
            'query_id' => $data['query_id'] ?? null,
            'raw_data' => $data,
        ];
    }
}
