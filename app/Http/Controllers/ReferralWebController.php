<?php

namespace App\Http\Controllers;

use App\Mail\ReferralInvitationMail;
use App\Models\Referral;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReferralWebController extends Controller
{
    public function index(Request $request): View
    {
        $user     = $request->user();
        $referrals = Referral::where('referrer_id', $user->id)
            ->latest()
            ->get();

        $referralLink = route('referral.register', ['token' => 'LINK']);
        // Generate a persistent share link token for this user
        $shareToken = $this->getOrCreateShareToken($user);
        $referralLink = route('referral.register', ['token' => $shareToken]);

        return view('referral.index', compact('user', 'referrals', 'referralLink'));
    }

    public function send(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'L\'adresse email est requise.',
            'email.email'    => 'Adresse email invalide.',
        ]);

        $email = strtolower(trim($request->email));

        if ($email === strtolower($user->email)) {
            return back()->with('error', 'Vous ne pouvez pas vous parrainer vous-même.');
        }

        // Check if already a registered user
        if (\App\Models\User::where('email', $email)->exists()) {
            return back()->with('error', 'Cette personne a déjà un compte LeadXchange.');
        }

        $referral = Referral::firstOrCreate(
            ['referrer_id' => $user->id, 'referred_email' => $email],
            ['token' => Str::random(48), 'status' => 'pending'],
        );

        // Resend: regenerate token
        if (!$referral->wasRecentlyCreated) {
            $referral->update(['token' => Str::random(48), 'status' => 'pending']);
        }

        try {
            Mail::to($email)->send(new ReferralInvitationMail($user, $referral->token));
        } catch (\Exception $e) {
            return back()->with('error', 'Impossible d\'envoyer l\'email. Réessayez plus tard.');
        }

        return back()->with('success', "Invitation envoyée à {$email}.");
    }

    private function getOrCreateShareToken(\App\Models\User $user): string
    {
        // Use a deterministic token based on user id for share links
        // (different from email-specific referral tokens)
        $existing = Referral::where('referrer_id', $user->id)
            ->where('referred_email', 'share:' . $user->id)
            ->first();

        if ($existing) {
            return $existing->token;
        }

        $referral = Referral::create([
            'referrer_id'    => $user->id,
            'referred_email' => 'share:' . $user->id,
            'token'          => Str::random(48),
            'status'         => 'pending',
        ]);

        return $referral->token;
    }
}
