<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Vérifier que le hash correspond
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('dashboard')
                ->with('error', 'Le lien de vérification est invalide.');
        }

        // Si déjà vérifié
        if ($user->hasVerifiedEmail()) {
            return $this->deepLinkResponse($request);
        }

        // Marquer comme vérifié
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Connecter l'utilisateur si ce n'est pas déjà fait
        if (!Auth::check()) {
            Auth::login($user);
        }

        return $this->deepLinkResponse($request);
    }

    /**
     * Return the appropriate response after email verification.
     *
     * Mobile browsers: HTTP 302 redirect to the custom URL scheme so the OS
     * opens the app directly (no JavaScript required — OS intercepts the redirect).
     * Desktop browsers: serve the JS bridge page which tries window.location and
     * falls back to the web dashboard after 2.5 s.
     */
    private function deepLinkResponse(Request $request)
    {
        $ua = $request->header('User-Agent', '');
        $isMobile = str_contains($ua, 'iPhone')
                 || str_contains($ua, 'iPad')
                 || str_contains($ua, 'Android');

        if ($isMobile) {
            return redirect('x-tensia://auth/email-verified');
        }

        return view('auth.email-verified');
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('info', 'Votre email est déjà vérifié.');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Un nouveau lien de vérification a été envoyé à votre adresse email.');
    }
}
