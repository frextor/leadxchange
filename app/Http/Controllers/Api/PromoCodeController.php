<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PromoCode;
use App\Models\Subscription;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromoCodeController extends Controller
{
    public function redeem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20'],
        ]);

        $code = PromoCode::where('code', strtoupper(trim($data['code'])))->first();

        if (! $code) {
            return response()->json([
                'success' => false,
                'message' => 'This code does not exist.',
            ], 422);
        }

        if (! $code->isRedeemable()) {
            return response()->json([
                'success' => false,
                'message' => $code->isUsed()
                    ? 'This code has already been used.'
                    : 'This code is no longer valid.',
            ], 422);
        }

        $user = $request->user();

        DB::transaction(function () use ($code, $user) {
            $code->update(['used_by' => $user->id, 'used_at' => now()]);

            // Extend from current expiry if a promo is already running — never shorten access.
            $base = ($user->promo_premium_until && now()->lt($user->promo_premium_until))
                ? $user->promo_premium_until
                : now();

            $until = $base->copy()->addDays($code->premium_days);
            $user->forceFill(['promo_premium_until' => $until])->save();

            // Reflect in subscriptions so the mobile sees plan=premium — only if no real paid plan.
            if (! $user->hasRealPlanAtLeast('premium')) {
                $premiumPlan = Plan::where('name', 'premium')->first();
                if ($premiumPlan) {
                    Subscription::updateOrCreate(
                        ['user_id' => $user->id, 'source' => 'promo_code'],
                        ['plan_id' => $premiumPlan->id, 'status' => 'active', 'trial_ends_at' => $until],
                    );
                }
            }
        });

        ActivityLogger::log(
            'promo_code.redeemed',
            "Code promo \"{$code->code}\" utilisé ({$code->premium_days} jours de Premium)",
            $user->id,
        );

        $freshUser = $user->fresh();

        return response()->json([
            'success' => true,
            'message' => "Code activated! You now have {$code->premium_days} days of Premium.",
            'data'    => [
                'premium_days'        => $code->premium_days,
                'promo_premium_until' => $freshUser->promo_premium_until?->toIso8601String(),
            ],
        ]);
    }
}
