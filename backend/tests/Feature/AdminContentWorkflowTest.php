<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\UploadSession;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminContentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config([
            'services.telegram.bot_token' => 'dummy_token',
            'services.telegram.webhook_secret' => '',
        ]);

        $this->adminUser = User::create([
            'telegram_id' => 9999999,
            'first_name' => 'Admin Boss',
            'is_admin' => true,
            'language' => 'uz',
        ]);

        $this->regularUser = User::create([
            'telegram_id' => 1111111,
            'first_name' => 'Regular Tomoshabin',
            'is_admin' => false,
            'language' => 'uz',
        ]);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/v1/admin/analytics/overview');

        $response->assertStatus(403);
        $response->assertJsonPath('error.code', 'ADMIN_ACCESS_REQUIRED');
    }

    public function test_admin_work_creation_and_publish_validation(): void
    {
        // 1. Create work as draft
        $createRes = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/admin/works', [
            'title' => 'Yangi AI Kinosi',
            'description' => 'Ajoyib kino tavsifi',
            'format' => 'film',
            'style' => 'realistic',
            'language' => 'uz',
            'age_rating' => '12+',
            'poster_path' => 'https://example.com/poster.jpg',
            'pricing_mode' => 'work',
            'price_stars' => 30,
        ]);

        $createRes->assertStatus(201);
        $workId = $createRes->json('data.id');

        // 2. Try to publish without healthy episodes -> must fail with 422
        $failPublishRes = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/v1/admin/works/{$workId}/publish");

        $failPublishRes->assertStatus(422);
        $failPublishRes->assertJsonPath('error.code', 'NO_HEALTHY_EPISODES');

        // 3. Admin creates an upload session
        $sessionRes = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/admin/uploads/sessions', [
            'work_id' => $workId,
        ]);

        $sessionRes->assertStatus(201);
        $sessionId = $sessionRes->json('data.session_id');
        $this->assertNotNull($sessionId);

        // 4. Simulate Admin sending video to Telegram Bot with caption "/upload <session_id>"
        Http::fake([
            'https://api.telegram.org/botdummy_token/copyMessage' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 555],
            ], 200),
            'https://api.telegram.org/botdummy_token/sendMessage' => Http::response(['ok' => true], 200),
        ]);

        $uploadWebhookUpdate = [
            'update_id' => 7771,
            'message' => [
                'message_id' => 1234,
                'chat' => ['id' => 9999999],
                'from' => ['id' => 9999999],
                'caption' => "/upload {$sessionId}",
                'video' => [
                    'file_id' => 'tg_uploaded_file_abc',
                    'file_unique_id' => 'unique_abc',
                    'duration' => 600,
                    'file_size' => 10485760,
                ],
            ],
        ];

        $webhookRes = $this->postJson('/api/v1/telegram/webhook', $uploadWebhookUpdate);
        $webhookRes->assertStatus(200);

        // Verify session is completed and episode created
        $session = UploadSession::find($sessionId);
        $this->assertEquals('completed', $session->status);
        $this->assertDatabaseHas('episodes', [
            'work_id' => $workId,
            'file_id' => 'tg_uploaded_file_abc',
            'storage_message_id' => 555,
        ]);

        // 5. Now publish the work -> must succeed!
        $publishRes = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/v1/admin/works/{$workId}/publish");

        $publishRes->assertStatus(200);
        $this->assertDatabaseHas('works', [
            'id' => $workId,
            'status' => 'published',
        ]);

        // Verify audit log
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_id' => $this->adminUser->id,
            'action' => 'work_published',
            'entity_id' => $workId,
        ]);
    }

    public function test_admin_analytics_overview(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/admin/analytics/overview');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'users' => ['total', 'dau', 'mau', 'paying_users'],
                'catalog' => ['total_works', 'published_works', 'total_episodes'],
                'revenue' => ['gross_stars', 'refund_stars', 'currency'],
                'playback' => ['delivered_videos'],
            ],
        ]);
    }
}
