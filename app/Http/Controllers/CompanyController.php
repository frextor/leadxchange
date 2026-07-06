<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function create()
    {
        $sectors = Sector::orderBy('name')->get(['id', 'name']);
        return view('company.create', compact('sectors'));
    }

    /**
     * GET /company/search?q=xxx  (local DB autocomplete)
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $companies = $this->companyService->searchCompanies($query);

        return response()->json($companies->map(fn($c) => [
            'id'     => $c->id,
            'name'   => $c->name,
            'siret'  => $c->siret,
            'sector' => $c->sector?->name ?? '',
        ]));
    }

    /**
     * GET /company/siret-lookup?siret=12345678901234
     * Proxy to the INSEE SIREN API — keeps the API key server-side.
     */
    public function siretLookup(Request $request): JsonResponse
    {
        $siret = preg_replace('/\D/', '', $request->get('siret', ''));

        if (strlen($siret) !== 14) {
            return response()->json(['error' => 'SIRET invalide (14 chiffres requis)'], 400);
        }

        $apiKey  = config('services.siren.key');
        $baseUrl = config('services.siren.base_url');

        try {
            $response = Http::withHeaders(['X-Client-Secret' => $apiKey])
                ->timeout(8)
                ->get("{$baseUrl}/v3/etablissements/{$siret}");

            if ($response->status() === 404) {
                return response()->json([
                    'found'   => false,
                    'message' => "Aucun établissement trouvé pour le SIRET {$siret}.",
                ], 404);
            }

            if ($response->status() === 400) {
                return response()->json(['error' => 'Format SIRET invalide'], 400);
            }

            if (!$response->successful()) {
                Log::warning('INSEE API error', ['status' => $response->status(), 'siret' => $siret]);
                return response()->json(['error' => 'Erreur lors de la consultation du registre INSEE'], 502);
            }

            $etab = $response->json('etablissement');

            // API returned 200 but no establishment data
            if (empty($etab)) {
                return response()->json([
                    'found'   => false,
                    'message' => "Aucun établissement trouvé pour le SIRET {$siret}.",
                ], 404);
            }

            $uniteLegale = $etab['unite_legale'] ?? [];

            $name = $uniteLegale['denomination']
                ?? trim(($uniteLegale['prenom_usuel'] ?? '') . ' ' . ($uniteLegale['nom'] ?? ''))
                ?: null;

            $nafCode = $etab['activite_principale'] ?? $uniteLegale['activite_principale'] ?? null;
            $etat    = $etab['etat_administratif'] ?? 'F'; // default to closed if unknown

            return response()->json([
                'found'              => true,
                'active'             => $etat === 'A',
                'name'               => $name,
                'siret'              => $siret,
                'siren'              => $etab['siren'] ?? substr($siret, 0, 9),
                'naf_code'           => $nafCode,
                'sector_suggestion'  => $this->nafToSector($nafCode),
                'etat_administratif' => $etat,
            ]);
        } catch (\Exception $e) {
            Log::error('INSEE API exception', ['message' => $e->getMessage(), 'siret' => $siret]);
            return response()->json(['error' => 'Impossible de contacter le registre INSEE'], 503);
        }
    }

    /**
     * POST /company  — create new or join existing company.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->company_id) {
            return back()->with('error', 'Vous avez déjà une entreprise associée à votre compte.');
        }

        try {
            // Case 1: join existing company (selected from autocomplete)
            if ($request->filled('existing_company_id')) {
                $company = $this->companyService->joinCompany(
                    $user,
                    $request->input('existing_company_id')
                );

                return redirect()->route('dashboard')
                    ->with('success', "Vous avez rejoint l'entreprise {$company->name} avec succès !");
            }

            // Case 2: create a new company
            $validated = $request->validate([
                'name'      => ['required', 'string', 'max:255'],
                'siret'     => ['required', 'string', 'size:14', 'unique:companies,siret', 'regex:/^[0-9]{14}$/'],
                'sector_id' => ['nullable', 'exists:sectors,id'],
                'website'   => ['nullable', 'url', 'max:255'],
            ], [
                'name.required'     => "Le nom de l'entreprise est requis.",
                'siret.required'    => 'Le SIRET est requis.',
                'siret.size'        => 'Le SIRET doit contenir exactement 14 chiffres.',
                'siret.unique'      => 'Ce numéro SIRET est déjà enregistré.',
                'siret.regex'       => 'Le SIRET doit contenir uniquement des chiffres.',
                'website.url'       => 'Le site web doit être une URL valide.',
            ]);

            $company = $this->companyService->createCompany($user, $validated);

            return redirect()->route('dashboard')
                ->with('success', 'Entreprise créée avec succès ! Votre profil est maintenant complet.');
        } catch (\Exception $e) {
            Log::error('Company operation failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Map a NAF/APE code to one of our sector names.
     */
    private function nafToSector(?string $nafCode): ?string
    {
        if (!$nafCode) {
            return null;
        }

        $prefix = (int) substr(preg_replace('/[^0-9]/', '', $nafCode), 0, 2);

        return match (true) {
            in_array($prefix, [58, 62, 63])             => 'Technology',
            in_array($prefix, range(64, 66))            => 'Finance',
            in_array($prefix, range(86, 88))            => 'Healthcare',
            $prefix === 85                              => 'Education',
            in_array($prefix, [45, 46, 47])             => 'Retail',
            in_array($prefix, range(10, 33))            => 'Manufacturing',
            in_array($prefix, [41, 42, 43])             => 'Construction',
            $prefix === 68                              => 'Real Estate',
            in_array($prefix, range(49, 53))            => 'Transport',
            in_array($prefix, [55, 56, 79])             => 'Tourism',
            default                                     => 'Services',
        };
    }
}
