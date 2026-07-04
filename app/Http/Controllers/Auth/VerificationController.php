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
            return view('auth.email-verified');
        }

        // Marquer comme vérifié
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Connecter l'utilisateur si ce n'est pas déjà fait
        if (!Auth::check()) {
            Auth::login($user);
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
