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

        $botUsername = config('services.telegram.bot_username', 'aikino_bot');

        return response()->json([
            'data' => [
                'status' => 'delivered',
                'request_id' => $requestId,
                'message' => 'Video Telegram bot chatiga yuborildi',
                'telegram_message_id' => $delivery['result']['message_id'] ?? null,
                'bot_link' => "https://t.me/{$botUsername}",
                'video_url' => $episode->video_url,
                'episode' => [
                    'id' => $episode->id,
                    'number' => $episode->number,
                    'title' => $episode->title,
                ],
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

    /**
     * Direct video stream resolver for in-app HTML5 player
     */
    public function stream(Request $request, int $id)
    {
        $episode = Episode::with('work')->findOrFail($id);

        // 1. Direct custom video URL
        $rawUrl = $episode->getRawOriginal('video_url');
        if (!empty($rawUrl)) {
            return redirect()->away($rawUrl);
        }

        // 2. Resolve Telegram video file path if file_id exists
        if (!empty($episode->file_id) && !str_starts_with($episode->file_id, 'sample_')) {
            $botToken = config('services.telegram.bot_token');
            if ($botToken) {
                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(5)->get("https://api.telegram.org/bot{$botToken}/getFile", [
                        'file_id' => $episode->file_id,
                    ]);
                    $filePath = $response->json('result.file_path');
                    if ($filePath) {
                        return redirect()->away("https://api.telegram.org/file/bot{$botToken}/{$filePath}");
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Telegram getFile stream error for episode {$id}: " . $e->getMessage());
                }
            }
        }

        // 3. Fallback
        return redirect()->away('https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4');
    }
}
