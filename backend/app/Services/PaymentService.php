<?php

namespace App\Services;

use App\Models\AccessGrant;
use App\Models\AdminAuditLog;
use App\Models\Episode;
use App\Models\Payment;
use App\Models\PurchaseIntent;
use App\Models\User;
use App\Models\Work;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    protected EntitlementService $entitlementService;

    public function __construct(EntitlementService $entitlementService)
    {
        $this->entitlementService = $entitlementService;
    }

    /**
     * Create a purchase intent with snapshot price
     */
    public function createIntent(User $user, string $targetType, int $targetId): PurchaseIntent
    {
        if (!in_array($targetType, ['work', 'episode'])) {
            throw new Exception('Noto\'g\'ri target_type: faqat work yoki episode ruxsat etilgan', 400);
        }

        $amountStars = 0;

        if ($targetType === 'work') {
            $work = Work::findOrFail($targetId);
            if ($work->status !== 'published') {
                throw new Exception('Ushbu asar hozircha mavjud emas', 404);
            }
            if ($work->pricing_mode === 'free') {
                throw new Exception('Ushbu asar bepul', 400);
            }

            // Check if already owned
            $existingGrant = AccessGrant::where('user_id', $user->id)
                ->where('target_type', 'work')
                ->where('target_id', $work->id)
                ->where('status', 'active')
                ->first();

            if ($existingGrant) {
                throw new Exception('Ushbu asar allaqachon sotib olingan', 409);
            }

            $amountStars = (int) $work->price_stars;
        } else {
            $episode = Episode::with('work')->findOrFail($targetId);
            if ($episode->status !== 'published') {
                throw new Exception('Ushbu qism hozircha mavjud emas', 404);
            }

            $access = $this->entitlementService->getEpisodeAccess($user, $episode);
            if ($access['accessible']) {
                throw new Exception('Ushbu qismga allaqachon ruxsat mavjud', 409);
            }

            $amountStars = (int) $access['price_stars'];
        }

        if ($amountStars <= 0) {
            throw new Exception('Narx belgilanmagan yoki noto\'g\'ri', 400);
        }

        $payload = Str::uuid()->toString() . '_' . time();

        return PurchaseIntent::create([
            'user_id' => $user->id,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'amount_stars' => $amountStars,
            'currency' => 'XTR',
            'status' => 'pending',
            'invoice_payload' => $payload,
            'expires_at' => now()->addMinutes(30),
        ]);
    }

    /**
     * Handle Telegram pre_checkout_query (must answer within 10 seconds)
     */
    public function handlePreCheckout(array $preCheckoutQuery): array
    {
        $payload = $preCheckoutQuery['invoice_payload'] ?? null;
        $totalAmount = (int) ($preCheckoutQuery['total_amount'] ?? 0);
        $currency = $preCheckoutQuery['currency'] ?? '';
        $fromId = $preCheckoutQuery['from']['id'] ?? null;

        if ($currency !== 'XTR') {
            return ['ok' => false, 'error_message' => 'Faqat Telegram Stars (XTR) qabul qilinadi'];
        }

        $intent = PurchaseIntent::where('invoice_payload', $payload)->first();
        if (!$intent) {
            return ['ok' => false, 'error_message' => 'To\'lov niyati topilmadi'];
        }

        if ($intent->status !== 'pending') {
            return ['ok' => false, 'error_message' => 'Ushbu to\'lov muddati tugagan yoki allaqachon bajarilgan'];
        }

        if ($intent->expires_at->isPast()) {
            $intent->update(['status' => 'expired']);
            return ['ok' => false, 'error_message' => 'To\'lov muddati tugadi'];
        }

        if ($intent->amount_stars !== $totalAmount) {
            return ['ok' => false, 'error_message' => 'Narx mos kelmadi'];
        }

        $user = User::where('telegram_id', $fromId)->first();
        if (!$user || $user->id !== $intent->user_id) {
            return ['ok' => false, 'error_message' => 'Foydalanuvchi identifikatori mos emas'];
        }

        return ['ok' => true];
    }

    /**
     * Handle Telegram successful_payment with atomic idempotency
     */
    public function handleSuccessfulPayment(array $successfulPayment, int $telegramUserId): Payment
    {
        $chargeId = $successfulPayment['telegram_payment_charge_id'] ?? null;
        $payload = $successfulPayment['invoice_payload'] ?? null;
        $totalAmount = (int) ($successfulPayment['total_amount'] ?? 0);
        $currency = $successfulPayment['currency'] ?? 'XTR';

        if (!$chargeId) {
            throw new Exception('Telegram charge ID mavjud emas', 400);
        }

        // Idempotency check: if this charge_id was already processed, return existing payment
        $existingPayment = Payment::where('telegram_payment_charge_id', $chargeId)->first();
        if ($existingPayment) {
            return $existingPayment;
        }

        $intent = PurchaseIntent::where('invoice_payload', $payload)->firstOrFail();
        $user = User::where('telegram_id', $telegramUserId)->firstOrFail();

        return DB::transaction(function () use ($intent, $user, $chargeId, $totalAmount, $currency, $successfulPayment) {
            // Lock intent row
            $intent = PurchaseIntent::where('id', $intent->id)->lockForUpdate()->first();

            // Create Payment record
            $payment = Payment::create([
                'intent_id' => $intent->id,
                'user_id' => $user->id,
                'telegram_payment_charge_id' => $chargeId,
                'amount_stars' => $totalAmount,
                'currency' => $currency,
                'paid_at' => now(),
                'status' => 'paid',
                'raw_event_reference' => $successfulPayment,
            ]);

            // Mark intent as paid
            $intent->update(['status' => 'paid']);

            // Grant entitlement
            AccessGrant::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'target_type' => $intent->target_type,
                    'target_id' => $intent->target_id,
                ],
                [
                    'source_type' => 'purchase',
                    'source_id' => $chargeId,
                    'status' => 'active',
                    'granted_at' => now(),
                ]
            );

            // Audit log
            AdminAuditLog::create([
                'actor_id' => $user->id,
                'action' => 'payment_success',
                'entity_type' => $intent->target_type,
                'entity_id' => $intent->target_id,
                'old_values' => ['intent_id' => $intent->id],
                'new_values' => ['charge_id' => $chargeId, 'amount_stars' => $totalAmount],
                'created_at' => now(),
            ]);

            return $payment;
        });
    }
}
