<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     *   { name, siret, sector_id, website?, position? }
     *
     * Join body:
     *   { existing_company_id, position? }
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            // ── Join existing company ──────────────────────────────────────
            if ($request->filled('existing_company_id')) {
                $request->validate([
                    'existing_company_id' => ['required', 'integer', 'exists:companies,id'],
                    'position'            => ['nullable', 'string', 'max:100'],
                ]);

                $company = $this->companyService->joinCompany(
                    $user,
                    $request->integer('existing_company_id'),
                    $request->input('position')
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

            // ── Create new company ─────────────────────────────────────────
            $validated = $request->validate([
                'name'      => ['required', 'string', 'max:255'],
                'siret'     => ['required', 'string', 'size:14', 'unique:companies,siret', 'regex:/^[0-9]{14}$/'],
                'sector_id' => ['required', 'integer', 'exists:sectors,id'],
                'website'   => ['nullable', 'url', 'max:255'],
                'position'  => ['nullable', 'string', 'max:100'],
            ]);

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
            'id'         => $company->id,
            'name'       => $company->name,
            'siret'      => $company->siret,
            'sector_id'  => $company->sector_id,
            'sector'     => $company->sector?->name ?? $company->sector_name,
            'website'    => $company->website,
            'created_at' => $company->created_at,
        ];
    }
}
