<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        // Log the incoming request data for debugging
        Log::info('Registration attempt', [
            'data' => $request->except('password', 'password_confirmation')
        ]);

        // Validate all fields from both steps
        $validated = $request->validate([
            // Step 1: Account Information
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],

            // Step 2: Profile Information
            'gender' => ['required', 'in:male,female,other'],
            'city_birth' => ['required', 'string', 'max:255'],
            'city_living' => ['required', 'string', 'max:255'],
            'birthday' => ['required', 'date', 'before:today'],
            'terms' => ['required', 'accepted'],
        ], [
            // Custom error messages
            'first_name.required' => 'Le prénom est requis.',
            'last_name.required' => 'Le nom est requis.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'email.required' => 'L\'email est requis.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'gender.required' => 'Veuillez sélectionner votre sexe.',
            'city_birth.required' => 'La ville de naissance est requise.',
            'city_living.required' => 'La ville de résidence est requise.',
            'birthday.required' => 'La date de naissance est requise.',
            'birthday.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'terms.required' => 'Vous devez accepter les conditions.',
            'terms.accepted' => 'Vous devez accepter les conditions.',
        ]);

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'gender' => $validated['gender'],
                'city_birth' => $validated['city_birth'],
                'city_living' => $validated['city_living'],
                'birthday' => $validated['birthday'],
                'role' => 'user',
                'onboarding_completed' => false,
            ]);

            Log::info('User created successfully', ['user_id' => $user->id]);

            // Fire the registered event for email verification
            event(new Registered($user));

            // Assign basic plan
            $basicPlan = Plan::where('name', 'basic')->first();

            if ($basicPlan) {
                Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $basicPlan->id,
                    'status' => 'active',
                    'trial_ends_at' => now()->addDays(14),
                ]);
                Log::info('Basic plan assigned', ['user_id' => $user->id]);
            } else {
                Log::warning('Basic plan not found in database');
            }

            DB::commit();

            // Log the user in
            Auth::login($user);

            Log::info('User logged in successfully', ['user_id' => $user->id]);

            // Redirect with success message to dashboard
            return redirect()->route('dashboard')
                ->with('success', 'Compte créé avec succès ! Un email de vérification vous a été envoyé.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['error' => 'Erreur lors de la création du compte. Détails: ' . $e->getMessage()]);
        }
    }
}
