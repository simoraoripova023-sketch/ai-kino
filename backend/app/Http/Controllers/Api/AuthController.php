<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TelegramInitDataValidator;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected TelegramInitDataValidator $validator;

    public function __construct(TelegramInitDataValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Authenticate or register user via raw Telegram initData
     */
    public function telegramAuth(Request $request): JsonResponse
    {
        $request->validate([
            'init_data' => 'required|string',
        ]);

        try {
            $validated = $this->validator->validate($request->input('init_data'));
            $tgUser = $validated['user'] ?? null;

            if (!$tgUser || !isset($tgUser['id'])) {
                return response()->json([
                    'error' => [
                        'code' => 'INVALID_AUTH',
                        'message' => 'Foydalanuvchi ma\'lumotlari topilmadi',
                    ],
                ], 401);
            }

            // Find or create user
            $user = User::updateOrCreate(
                ['telegram_id' => $tgUser['id']],
                [
                    'first_name' => $tgUser['first_name'] ?? 'User',
                    'username' => $tgUser['username'] ?? null,
                    'language' => substr($tgUser['language_code'] ?? 'uz', 0, 2),
                    'last_seen_at' => now(),
                ]
            );

            if ($user->is_blocked) {
                return response()->json([
                    'error' => [
                        'code' => 'USER_BLOCKED',
                        'message' => 'Foydalanuvchi hisobi bloklangan',
                    ],
                ], 403);
            }

            // Create Sanctum personal access token
            $tokenResult = $user->createToken('telegram_mini_app');

            return response()->json([
                'data' => [
                    'token' => $tokenResult->plainTextToken,
                    'expires_at' => now()->addDays(30)->toIso8601String(),
                    'user' => [
                        'id' => $user->id,
                        'telegram_id' => $user->telegram_id,
                        'first_name' => $user->first_name,
                        'username' => $user->username,
                        'language' => $user->language,
                    ],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => $e->getMessage(),
                ],
            ], 401);
        }
    }

    /**
     * Get current user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'data' => [
                'id' => $user->id,
                'telegram_id' => $user->telegram_id,
                'first_name' => $user->first_name,
                'username' => $user->username,
                'language' => $user->language,
            ],
        ]);
    }

    /**
     * Revoke current session token
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'data' => [
                'message' => 'Muvaffaqiyatli chiqildi',
            ],
        ]);
    }
}
