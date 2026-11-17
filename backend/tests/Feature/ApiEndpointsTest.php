<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['services.telegram.bot_token' => 'dummy_token']);
    }

    public function test_get_genres_returns_active_genres(): void
    {
        $response = $this->getJson('/api/v1/genres');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['id', 'slug', 'name_uz', 'name_ru']]]);
        $this->assertCount(5, $response->json('data'));
    }

    public function test_get_works_filtering_by_format(): void
    {
        $response = $this->getJson('/api/v1/works?format=film');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'pagination']);

        foreach ($response->json('data') as $work) {
            $this->assertEquals('film', $work['format']);
        }
    }

    public function test_get_work_detail_returns_episodes_with_access_state(): void
    {
        $work = Work::first();
        $response = $this->getJson("/api/v1/works/{$work->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'work' => ['id', 'title', 'format', 'pricing_mode'],
                'episodes' => [['id', 'number', 'access_state']],
            ],
        ]);
    }

    public function test_play_locked_episode_returns_403_payment_required(): void
    {
        $user = User::create([
            'telegram_id' => 11223344,
            'first_name' => 'Test User',
            'language' => 'uz',
        ]);

        // Find locked episode (e.g. episode 2 of series)
        $lockedEpisode = Episode::whereHas('work', function ($q) {
            $q->where('pricing_mode', '!=', 'free');
        })->where('number', '>', 1)->first();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/episodes/{$lockedEpisode->id}/play");

        $response->assertStatus(403);
        $response->assertJsonPath('error.code', 'PAYMENT_REQUIRED');
    }

    public function test_play_free_episode_delivers_video_and_updates_progress(): void
    {
        Http::fake([
            'https://api.telegram.org/botdummy_token/copyMessage' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 999],
            ], 200),
        ]);

        $user = User::create([
            'telegram_id' => 99887766,
            'first_name' => 'Free Watcher',
            'language' => 'uz',
        ]);

        // Multi-episode 1st episode is free
        $freeEpisode = Episode::whereHas('work', function ($q) {
            $q->where('format', 'series');
        })->where('number', 1)->first();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/episodes/{$freeEpisode->id}/play");

        $response->assertStatus(202);
        $response->assertJsonPath('data.status', 'delivered');

        // Check watch progress was updated
        $this->assertDatabaseHas('watch_progress', [
            'user_id' => $user->id,
            'work_id' => $freeEpisode->work_id,
            'last_episode_id' => $freeEpisode->id,
        ]);
    }

    public function test_purchase_intent_and_webhook_payment_flow(): void
    {
        Http::fake([
            'https://api.telegram.org/botdummy_token/sendInvoice' => Http::response(['ok' => true], 200),
            'https://api.telegram.org/botdummy_token/answerPreCheckoutQuery' => Http::response(['ok' => true], 200),
            'https://api.telegram.org/botdummy_token/sendMessage' => Http::response(['ok' => true], 200),
        ]);

        $user = User::create([
            'telegram_id' => 44556677,
            'first_name' => 'Buyer',
            'language' => 'uz',
        ]);

        $work = Work::where('pricing_mode', 'work')->first();

        // 1. Create Purchase Intent
        $intentRes = $this->actingAs($user, 'sanctum')->postJson('/api/v1/purchase-intents', [
            'target_type' => 'work',
            'target_id' => $work->id,
        ]);

        $intentRes->assertStatus(201);
        $intentId = $intentRes->json('data.intent_id');
        $this->assertNotNull($intentId);

        $intent = \App\Models\PurchaseIntent::find($intentId);

        // 2. Telegram Webhook: pre_checkout_query
        $preCheckoutUpdate = [
            'update_id' => 5001,
            'pre_checkout_query' => [
                'id' => 'pcq_test_1',
                'from' => ['id' => 44556677],
                'currency' => 'XTR',
                'total_amount' => $intent->amount_stars,
                'invoice_payload' => $intent->invoice_payload,
            ],
        ];

        $pcRes = $this->postJson('/api/v1/telegram/webhook', $preCheckoutUpdate);
        $pcRes->assertStatus(200);

        // 3. Telegram Webhook: successful_payment
        $successfulPaymentUpdate = [
            'update_id' => 5002,
            'message' => [
                'chat' => ['id' => 44556677],
                'from' => ['id' => 44556677],
                'successful_payment' => [
                    'currency' => 'XTR',
                    'total_amount' => $intent->amount_stars,
                    'invoice_payload' => $intent->invoice_payload,
                    'telegram_payment_charge_id' => 'tg_charge_integration_1',
                ],
            ],
        ];

        $spRes = $this->postJson('/api/v1/telegram/webhook', $successfulPaymentUpdate);
        $spRes->assertStatus(200);

        // 4. Verify access grant
        $this->assertDatabaseHas('access_grants', [
            'user_id' => $user->id,
            'target_type' => 'work',
            'target_id' => $work->id,
            'status' => 'active',
        ]);
    }
}
