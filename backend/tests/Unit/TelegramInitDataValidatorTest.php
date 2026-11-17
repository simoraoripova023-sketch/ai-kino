<?php

namespace Tests\Unit;

use App\Services\TelegramInitDataValidator;
use Exception;
use PHPUnit\Framework\TestCase;

class TelegramInitDataValidatorTest extends TestCase
{
    protected string $botToken = '123456789:ABCdefGHIjklMNOpqrSTUvwxYZ';

    protected function generateValidInitData(array $userData, int $authDate, string $botToken): string
    {
        $params = [
            'auth_date' => (string) $authDate,
            'query_id' => 'AAHdF6IQAAAAAN0XohDhrOrc',
            'user' => json_encode($userData),
        ];

        ksort($params);
        $dataCheckArr = [];
        foreach ($params as $key => $value) {
            $dataCheckArr[] = "{$key}={$value}";
        }
        $dataCheckString = implode("\n", $dataCheckArr);

        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        $params['hash'] = $hash;
        return http_build_query($params);
    }

    public function test_valid_init_data_passes_verification(): void
    {
        $validator = new TelegramInitDataValidator($this->botToken);
        $userData = [
            'id' => 987654321,
            'first_name' => 'Ali',
            'username' => 'ali_user',
            'language_code' => 'uz',
        ];

        $initData = $this->generateValidInitData($userData, time(), $this->botToken);
        $result = $validator->validate($initData);

        $this->assertTrue($result['valid']);
        $this->assertEquals(987654321, $result['user']['id']);
        $this->assertEquals('Ali', $result['user']['first_name']);
        $this->assertEquals('ali_user', $result['user']['username']);
    }

    public function test_tampered_init_data_fails_with_invalid_hash(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Noto\'g\'ri Telegram imzosi');

        $validator = new TelegramInitDataValidator($this->botToken);
        $userData = ['id' => 111, 'first_name' => 'Hacker'];

        $initData = $this->generateValidInitData($userData, time(), $this->botToken);
        // Tamper with data: change user ID
        $tamperedData = str_replace('111', '999', $initData);

        $validator->validate($tamperedData);
    }

    public function test_expired_init_data_fails(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('initData muddati o\'tgan');

        $validator = new TelegramInitDataValidator($this->botToken, 300);
        $userData = ['id' => 123, 'first_name' => 'Bobur'];

        // 10 minutes ago (> 300s)
        $expiredTime = time() - 600;
        $initData = $this->generateValidInitData($userData, $expiredTime, $this->botToken);

        $validator->validate($initData);
    }

    public function test_missing_hash_fails(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('hash mavjud emas');

        $validator = new TelegramInitDataValidator($this->botToken);
        $validator->validate('auth_date=' . time() . '&user=%7B%22id%22%3A123%7D');
    }
}
