<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\UploadSession;
use App\Models\Work;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadSessionController extends Controller
{
    /**
     * Create an upload session for an admin to upload video via Telegram bot
     */
    public function createSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'work_id' => 'required|exists:works,id',
            'season_id' => 'nullable|exists:seasons,id',
        ]);

        $work = Work::findOrFail($validated['work_id']);
        $admin = $request->user();

        $session = UploadSession::create([
            'id' => Str::uuid()->toString(),
            'admin_user_id' => $admin->id,
            'work_id' => $work->id,
            'season_id' => $validated['season_id'] ?? null,
            'status' => 'pending',
            'expires_at' => now()->addHours(2),
        ]);

        $botUsername = config('services.telegram.bot_username', 'aikino_bot');

        return response()->json([
            'data' => [
                'session_id' => $session->id,
                'work_id' => $work->id,
                'work_title' => $work->title,
                'expires_at' => $session->expires_at,
                'telegram_command' => "/upload {$session->id}",
                'instructions' => "Telegram botga (@{$botUsername}) videoni yuboring va xabar izohiga (caption) yoki alohida xabar sifatida `/upload {$session->id}` buyrug'ini bering.",
            ],
        ], 201);
    }

    /**
     * List upload sessions
     */
    public function index(): JsonResponse
    {
        $sessions = UploadSession::with(['work'])
            ->latest()
            ->paginate(20);

        return response()->json(['data' => $sessions]);
    }
}
