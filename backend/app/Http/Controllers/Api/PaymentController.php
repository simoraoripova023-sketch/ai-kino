<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PurchaseIntent;
use App\Services\PaymentService;
use App\Services\TelegramBotService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;
    protected TelegramBotService $botService;

    public function __construct(PaymentService $paymentService, TelegramBotService $botService)
    {
        $this->paymentService = $paymentService;
        $this->botService = $botService;
    }

    /**
     * Create purchase intent for whole work or single episode
     */
    public function createIntent(Request $request): JsonResponse
    {
        $request->validate([
            'target_type' => 'required|in:work,episode',
            'target_id' => 'required|integer',
        ]);

        $user = $request->user();

        try {
            $intent = $this->paymentService->createIntent(
                $user,
                $request->input('target_type'),
                (int) $request->input('target_id')
            );

            // Create Telegram Stars invoice link for Mini App in-app payment
            $title = $intent->target_type === 'work' ? 'To\'liq asarga kirish' : 'Epizodga kirish';
            $desc = "AI Kino platformasida tomosha qilish uchun Telegram Stars to'lovi";

            $linkResult = $this->botService->createInvoiceLink(
                $title,
                $desc,
                $intent->invoice_payload,
                $intent->amount_stars
            );

            // Also send invoice directly to user's Telegram chat as fallback
            $this->botService->sendStarsInvoice(
                $user->telegram_id,
                $title,
                $desc,
                $intent->invoice_payload,
                $intent->amount_stars
            );

            return response()->json([
                'data' => [
                    'intent_id' => $intent->id,
                    'target_type' => $intent->target_type,
                    'target_id' => $intent->target_id,
                    'amount_stars' => $intent->amount_stars,
                    'currency' => $intent->currency,
                    'status' => $intent->status,
                    'invoice_link' => $linkResult['link'] ?? null,
                    'invoice_action' => !empty($linkResult['link']) ? 'open_invoice' : 'bot',
                    'message' => !empty($linkResult['link']) ? 'Hisob yaratildi' : 'To\'lov hisobi Telegram bot chatiga yuborildi',
                ],
            ], 201);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 400;
            return response()->json([
                'error' => [
                    'code' => $code === 409 ? 'ALREADY_OWNED' : 'PAYMENT_INTENT_ERROR',
                    'message' => $e->getMessage(),
                ],
            ], $code >= 400 && $code < 500 ? $code : 400);
        }
    }

    /**
     * Get purchase intent status
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $intent = PurchaseIntent::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json(['data' => $intent]);
    }

    /**
     * Get user's completed purchases
     */
    public function myPurchases(Request $request): JsonResponse
    {
        $user = $request->user();

        $purchases = Payment::with(['intent'])
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->latest('paid_at')
            ->get();

        return response()->json(['data' => $purchases]);
    }

    /**
     * Simulate payment for local/sandbox testing
     */
    public function simulatePay(Request $request, string $id): JsonResponse
    {
        if (!config('app.debug')) {
            return response()->json(['error' => ['code' => 'FORBIDDEN', 'message' => 'Simulyatsiya faqat debug rejimida mavjud']], 403);
        }

        $user = $request->user();
        $intent = PurchaseIntent::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $fakePayment = [
            'telegram_payment_charge_id' => 'sandbox_charge_' . \Illuminate\Support\Str::random(12),
            'invoice_payload' => $intent->invoice_payload,
            'total_amount' => $intent->amount_stars,
            'currency' => 'XTR',
        ];

        $payment = $this->paymentService->handleSuccessfulPayment($fakePayment, $user->telegram_id);

        return response()->json([
            'data' => [
                'message' => 'To‘lov muvaffaqiyatli simulyatsiya qilindi va kontent ochildi',
                'payment' => $payment,
            ],
        ]);
    }
}
