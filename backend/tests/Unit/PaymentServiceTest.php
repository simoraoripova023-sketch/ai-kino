<?php

namespace Tests\Unit;

use App\Models\AccessGrant;
use App\Models\Episode;
use App\Models\Payment;
use App\Models\PurchaseIntent;
use App\Models\User;
use App\Models\Work;
use App\Services\EntitlementService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;
    protected EntitlementService $entitlementService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entitlementService = new EntitlementService();
        $this->paymentService = new PaymentService($this->entitlementService);
    }

    public function test_create_intent_snapshots_price(): void
    {
        $user = User::create([
            'telegram_id' => 123456,
            'first_name' => 'Temur',
            'language' => 'uz',
        ]);

        $work = Work::create([
            'title' => 'AI Kino Film',
            'description' => 'Test',
            'format' => 'film',
            'style' => 'realistic',
            'language' => 'uz',
            'age_rating' => '12+',
            'poster_path' => '/posters/1.jpg',
            'status' => 'published',
            'pricing_mode' => 'work',
            'price_stars' => 35,
        ]);

        $intent = $this->paymentService->createIntent($user, 'work', $work->id);

        $this->assertNotNull($intent);
        $this->assertEquals(35, $intent->amount_stars);
        $this->assertEquals('XTR', $intent->currency);
        $this->assertEquals('pending', $intent->status);
    }

    public function test_pre_checkout_validation(): void
    {
        $user = User::create([
            'telegram_id' => 777888999,
            'first_name' => 'Nodir',
            'language' => 'uz',
        ]);

        $work = Work::create([
            'title' => 'Serial',
            'description' => 'Test',
            'format' => 'series',
            'style' => 'animation_3d',
            'language' => 'uz',
            'age_rating' => '6+',
            'poster_path' => '/p.jpg',
            'status' => 'published',
            'pricing_mode' => 'work',
            'price_stars' => 20,
        ]);

        $intent = $this->paymentService->createIntent($user, 'work', $work->id);

        // Valid query
        $validQuery = [
            'invoice_payload' => $intent->invoice_payload,
            'total_amount' => 20,
            'currency' => 'XTR',
            'from' => ['id' => 777888999],
        ];
        $res = $this->paymentService->handlePreCheckout($validQuery);
        $this->assertTrue($res['ok']);

        // Wrong amount
        $invalidQuery = $validQuery;
        $invalidQuery['total_amount'] = 99;
        $resInvalid = $this->paymentService->handlePreCheckout($invalidQuery);
        $this->assertFalse($resInvalid['ok']);
        $this->assertEquals('Narx mos kelmadi', $resInvalid['error_message']);
    }

    public function test_successful_payment_idempotency_and_grant(): void
    {
        $user = User::create([
            'telegram_id' => 999111222,
            'first_name' => 'Sardor',
            'language' => 'uz',
        ]);

        $work = Work::create([
            'title' => 'Film 3',
            'description' => 'Test',
            'format' => 'film',
            'style' => 'realistic',
            'language' => 'uz',
            'age_rating' => '12+',
            'poster_path' => '/p3.jpg',
            'status' => 'published',
            'pricing_mode' => 'work',
            'price_stars' => 40,
        ]);

        $intent = $this->paymentService->createIntent($user, 'work', $work->id);

        $successfulPayment = [
            'currency' => 'XTR',
            'total_amount' => 40,
            'invoice_payload' => $intent->invoice_payload,
            'telegram_payment_charge_id' => 'tg_charge_unique_abc123',
        ];

        // 1st delivery
        $payment1 = $this->paymentService->handleSuccessfulPayment($successfulPayment, 999111222);
        $this->assertNotNull($payment1);
        $this->assertEquals('paid', $payment1->status);

        // Verify grant was created
        $grant = AccessGrant::where('user_id', $user->id)
            ->where('target_type', 'work')
            ->where('target_id', $work->id)
            ->first();
        $this->assertNotNull($grant);
        $this->assertEquals('active', $grant->status);

        // 2nd delivery with identical charge ID (idempotent replay)
        $payment2 = $this->paymentService->handleSuccessfulPayment($successfulPayment, 999111222);
        $this->assertEquals($payment1->id, $payment2->id);

        // Total payments in DB should still be exactly 1
        $this->assertEquals(1, Payment::count());
        $this->assertEquals(1, AccessGrant::count());
    }
}
