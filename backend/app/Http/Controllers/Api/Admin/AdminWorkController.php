<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Work;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminWorkController extends Controller
{
    /**
     * List all works for admin
     */
    public function index(): JsonResponse
    {
        $works = Work::with(['genres', 'seasons', 'episodes'])
            ->latest()
            ->paginate(20);

        return response()->json(['data' => $works]);
    }

    /**
     * Create a new AI work (initially draft)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'format' => 'required|in:film,series,short',
            'style' => 'required|in:realistic,animation_2d,animation_3d,mixed',
            'language' => 'required|string|max:8',
            'age_rating' => 'required|in:0+,6+,12+,16+',
            'poster_path' => 'required|string|max:512',
            'cover_path' => 'nullable|string|max:512',
            'trailer_file_id' => 'nullable|string|max:255',
            'pricing_mode' => 'required|in:free,work,episode',
            'price_stars' => 'nullable|integer|min:1',
            'genre_ids' => 'nullable|array',
            'genre_ids.*' => 'exists:genres,id',
        ]);

        if (in_array($validated['pricing_mode'], ['work', 'episode']) && empty($validated['price_stars'])) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_PRICE',
                    'message' => 'Pullik asar uchun Stars narxi kiritilishi shart',
                ],
            ], 422);
        }

        $genreIds = $validated['genre_ids'] ?? [];
        unset($validated['genre_ids']);

        $validated['status'] = 'draft';

        $work = Work::create($validated);

        if (!empty($genreIds)) {
            $work->genres()->sync($genreIds);
        }

        // Audit log
        AdminAuditLog::create([
            'actor_id' => $request->user()->id,
            'action' => 'work_created',
            'entity_type' => 'work',
            'entity_id' => $work->id,
            'new_values' => $work->toArray(),
            'created_at' => now(),
        ]);

        return response()->json(['data' => $work->load('genres')], 201);
    }

    /**
     * Get work details for admin
     */
    public function show(int $id): JsonResponse
    {
        $work = Work::with(['genres', 'seasons', 'episodes'])->findOrFail($id);
        return response()->json(['data' => $work]);
    }

    /**
     * Update work fields
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $work = Work::findOrFail($id);
        $oldValues = $work->toArray();

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'format' => 'sometimes|in:film,series,short',
            'style' => 'sometimes|in:realistic,animation_2d,animation_3d,mixed',
            'language' => 'sometimes|string|max:8',
            'age_rating' => 'sometimes|in:0+,6+,12+,16+',
            'poster_path' => 'sometimes|string|max:512',
            'cover_path' => 'nullable|string|max:512',
            'trailer_file_id' => 'nullable|string|max:255',
            'pricing_mode' => 'sometimes|in:free,work,episode',
            'price_stars' => 'nullable|integer|min:1',
            'genre_ids' => 'nullable|array',
            'genre_ids.*' => 'exists:genres,id',
        ]);

        if (isset($validated['genre_ids'])) {
            $work->genres()->sync($validated['genre_ids']);
            unset($validated['genre_ids']);
        }

        $work->update($validated);

        AdminAuditLog::create([
            'actor_id' => $request->user()->id,
            'action' => 'work_updated',
            'entity_type' => 'work',
            'entity_id' => $work->id,
            'old_values' => $oldValues,
            'new_values' => $work->toArray(),
            'created_at' => now(),
        ]);

        return response()->json(['data' => $work->load('genres')]);
    }

    /**
     * Publish work with all invariant checks (Section 8)
     */
    public function publish(Request $request, int $id): JsonResponse
    {
        $work = Work::with('episodes')->findOrFail($id);

        // Invariant 1: Must have title, description, poster
        if (empty($work->title) || empty($work->description) || empty($work->poster_path)) {
            return response()->json([
                'error' => [
                    'code' => 'INCOMPLETE_METADATA',
                    'message' => 'Nashr qilish uchun poster, nom va tavsif to\'liq bo\'lishi shart',
                ],
            ], 422);
        }

        // Invariant 2: Must have at least 1 healthy published episode with storage reference
        $healthyEpisodes = $work->episodes->filter(function ($ep) {
            return !empty($ep->file_id) && !empty($ep->storage_chat_id) && !empty($ep->storage_message_id);
        });

        if ($healthyEpisodes->isEmpty()) {
            return response()->json([
                'error' => [
                    'code' => 'NO_HEALTHY_EPISODES',
                    'message' => 'Nashr qilish uchun kamida bitta omborga yuklangan qism (storage reference) bo\'lishi shart',
                ],
            ], 422);
        }

        // Invariant 3: Pricing check
        if ($work->pricing_mode !== 'free' && (!$work->price_stars || $work->price_stars <= 0)) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_PRICING',
                    'message' => 'Pullik asar uchun musbat Stars narxi belgilanishi shart',
                ],
            ], 422);
        }

        $oldStatus = $work->status;
        $work->update([
            'status' => 'published',
            'published_at' => $work->published_at ?? now(),
        ]);

        // Publish all healthy episodes
        foreach ($healthyEpisodes as $ep) {
            $ep->update([
                'status' => 'published',
                'publish_at' => $ep->publish_at ?? now(),
            ]);
        }

        AdminAuditLog::create([
            'actor_id' => $request->user()->id,
            'action' => 'work_published',
            'entity_type' => 'work',
            'entity_id' => $work->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'published', 'published_at' => $work->published_at],
            'created_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'message' => 'Asar muvaffaqiyatli nashr etildi',
                'work' => $work->fresh(['episodes', 'genres']),
            ],
        ]);
    }

    /**
     * Hide work
     */
    public function hide(Request $request, int $id): JsonResponse
    {
        $work = Work::findOrFail($id);
        $oldStatus = $work->status;

        $work->update(['status' => 'hidden']);

        AdminAuditLog::create([
            'actor_id' => $request->user()->id,
            'action' => 'work_hidden',
            'entity_type' => 'work',
            'entity_id' => $work->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'hidden'],
            'created_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'message' => 'Asar yashirildi',
                'work' => $work,
            ],
        ]);
    }
}
