<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    protected CompanyService $companyService;
    protected AuthService $authService;

    public function __construct(CompanyService $companyService, AuthService $authService)
    {
        $this->companyService = $companyService;
        $this->authService    = $authService;
    }

    /**
     * GET /api/companies/search?q=xxx
     */
    public function search(Request $request): JsonResponse
    {
        $companies = $this->companyService->searchCompanies($request->get('q', ''));

        return response()->json([
            'success' => true,
            'data'    => $companies->map(fn($c) => [
                'id'        => $c->id,
                'name'      => $c->name,
                'siret'     => $c->siret,
                'sector_id' => $c->sector_id,
                'sector'    => $c->sector?->name,
                'website'   => $c->website,
            ]),
        ]);
    }

    /**
     * POST /api/companies
     *
     * Create a new company OR join an existing one.
     *
     * Create body:
     *   { name, siret, website?, sector_id? }
     *
     * Join body:
     *   { existing_company_id }
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            // ── Join existing company ──────────────────────────────────────
            if ($request->filled('existing_company_id')) {
                $request->validate([
                    'existing_company_id' => ['required', 'integer', 'exists:companies,id'],
                ]);

                $company = $this->companyService->joinCompany(
                    $user,
                    $request->integer('existing_company_id')
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Company joined successfully',
                    'data'    => [
                        'action'  => 'joined',
                        'company' => $this->companyData($company),
                        'user'    => $this->authService->getUserData($user->fresh()),
                    ],
                ]);
            }

            // ── Create new company or return existing one by SIRET ────────
            $validated = $request->validate([
                'name'      => ['required', 'string', 'max:255'],
                'siret'     => ['required', 'string', 'size:14', 'regex:/^[0-9]{14}$/'],
                'sector_id' => ['nullable', 'integer', 'exists:sectors,id'],
                'website'   => ['nullable', 'url', 'max:255'],
            ]);

            $existing = \App\Models\Company::where('siret', $validated['siret'])->first();
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Company already exists',
                    'data'    => [
                        'action'  => 'existing',
                        'company' => $this->companyData($existing),
                    ],
                ]);
            }

            $company = $this->companyService->createCompany($user, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Company created successfully',
                'data'    => [
                    'action'  => 'created',
                    'company' => $this->companyData($company),
                    'user'    => $this->authService->getUserData($user->fresh()),
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * GET /api/companies/search-external?q=xxx
     * Search by name OR SIRET via the official French government API.
     * No API key required. Returns companies not necessarily in our local DB.
     */
    public function searchExternal(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));

        if (mb_strlen($query) < 3) {
            return response()->json(['success' => true, 'data' => []]);
        }

        try {
            $response = Http::timeout(6)->get('https://recherche-entreprises.api.gouv.fr/search', [
                'q'        => $query,
                'per_page' => 8,
            ]);

            if (!$response->successful()) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $companies = collect($response->json('results') ?? [])
                ->map(fn ($r) => [
                    'id'    => '',
                    'name'  => $r['nom_complet'] ?? $r['nom_raison_sociale'] ?? null,
                    'siret' => $r['siege']['siret'] ?? null,
                ])
                ->filter(fn ($c) => $c['siret'] && $c['name'])
                ->values();

            return response()->json(['success' => true, 'data' => $companies]);
        } catch (\Exception $e) {
            Log::warning('searchExternal failed', ['error' => $e->getMessage(), 'q' => $query]);
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    /**
     * GET /api/companies/me
     */
    public function getUserCompany(Request $request): JsonResponse
    {
        $company = $this->companyService->getUserCompany($request->user());

        return response()->json([
            'success' => true,
            'data'    => [
                'company' => $company ? $this->companyData($company) : null,
            ],
        ]);
    }

    /**
     * GET /api/companies
     */
    public function index(Request $request): JsonResponse
    {
        $companies = \App\Models\Company::with('sector')->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $companies,
        ]);
    }

    private function companyData($company): array
    {
        return [
            'id'      => $company->id,
            'name'    => $company->name,
            'siret'   => $company->siret,
            'website' => $company->website,
            'sector'  => $company->sector ? ['id' => $company->sector->id, 'name' => $company->sector->name] : ($company->sector_name ? ['id' => null, 'name' => $company->sector_name] : null),
            'created_at' => $company->created_at,
        ];
    }
}
