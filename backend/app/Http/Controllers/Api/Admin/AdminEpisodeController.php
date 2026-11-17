<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Episode;
use App\Models\Work;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminEpisodeController extends Controller
{
    /**
     * List all episodes for a work
     */
    public function index(int $workId): JsonResponse
    {
        $episodes = Episode::where('work_id', $workId)->orderBy('number')->get();
        return response()->json(['data' => $episodes]);
    }

    /**
     * Create an episode for a work
     */
    public function store(Request $request, int $workId): JsonResponse
    {
        $work = Work::findOrFail($workId);

        $validated = $request->validate([
            'season_id' => 'nullable|exists:seasons,id',
            'number' => 'required|integer|min:1',
            'title' => 'nullable|string|max:255',
            'duration_sec' => 'nullable|integer|min:0',
            'file_id' => 'required|string|max:255',
            'file_unique_id' => 'nullable|string|max:255',
            'storage_chat_id' => 'required|integer',
            'storage_message_id' => 'required|integer',
            'is_preview' => 'nullable|boolean',
            'price_stars' => 'nullable|integer|min:1',
            'status' => 'nullable|in:draft,published,hidden',
        ]);

        $validated['work_id'] = $work->id;
        $validated['status'] = $validated['status'] ?? 'draft';

        $episode = Episode::create($validated);

        AdminAuditLog::create([
            'actor_id' => $request->user()->id,
            'action' => 'episode_created',
            'entity_type' => 'episode',
            'entity_id' => $episode->id,
            'new_values' => $episode->toArray(),
            'created_at' => now(),
        ]);

        return response()->json(['data' => $episode], 201);
    }

    /**
     * Update episode fields
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $episode = Episode::findOrFail($id);
        $oldValues = $episode->toArray();

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'number' => 'sometimes|integer|min:1',
            'duration_sec' => 'nullable|integer|min:0',
            'is_preview' => 'nullable|boolean',
            'price_stars' => 'nullable|integer|min:1',
        ]);

        $episode->update($validated);

        AdminAuditLog::create([
            'actor_id' => $request->user()->id,
            'action' => 'episode_updated',
            'entity_type' => 'episode',
            'entity_id' => $episode->id,
            'old_values' => $oldValues,
            'new_values' => $episode->toArray(),
            'created_at' => now(),
        ]);

        return response()->json(['data' => $episode]);
    }

    /**
     * Publish episode
     */
    public function publish(Request $request, int $id): JsonResponse
    {
        $episode = Episode::findOrFail($id);
        $episode->update([
            'status' => 'published',
            'publish_at' => now(),
        ]);

        return response()->json(['data' => ['message' => 'Epizod nashr qilindi', 'episode' => $episode]]);
    }

    /**
     * Hide episode
     */
    public function hide(Request $request, int $id): JsonResponse
    {
        $episode = Episode::findOrFail($id);
        $episode->update(['status' => 'hidden']);

        return response()->json(['data' => ['message' => 'Epizod yashirildi', 'episode' => $episode]]);
    }
}
