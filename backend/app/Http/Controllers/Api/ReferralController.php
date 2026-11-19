<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    protected ReferralService $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    /**
     * Get user referral statistics and links
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->referralService->getStats($user);

        return response()->json([
            'ok' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Claim / bind referral code
     */
    public function claim(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:32',
        ]);

        $user = $request->user();
        $result = $this->referralService->processReferral($user, $validated['code']);

        return response()->json($result, $result['ok'] ? 200 : 422);
    }
}
