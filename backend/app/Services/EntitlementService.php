<?php

namespace App\Services;

use App\Models\AccessGrant;
use App\Models\Episode;
use App\Models\User;
use App\Models\Work;

class EntitlementService
{
    /**
     * Determine access state and pricing for an episode
     *
     * @param User|null $user
     * @param Episode $episode
     * @return array
     */
    public function getEpisodeAccess(?User $user, Episode $episode): array
    {
        $work = $episode->work ?? Work::find($episode->work_id);

        // 0. Admin user always has full free access to view and play everything
        if ($user && $user->is_admin) {
            return [
                'accessible' => true,
                'access_state' => 'owned',
                'price_stars' => 0,
                'target_type' => null,
                'target_id' => null,
            ];
        }

        // 1. Check publish status
        if ($work->status !== 'published' || ($work->published_at && $work->published_at->isFuture())) {
            return [
                'accessible' => false,
                'access_state' => 'unpublished',
                'reason' => 'Asar hali e\'lon qilinmagan',
            ];
        }

        if ($episode->status !== 'published' || ($episode->publish_at && $episode->publish_at->isFuture())) {
            return [
                'accessible' => false,
                'access_state' => 'unpublished',
                'reason' => 'Qism hali e\'lon qilinmagan',
            ];
        }

        // 2. Pricing mode is completely free
        if ($work->pricing_mode === 'free') {
            return [
                'accessible' => true,
                'access_state' => 'free',
                'price_stars' => 0,
                'target_type' => null,
                'target_id' => null,
            ];
        }

        // 3. Multi-episode free 1st episode rule:
        // Ko'p qismli asarning 1-qismi har doim bepul.
        // Istisno: Bitta epizodli to'liq filmning butun fayli preview sababli bepul bo'lmaydi!
        $totalEpisodes = $work->episodes()->where('status', 'published')->count();
        $isMultiEpisode = $work->format === 'series' || $totalEpisodes > 1;

        if ($isMultiEpisode && $episode->number === 1) {
            return [
                'accessible' => true,
                'access_state' => 'free',
                'price_stars' => 0,
                'target_type' => null,
                'target_id' => null,
            ];
        }

        // Check if episode specifically marked as preview
        if ($episode->is_preview) {
            return [
                'accessible' => true,
                'access_state' => 'free',
                'price_stars' => 0,
                'target_type' => null,
                'target_id' => null,
            ];
        }

        // 4. Check user active entitlements if user is authenticated
        if ($user) {
            // Whole work grant gives access to ALL episodes
            $hasWorkGrant = AccessGrant::where('user_id', $user->id)
                ->where('target_type', 'work')
                ->where('target_id', $work->id)
                ->where('status', 'active')
                ->exists();

            if ($hasWorkGrant) {
                return [
                    'accessible' => true,
                    'access_state' => 'owned',
                    'price_stars' => 0,
                    'target_type' => 'work',
                    'target_id' => $work->id,
                ];
            }

            // Episode specific grant
            $hasEpisodeGrant = AccessGrant::where('user_id', $user->id)
                ->where('target_type', 'episode')
                ->where('target_id', $episode->id)
                ->where('status', 'active')
                ->exists();

            if ($hasEpisodeGrant) {
                return [
                    'accessible' => true,
                    'access_state' => 'owned',
                    'price_stars' => 0,
                    'target_type' => 'episode',
                    'target_id' => $episode->id,
                ];
            }
        }

        // 5. Locked - calculate required target and stars price
        $targetType = $work->pricing_mode === 'work' ? 'work' : 'episode';
        $targetId = $targetType === 'work' ? $work->id : $episode->id;
        $priceStars = $targetType === 'work' ? $work->price_stars : ($episode->price_stars ?? $work->price_stars);

        return [
            'accessible' => false,
            'access_state' => 'locked',
            'price_stars' => (int) $priceStars,
            'target_type' => $targetType,
            'target_id' => $targetId,
        ];
    }
}
