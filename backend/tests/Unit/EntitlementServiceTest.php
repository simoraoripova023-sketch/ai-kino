<?php

namespace Tests\Unit;

use App\Models\AccessGrant;
use App\Models\Episode;
use App\Models\User;
use App\Models\Work;
use App\Services\EntitlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntitlementServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EntitlementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EntitlementService();
    }

    public function test_multi_episode_series_first_episode_is_free(): void
    {
        $work = Work::create([
            'title' => 'AI Serial',
            'description' => 'Test serial',
            'format' => 'series',
            'style' => 'animation_3d',
            'language' => 'uz',
            'age_rating' => '12+',
            'poster_path' => '/posters/serial.jpg',
            'status' => 'published',
            'pricing_mode' => 'episode',
            'price_stars' => 10,
        ]);

        $ep1 = Episode::create([
            'work_id' => $work->id,
            'number' => 1,
            'title' => '1-qism',
            'file_id' => 'tg_file_1',
            'storage_chat_id' => -1001,
            'storage_message_id' => 10,
            'status' => 'published',
        ]);

        $ep2 = Episode::create([
            'work_id' => $work->id,
            'number' => 2,
            'title' => '2-qism',
            'file_id' => 'tg_file_2',
            'storage_chat_id' => -1001,
            'storage_message_id' => 11,
            'status' => 'published',
            'price_stars' => 15,
        ]);

        $access1 = $this->service->getEpisodeAccess(null, $ep1);
        $this->assertTrue($access1['accessible']);
        $this->assertEquals('free', $access1['access_state']);

        $access2 = $this->service->getEpisodeAccess(null, $ep2);
        $this->assertFalse($access2['accessible']);
        $this->assertEquals('locked', $access2['access_state']);
        $this->assertEquals(15, $access2['price_stars']);
    }

    public function test_single_episode_paid_film_is_not_free(): void
    {
        $work = Work::create([
            'title' => 'AI To\'liq Metrajli Film',
            'description' => 'Bitta qismli pullik kino',
            'format' => 'film',
            'style' => 'realistic',
            'language' => 'uz',
            'age_rating' => '16+',
            'poster_path' => '/posters/film.jpg',
            'status' => 'published',
            'pricing_mode' => 'work',
            'price_stars' => 50,
        ]);

        $ep1 = Episode::create([
            'work_id' => $work->id,
            'number' => 1,
            'title' => 'Film',
            'file_id' => 'tg_film_file',
            'storage_chat_id' => -1001,
            'storage_message_id' => 20,
            'status' => 'published',
            'is_preview' => false,
        ]);

        // Single episode paid film: butun fayli bepul preview bo'lmasligi kerak
        $access = $this->service->getEpisodeAccess(null, $ep1);
        $this->assertFalse($access['accessible']);
        $this->assertEquals('locked', $access['access_state']);
        $this->assertEquals(50, $access['price_stars']);
        $this->assertEquals('work', $access['target_type']);
    }

    public function test_user_with_work_grant_has_access_to_all_episodes(): void
    {
        $user = User::create([
            'telegram_id' => 555666777,
            'first_name' => 'Dilshod',
            'language' => 'uz',
        ]);

        $work = Work::create([
            'title' => 'AI Serial 2',
            'description' => 'Test',
            'format' => 'series',
            'style' => 'animation_2d',
            'language' => 'uz',
            'age_rating' => '6+',
            'poster_path' => '/posters/s2.jpg',
            'status' => 'published',
            'pricing_mode' => 'work',
            'price_stars' => 100,
        ]);

        $ep2 = Episode::create([
            'work_id' => $work->id,
            'number' => 2,
            'file_id' => 'tg_file_ep2',
            'storage_chat_id' => -1001,
            'storage_message_id' => 30,
            'status' => 'published',
        ]);

        // Create work access grant
        AccessGrant::create([
            'user_id' => $user->id,
            'target_type' => 'work',
            'target_id' => $work->id,
            'source_type' => 'purchase',
            'source_id' => 'charge_test_123',
            'status' => 'active',
            'granted_at' => now(),
        ]);

        $access = $this->service->getEpisodeAccess($user, $ep2);
        $this->assertTrue($access['accessible']);
        $this->assertEquals('owned', $access['access_state']);
    }
}
