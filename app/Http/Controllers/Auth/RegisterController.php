<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Nationality;
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
            'cities'        => City::orderBy('name')->get(['id', 'name']),
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

            // Step 2: Profile Information
            'phone'          => ['nullable', 'string', 'max:30', 'unique:users,phone'],
            'gender'         => ['required', 'in:male,female,other'],
            'nationality_id' => ['required', 'integer', 'exists:nationalities,id'],
            'city_id'        => ['required', 'integer', 'exists:cities,id'],
            'birthday'       => ['required', 'date', 'before:today'],
            'terms'          => ['required', 'accepted'],
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
            'city_id.required'        => 'Veuillez sélectionner votre ville de résidence.',
            'city_id.exists'          => 'Ville invalide.',
            'birthday.required'       => 'La date de naissance est requise.',
            'birthday.before'         => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'terms.required'          => 'Vous devez accepter les conditions.',
            'terms.accepted'          => 'Vous devez accepter les conditions.',
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
                'birthday'           => $validated['birthday'],
            ]);

            // Log the user in
            AuthFacade::login($user);

            // Redirect with success message to dashboard
            return redirect()->route('dashboard')
                ->with('success', 'Compte créé avec succès ! Bienvenue ' . $user->first_name . ' !');
        } catch (\Exception $e) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['error' => 'Erreur lors de la création du compte. Détails: ' . $e->getMessage()]);
        }
    }
}
