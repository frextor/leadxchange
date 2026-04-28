<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    /**
     * Show the form for creating a new company.
     */
    public function create()
    {
        return view('company.create');
    }

    /**
     * Search companies by name (AJAX)
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 3) {
            return response()->json([]);
        }

        $companies = Company::where('name', 'LIKE', "%{$query}%")
            ->orWhere('siret', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'siret', 'sector', 'website']);

        return response()->json($companies);
    }

    /**
     * Store a newly created company OR join existing company.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Check if user already has a company
        if ($user->company_id) {
            return back()->with('error', 'Vous avez déjà une entreprise associée à votre compte.');
        }

        // Case 1: User selected an existing company
        if ($request->filled('existing_company_id')) {
            return $this->joinExistingCompany($request, $user);
        }

        // Case 2: User is creating a new company
        return $this->createNewCompany($request, $user);
    }

    /**
     * Join an existing company
     */
    private function joinExistingCompany(Request $request, $user)
    {
        $companyId = $request->input('existing_company_id');

        // Find the company
        $company = Company::find($companyId);

        if (!$company) {
            return back()->with('error', 'Entreprise introuvable.');
        }

        DB::beginTransaction();

        try {
            // Associate user with company
            $user->update([
                'company_id' => $company->id,
                'onboarding_completed' => true,
            ]);

            DB::commit();

            Log::info('User joined existing company', [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'user_id' => $user->id
            ]);

            return redirect()->route('dashboard')
                ->with('success', "Vous avez rejoint l'entreprise {$company->name} avec succès !");
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to join company', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'company_id' => $companyId
            ]);

            return back()->with('error', 'Erreur lors de l\'association à l\'entreprise. Veuillez réessayer.');
        }
    }

    /**
     * Create a new company
     */
    private function createNewCompany(Request $request, $user)
    {
        // Validate
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'siret' => ['required', 'string', 'size:14', 'unique:companies,siret', 'regex:/^[0-9]{14}$/'],
            'sector' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ], [
            'name.required' => 'Le nom de l\'entreprise est requis.',
            'siret.required' => 'Le SIRET est requis.',
            'siret.size' => 'Le SIRET doit contenir exactement 14 chiffres.',
            'siret.unique' => 'Ce numéro SIRET est déjà enregistré.',
            'siret.regex' => 'Le SIRET doit contenir uniquement des chiffres.',
            'sector.required' => 'Le secteur d\'activité est requis.',
            'website.url' => 'Le site web doit être une URL valide.',
        ]);

        DB::beginTransaction();

        try {
            // Create company
            $company = Company::create([
                'name' => $validated['name'],
                'siret' => $validated['siret'],
                'sector' => $validated['sector'],
                'website' => $validated['website'],
            ]);

            // Update user with company and mark onboarding as completed
            $user->update([
                'company_id' => $company->id,
                'onboarding_completed' => true,
            ]);

            DB::commit();

            Log::info('Company created successfully', [
                'company_id' => $company->id,
                'user_id' => $user->id
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Entreprise créée avec succès ! Votre profil est maintenant complet.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Company creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de l\'entreprise. Veuillez réessayer.');
        }
    }
}
