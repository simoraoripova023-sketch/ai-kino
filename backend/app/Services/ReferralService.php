<?php

namespace App\Services;

use App\Models\ReferralLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    /**
     * Process referral binding
     */
    public function processReferral(User $user, string $code): array
    {
        // Strip ref_ or ref- if passed
        $cleanCode = strtoupper(preg_replace('/^ref[_-]/i', '', trim($code)));

        if (empty($cleanCode)) {
            return ['ok' => false, 'message' => 'Yaroqsiz referal kodi'];
        }

        // Already has referrer
        if ($user->referrer_id) {
            return ['ok' => false, 'message' => 'Siz allaqachon taklif orqali ulangan ekansiz'];
        }

        // Find referrer
        $referrer = User::where('referral_code', $cleanCode)->first();
        if (!$referrer) {
            return ['ok' => false, 'message' => 'Taklif qiluvchi foydalanuvchi topilmadi'];
        }

        // Prevent self-referral
        if ($referrer->id === $user->id) {
            return ['ok' => false, 'message' => 'O\'zingizni taklif qila olmaysiz'];
        }

        // Guard against duplicate log
        if (ReferralLog::where('referred_user_id', $user->id)->exists()) {
            return ['ok' => false, 'message' => 'Taklif bonusi allaqachon olingan'];
        }

        return DB::transaction(function () use ($referrer, $user) {
            $user->referrer_id = $referrer->id;
            $user->bonus_stars = (int) $user->bonus_stars + 2; // Welcome bonus
            $user->save();

            $referrer->referrals_count = (int) $referrer->referrals_count + 1;
            $referrer->bonus_stars = (int) $referrer->bonus_stars + 5; // Referrer reward
            $referrer->save();

            $log = ReferralLog::create([
                'referrer_id' => $referrer->id,
                'referred_user_id' => $user->id,
                'reward_stars' => 5,
                'referred_reward_stars' => 2,
            ]);

            // Notify referrer via Telegram if telegram_id exists
            if ($referrer->telegram_id) {
                $botToken = config('services.telegram.bot_token');
                $friendName = $user->first_name ?: ($user->username ? '@' . $user->username : 'Yangi do\'stingiz');
                try {
                    Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $referrer->telegram_id,
                        'text' => "🎉 **Tabriklaymiz!**\nSizning taklif havolangiz orqali **{$friendName}** Mini Appga qo'shildi!\n\n⭐️ Sizga: **+5 Stars** sovg'a qilindi\nJami Stars balansingiz: **{$referrer->bonus_stars}** Stars.",
                        'parse_mode' => 'Markdown',
                    ]);
                } catch (\Throwable $e) {
                    Log::warning("Could not send referral notification to referrer {$referrer->id}: " . $e->getMessage());
                }
            }

            return [
                'ok' => true,
                'message' => 'Taklif muvaffaqiyatli qabul qilindi! Sizga 2 Stars taqdim etildi.',
                'referrer_name' => $referrer->first_name ?: $referrer->username,
                'reward_stars' => 2,
            ];
        });
    }

    /**
     * Get user referral dashboard stats
     */
    public function getStats(User $user): array
    {
        $code = $user->ensureReferralCode();
        $botUsername = config('services.telegram.bot_username', 'Aimedias_bot');

        $miniAppUrl = "https://t.me/{$botUsername}/kino?startapp=ref_{$code}";
        $botStartUrl = "https://t.me/{$botUsername}?start=ref_{$code}";

        $referralList = ReferralLog::with('referredUser:id,first_name,username,created_at')
            ->where('referrer_id', $user->id)
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'name' => $log->referredUser->first_name ?? 'Foydalanuvchi',
                    'username' => $log->referredUser->username ?? null,
                    'reward_stars' => $log->reward_stars,
                    'date' => $log->created_at->format('d.m.Y H:i'),
                ];
            });

        return [
            'referral_code' => $code,
            'referral_link' => $miniAppUrl,
            'bot_link' => $botStartUrl,
            'referrals_count' => (int) $user->referrals_count,
            'bonus_stars' => (int) $user->bonus_stars,
            'stars_per_referral' => 5,
            'welcome_bonus' => 2,
            'recent_referrals' => $referralList,
        ];
    }
}
