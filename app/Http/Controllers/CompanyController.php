<?php

namespace App\Http\Controllers;

use App\Services\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * CompanyController (WEB - REFACTORED)
 * 
 * Thin controller for web interface.
 * Uses CompanyService for business logic.
 */
class CompanyController extends Controller
{
    protected CompanyService $companyService;

    /**
     * Inject CompanyService.
     */
    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * Show the form for creating a new company.
     */
    public function create()
    {
        return view('company.create');
    }

    /**
     * Search companies (AJAX).
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        // Service handles search logic
        $companies = $this->companyService->searchCompanies($query);

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

        try {
            // Case 1: User selected an existing company
            if ($request->filled('existing_company_id')) {
                $company = $this->companyService->joinCompany(
                    $user,
                    $request->input('existing_company_id')
                );

                return redirect()->route('dashboard')
                    ->with('success', "Vous avez rejoint l'entreprise {$company->name} avec succès !");
            }

            // Case 2: User is creating a new company
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

            $company = $this->companyService->createCompany($user, $validated);

            return redirect()->route('dashboard')
                ->with('success', 'Entreprise créée avec succès ! Votre profil est maintenant complet.');
        } catch (\Exception $e) {
            Log::error('Company operation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
