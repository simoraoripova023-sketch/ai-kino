<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\Episode;
use App\Models\WatchProgress;
use App\Services\EntitlementService;
use App\Services\TelegramBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlaybackController extends Controller
{
    protected EntitlementService $entitlementService;
    protected TelegramBotService $botService;

    public function __construct(EntitlementService $entitlementService, TelegramBotService $botService)
    {
        $this->entitlementService = $entitlementService;
        $this->botService = $botService;
    }

    /**
     * Request playback of an episode in Telegram Bot chat
     */
    public function play(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $episode = Episode::with('work')->findOrFail($id);

        // Check entitlement
        $access = $this->entitlementService->getEpisodeAccess($user, $episode);
        if (!$access['accessible']) {
            return response()->json([
                'error' => [
                    'code' => 'PAYMENT_REQUIRED',
                    'message' => 'Ushbu qism pullik',
                    'details' => [
                        'target_type' => $access['target_type'],
                        'target_id' => $access['target_id'],
                        'price_stars' => $access['price_stars'],
                    ],
                ],
            ], 403);
        }

        $requestId = Str::uuid()->toString();

        // Deliver video to user's Telegram chat
        $delivery = $this->botService->deliverVideo(
            $user->telegram_id,
            $episode->storage_chat_id,
            $episode->storage_message_id
        );

        if (!$delivery['ok']) {
            if (($delivery['code'] ?? '') === 'BOT_BLOCKED') {
                return response()->json([
                    'error' => [
                        'code' => 'BOT_UNAVAILABLE',
                        'message' => 'Bot bloklangan yoki ishga tushirilmagan. Iltimos, avval botda /start buyrug\'ini bosing.',
                    ],
                ], 409);
            }

            return response()->json([
                'error' => [
                    'code' => 'PLAYBACK_ERROR',
                    'message' => 'Videoni yetkazishda xatolik yuz berdi: ' . ($delivery['description'] ?? ''),
                ],
            ], 500);
        }

        // Update watch progress (last_episode_id)
        WatchProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'work_id' => $episode->work_id,
            ],
            [
                'last_episode_id' => $episode->id,
                'player_message_id' => $delivery['result']['message_id'] ?? null,
                'opened_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Log analytics event
        AnalyticsEvent::create([
            'user_id' => $user->id,
            'event_name' => 'episode_delivered',
            'work_id' => $episode->work_id,
            'episode_id' => $episode->id,
            'properties' => [
                'source' => $request->input('source', 'mini_app'),
            ],
            'occurred_at' => now(),
            'request_id' => $requestId,
        ]);

        return response()->json([
            'data' => [
                'status' => 'delivered',
                'request_id' => $requestId,
                'message' => 'Video Telegram bot chatiga yuborildi',
            ],
        ], 202);
    }

    /**
     * Get continue watching progress list
     */
    public function progress(Request $request): JsonResponse
    {
        $user = $request->user();

        $progress = WatchProgress::with(['work', 'lastEpisode'])
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'work_id' => $p->work_id,
                    'work_title' => $p->work->title ?? '',
                    'poster_path' => $p->work->poster_path ?? '',
                    'last_episode_id' => $p->last_episode_id,
                    'last_episode_number' => $p->lastEpisode->number ?? 1,
                    'last_episode_title' => $p->lastEpisode->title ?? '',
                    'updated_at' => $p->updated_at,
                ];
            });

        return response()->json(['data' => $progress]);
    }
}
