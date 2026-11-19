<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_referral_stats(): void
    {
        $user = User::factory()->create([
            'telegram_id' => 111222333,
            'bonus_stars' => 10,
            'referrals_count' => 2,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/referrals/stats');

        $response->assertStatus(200)
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.bonus_stars', 10)
            ->assertJsonPath('data.referrals_count', 2);

        $this->assertNotNull($response->json('data.referral_code'));
        $this->assertStringContainsString('startapp=ref_', $response->json('data.referral_link'));
    }

    public function test_user_can_claim_valid_referral_code(): void
    {
        $referrer = User::factory()->create([
            'telegram_id' => 444555666,
            'bonus_stars' => 0,
            'referrals_count' => 0,
            'referral_code' => 'ABC12345',
        ]);

        $newUser = User::factory()->create([
            'telegram_id' => 777888999,
            'bonus_stars' => 0,
            'referrer_id' => null,
        ]);

        $response = $this->actingAs($newUser, 'sanctum')->postJson('/api/v1/referrals/claim', [
            'code' => 'ref_ABC12345',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('ok', true);

        $referrer->refresh();
        $newUser->refresh();

        $this->assertEquals(5, $referrer->bonus_stars);
        $this->assertEquals(1, $referrer->referrals_count);
        $this->assertEquals(2, $newUser->bonus_stars);
        $this->assertEquals($referrer->id, $newUser->referrer_id);
    }

    public function test_user_cannot_self_refer(): void
    {
        $user = User::factory()->create([
            'telegram_id' => 123123123,
            'referral_code' => 'MYCODE01',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/referrals/claim', [
            'code' => 'MYCODE01',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('ok', false);
    }

    public function test_user_cannot_be_referred_twice(): void
    {
        $referrer1 = User::factory()->create(['referral_code' => 'REFONE11']);
        $referrer2 = User::factory()->create(['referral_code' => 'REFTWO22']);

        $user = User::factory()->create([
            'referrer_id' => $referrer1->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/referrals/claim', [
            'code' => 'REFTWO22',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('ok', false);
    }
}
