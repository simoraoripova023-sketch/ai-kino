<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\AnalyticsEvent;
use App\Models\Episode;
use App\Models\Payment;
use App\Models\User;
use App\Models\Work;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAnalyticsController extends Controller
{
    /**
     * Get platform overview metrics
     */
    public function overview(): JsonResponse
    {
        $totalUsers = User::count();
        $dau = User::where('last_seen_at', '>=', now()->startOfDay())->count();
        $mau = User::where('last_seen_at', '>=', now()->subDays(30))->count();

        $totalWorks = Work::count();
        $publishedWorks = Work::where('status', 'published')->count();
        $totalEpisodes = Episode::count();
        $publishedEpisodes = Episode::where('status', 'published')->count();

        $grossStars = (int) Payment::where('status', 'paid')->sum('amount_stars');
        $refundStars = (int) Payment::where('status', 'refunded')->sum('amount_stars');
        $totalPayments = Payment::where('status', 'paid')->count();
        $payingUsers = Payment::where('status', 'paid')->distinct('user_id')->count('user_id');

        $deliveredVideos = AnalyticsEvent::where('event_name', 'episode_delivered')->count();

        // Calculate paywall conversion: paying users / total users
        $conversionRate = $totalUsers > 0 ? round(($payingUsers / $totalUsers) * 100, 2) : 0;

        return response()->json([
            'data' => [
                'users' => [
                    'total' => $totalUsers,
                    'dau' => $dau,
                    'mau' => $mau,
                    'paying_users' => $payingUsers,
                    'conversion_rate_percent' => $conversionRate,
                ],
                'catalog' => [
                    'total_works' => $totalWorks,
                    'published_works' => $publishedWorks,
                    'total_episodes' => $totalEpisodes,
                    'published_episodes' => $publishedEpisodes,
                ],
                'revenue' => [
                    'gross_stars' => $grossStars,
                    'refund_stars' => $refundStars,
                    'net_stars' => $grossStars - $refundStars,
                    'total_transactions' => $totalPayments,
                    'currency' => 'XTR',
                ],
                'playback' => [
                    'delivered_videos' => $deliveredVideos,
                ],
            ],
        ]);
    }

    /**
     * Get payments history
     */
    public function payments(Request $request): JsonResponse
    {
        $payments = Payment::with(['user', 'intent'])
            ->latest()
            ->paginate(20);

        return response()->json(['data' => $payments]);
    }

    /**
     * Get audit logs
     */
    public function auditLogs(Request $request): JsonResponse
    {
        $logs = AdminAuditLog::latest()->paginate(50);
        return response()->json(['data' => $logs]);
    }
}
