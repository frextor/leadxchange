<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\PromoCode;
use App\Models\Subscription;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PromoCodeRedeemController extends Controller
{
    public function show(): View
    {
        return view('promo.redeem');
    }

    public function redeem(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20'],
        ], [
            'code.required' => 'Veuillez saisir un code.',
        ]);

        $code = PromoCode::where('code', strtoupper(trim($data['code'])))->first();

        if (! $code) {
            return back()->withErrors(['code' => 'Ce code n\'existe pas.'])->withInput();
        }

        if (! $code->isRedeemable()) {
            return back()->withErrors(['code' => $code->isUsed()
                ? 'Ce code a déjà été utilisé.'
                : 'Ce code n\'est plus valide.'])->withInput();
        }

        $user = $request->user();

        DB::transaction(function () use ($code, $user) {
            $code->update(['used_by' => $user->id, 'used_at' => now()]);

            // Si un accès promo court déjà, on prolonge à partir de sa date de fin plutôt que
            // de repartir d'aujourd'hui — un deuxième code ne doit jamais raccourcir l'accès en cours.
            $base = ($user->promo_premium_until && now()->lt($user->promo_premium_until))
                ? $user->promo_premium_until
                : now();

            $until = $base->copy()->addDays($code->premium_days);
            $user->forceFill(['promo_premium_until' => $until])->save();

            // Reflète l'accès dans subscriptions (source=promo_code) pour que le plan Premium
            // soit visible partout (mobile, admin…), sans jamais rétrograder un plan payant réel.
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

        ActivityLogger::log('promo_code.redeemed', "Code promo \"{$code->code}\" utilisé ({$code->premium_days} jours de Premium)", $user->id);

        return redirect()->route('dashboard')->with('success', "Code activé ! Vous profitez de Premium pendant {$code->premium_days} jour" . ($code->premium_days > 1 ? 's' : '') . ".");
    }
}
