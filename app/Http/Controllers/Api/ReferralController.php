<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ReferralInvitationMail;
use App\Models\Referral;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ReferralController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));

        // Don't allow self-referral
        if ($email === strtolower($user->email)) {
            return response()->json(['message' => 'Vous ne pouvez pas vous parrainer vous-même.'], 422);
        }

        // Upsert — one pending referral per (referrer, email)
        $referral = Referral::firstOrCreate(
            ['referrer_id' => $user->id, 'referred_email' => $email],
            ['token' => Str::random(48), 'status' => 'pending'],
        );

        // Regenerate token if already sent (resend flow)
        if (!$referral->wasRecentlyCreated) {
            $referral->update(['token' => Str::random(48), 'status' => 'pending']);
        }

        try {
            Mail::to($email)->send(new ReferralInvitationMail($user, $referral->token));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ReferralMail failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Impossible d\'envoyer l\'email. Réessayez.'], 500);
        }

        return response()->json(['message' => 'Invitation envoyée avec succès.']);
    }
}
