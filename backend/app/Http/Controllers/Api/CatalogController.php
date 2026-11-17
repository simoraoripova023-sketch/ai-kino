<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Work;
use App\Services\EntitlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    protected EntitlementService $entitlementService;

    public function __construct(EntitlementService $entitlementService)
    {
        $this->entitlementService = $entitlementService;
    }

    /**
     * Get active genres
     */
    public function genres(): JsonResponse
    {
        $genres = Genre::orderBy('sort_order')->get();
        return response()->json(['data' => $genres]);
    }

    /**
     * List published works with filters & pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Work::with('genres')
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });

        // Format filter (film, series, short)
        if ($request->filled('format')) {
            $query->where('format', $request->input('format'));
        }

        // Style filter (realistic, animation_2d, animation_3d, mixed)
        if ($request->filled('style')) {
            $query->where('style', $request->input('style'));
        }

        // Language filter
        if ($request->filled('language')) {
            $query->where('language', $request->input('language'));
        }

        // Genre filter (by slug or id)
        if ($request->filled('genre')) {
            $genreParam = $request->input('genre');
            $query->whereHas('genres', function ($q) use ($genreParam) {
                $q->where('slug', $genreParam)->orWhere('genres.id', $genreParam);
            });
        }

        // Search query
        if ($request->filled('q')) {
            $search = '%' . $request->input('q') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }

        $perPage = min((int) $request->input('per_page', 20), 50);
        $works = $query->latest('published_at')->paginate($perPage);

        return response()->json([
            'data' => $works->items(),
            'pagination' => [
                'current_page' => $works->currentPage(),
                'last_page' => $works->lastPage(),
                'per_page' => $works->perPage(),
                'total' => $works->total(),
            ],
        ]);
    }

    /**
     * Get work detail with episode access states
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user('sanctum');

        $work = Work::with(['genres', 'seasons'])->find($id);
        if (!$work || $work->status !== 'published') {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Asar topilmadi yoki e\'lon qilinmagan',
                ],
            ], 404);
        }

        // Get published episodes
        $episodes = $work->publishedEpisodes()->get()->map(function ($episode) use ($user) {
            $access = $this->entitlementService->getEpisodeAccess($user, $episode);
            return [
                'id' => $episode->id,
                'season_id' => $episode->season_id,
                'number' => $episode->number,
                'title' => $episode->title,
                'duration_sec' => $episode->duration_sec,
                'is_preview' => $episode->is_preview,
                'access_state' => $access['access_state'],
                'price_stars' => $access['price_stars'] ?? null,
                'target_type' => $access['target_type'] ?? null,
                'target_id' => $access['target_id'] ?? null,
            ];
        });

        return response()->json([
            'data' => [
                'work' => [
                    'id' => $work->id,
                    'title' => $work->title,
                    'description' => $work->description,
                    'format' => $work->format,
                    'style' => $work->style,
                    'language' => $work->language,
                    'age_rating' => $work->age_rating,
                    'poster_path' => $work->poster_path,
                    'cover_path' => $work->cover_path,
                    'has_trailer' => !empty($work->trailer_file_id),
                    'pricing_mode' => $work->pricing_mode,
                    'price_stars' => $work->price_stars,
                    'published_at' => $work->published_at,
                    'genres' => $work->genres,
                    'seasons' => $work->seasons,
                ],
                'episodes' => $episodes,
            ],
        ]);
    }
}
