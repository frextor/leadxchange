<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Nationality;
use App\Models\Referral;
use App\Models\Sector;
use App\Services\ActivityLogger;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as AuthFacade;

/**
 * RegisterController (WEB - REFACTORED)
 * 
 * Thin controller for web interface.
 * Uses AuthService for business logic.
 */
class RegisterController extends Controller
{
    protected AuthService $authService;

    /**
     * Inject AuthService.
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register', [
            'nationalities' => Nationality::orderBy('country')->get(),
            'cities'        => City::active()->with('country:id,name')->orderBy('name')->get(['id', 'name', 'country_id']),
            'sectors'       => Sector::orderBy('name')->get(['id', 'name']),  // §4.1
        ]);
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        // Validate all fields from both steps
        $validated = $request->validate([
            // Step 1: Account Information
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],

            // Parrainage
            'referral_token' => ['nullable', 'string', 'max:64'],

            // Step 2: Profile Information
            'phone'          => ['nullable', 'string', 'max:30', 'unique:users,phone'],
            'gender'         => ['required', 'in:male,female,other'],
            'nationality_id' => ['required', 'integer', 'exists:nationalities,id'],
            'city_id'   => ['required', 'integer', 'exists:cities,id'],
            'region_id' => ['nullable', 'integer', 'exists:cities,id'],
            'birthday'       => ['required', 'date', 'before:-18 years'],  // §3.2 : ≥ 18 ans
            'legal_capacity' => ['required', 'accepted'],                  // §3.2 : capacité juridique
            // §4.1 — Secteur d'activité et fonction obligatoires
            'sector_id'       => ['required', 'integer', 'exists:sectors,id'],
            'job_title'       => ['required', 'string', 'max:100'],
            'is_professional' => ['required', 'accepted'],  // CGU §2.3
            'terms'           => ['required', 'accepted'],   // CGU §3.1
        ], [
            'first_name.required'     => 'Le prénom est requis.',
            'last_name.required'      => 'Le nom est requis.',
            'email.unique'            => 'Cet email est déjà utilisé.',
            'email.required'          => 'L\'email est requis.',
            'password.confirmed'      => 'Les mots de passe ne correspondent pas.',
            'password.required'       => 'Le mot de passe est requis.',
            'password.min'            => 'Le mot de passe doit contenir au moins 8 caractères.',
            'phone.unique'            => 'Ce numéro de téléphone est déjà utilisé.',
            'gender.required'         => 'Veuillez sélectionner votre sexe.',
            'nationality_id.required' => 'Veuillez sélectionner votre nationalité.',
            'nationality_id.exists'   => 'Nationalité invalide.',
            'city_id.required' => 'Veuillez sélectionner votre ville de résidence.',
            'city_id.exists'   => 'Ville invalide.',
            'birthday.required'          => 'La date de naissance est requise.',
            'birthday.before'            => 'Vous devez avoir au moins 18 ans pour vous inscrire.',
            'legal_capacity.accepted'    => 'Vous devez déclarer avoir la pleine capacité juridique et être âgé(e) d\'au moins 18 ans.',
            'sector_id.required'         => 'Veuillez sélectionner votre secteur d\'activité.',
            'sector_id.exists'           => 'Secteur invalide.',
            'job_title.required'         => 'Veuillez indiquer votre fonction / poste.',
            'is_professional.accepted'   => 'Vous devez déclarer agir dans le cadre de votre activité professionnelle.',
            'terms.accepted'             => 'Vous devez accepter les Conditions Générales d\'Utilisation.',
        ]);

        try {
            // Service handles ALL business logic
            $user = $this->authService->register($validated);

            // Update profile (step 2 data)
            $user = $this->authService->updateProfile($user, [
                'phone'              => $validated['phone'] ?? null,
                'phone_country_code' => $validated['phone_country_code'] ?? null,
                'gender'             => $validated['gender'],
                'nationality_id'     => $validated['nationality_id'],
                'city_id'            => $validated['city_id'],
                'region_id'          => $validated['region_id'] ?? $validated['city_id'],
                'birthday'           => $validated['birthday'],
                'job_title'          => $validated['job_title'],     // §4.1
                'sector_id'          => $validated['sector_id'],     // §4.1
            ]);

            // The registration form already collects the account & step-2 info,
            // but the guided onboarding (photo, entreprise, préférences, présentation)
            // still needs to run — updateProfile() marks onboarding as completed,
            // so we reset it here to route the user through that wizard next.
            $user->update(['onboarding_completed' => false]);

            // Traitement du parrainage
            $referralToken = $validated['referral_token'] ?? $request->session()->get('referral_token');
            if ($referralToken) {
                $referral = Referral::where('token', $referralToken)
                    ->where('status', 'pending')
                    ->whereNotNull('referrer_id')
                    ->first();

                if ($referral) {
                    // Marquer comme inscrit et lier le parrain
                    $referral->update(['status' => 'registered']);
                    $user->update(['referred_by' => $referral->referrer_id]);

                    // Récompense : +5 points au parrain
                    $referrer = $referral->referrer;
                    if ($referrer) {
                        $referrer->adjustPoints(5, 'referral_reward');
                    }
                }

                $request->session()->forget('referral_token');
            }

            ActivityLogger::log('auth.register', "Nouveau compte créé ({$user->email})", $user->id);

            // Log the user in
            AuthFacade::login($user);

            // Create a Sanctum token for SPA API calls (JS fetch)
            $token = $user->createToken('web-spa')->plainTextToken;
            $request->session()->put('web_api_token', $token);

            // Redirect to the guided onboarding wizard before the dashboard
            return redirect()->route('onboarding.show')
                ->with('success', 'Compte créé avec succès ! Bienvenue ' . $user->first_name . ' !');
        } catch (\Exception $e) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['error' => 'Erreur lors de la création du compte. Détails: ' . $e->getMessage()]);
        }
    }
}
