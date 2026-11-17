<?php

namespace Tests\Unit;

use App\Models\TelegramUpdate;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramBotServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TelegramBotService $botService;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.telegram.bot_token' => 'dummy_token_123']);
        $this->botService = new TelegramBotService('dummy_token_123');
    }

    public function test_record_update_prevents_duplicate_replays(): void
    {
        $update = [
            'update_id' => 987654,
            'message' => ['text' => '/start'],
        ];

        // 1st time: accepted
        $first = $this->botService->recordUpdate($update);
        $this->assertTrue($first);
        $this->assertDatabaseHas('telegram_updates', ['update_id' => 987654]);

        // 2nd time: rejected (duplicate replay)
        $second = $this->botService->recordUpdate($update);
        $this->assertFalse($second);
    }

    public function test_deliver_video_calls_copy_message_with_protect_content(): void
    {
        Http::fake([
            'https://api.telegram.org/botdummy_token_123/copyMessage' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 888],
            ], 200),
        ]);

        $res = $this->botService->deliverVideo(12345, -100199999, 42);

        $this->assertTrue($res['ok']);
        $this->assertEquals(888, $res['result']['message_id']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.telegram.org/botdummy_token_123/copyMessage' &&
                   $request['chat_id'] === 12345 &&
                   $request['from_chat_id'] === -100199999 &&
                   $request['message_id'] === 42 &&
                   $request['protect_content'] === true;
        });
    }

    public function test_deliver_video_handles_429_rate_limit(): void
    {
        Http::fake([
            'https://api.telegram.org/botdummy_token_123/copyMessage' => Http::response([
                'ok' => false,
                'error_code' => 429,
                'description' => 'Too Many Requests',
                'parameters' => ['retry_after' => 12],
            ], 429),
        ]);

        $res = $this->botService->deliverVideo(12345, -100199999, 42);

        $this->assertFalse($res['ok']);
        $this->assertEquals('RATE_LIMITED', $res['code']);
        $this->assertEquals(12, $res['retry_after']);
    }

    public function test_send_stars_invoice_uses_xtr_and_empty_provider(): void
    {
        Http::fake([
            'https://api.telegram.org/botdummy_token_123/sendInvoice' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 777],
            ], 200),
        ]);

        $res = $this->botService->sendStarsInvoice(12345, 'Film Access', 'Full work', 'payload_123', 25);

        $this->assertTrue($res['ok']);

        Http::assertSent(function ($request) {
            return $request['currency'] === 'XTR' &&
                   $request['provider_token'] === '' &&
                   $request['prices'][0]['amount'] === 25;
        });
    }
}
