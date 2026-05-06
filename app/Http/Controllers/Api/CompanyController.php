<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CompanyController (REFACTORED)
 * 
 * Thin controller - only handles HTTP requests/responses.
 * All business logic is in CompanyService.
 */
class CompanyController extends Controller
{
    protected CompanyService $companyService;
    protected AuthService $authService;

    /**
     * Inject services via constructor.
     */
    public function __construct(CompanyService $companyService, AuthService $authService)
    {
        $this->companyService = $companyService;
        $this->authService = $authService;
    }

    /**
     * Search companies.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        // Service handles search logic
        $companies = $this->companyService->searchCompanies($query);

        return response()->json([
            'success' => true,
            'data' => $companies
        ]);
    }

    /**
     * Create new company OR join existing company.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Case 1: Join existing company
            if ($request->filled('existing_company_id')) {
                $company = $this->companyService->joinCompany(
                    $request->user(),
                    $request->input('existing_company_id')
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Joined company successfully',
                    'data' => [
                        'company' => [
                            'id' => $company->id,
                            'name' => $company->name,
                            'siret' => $company->siret,
                            'sector_id' => $company->sector_id,
                            'sector'    => $company->sector_name,
                            'website' => $company->website,
                        ],
                        'action' => 'joined'
                    ]
                ]);
            }

            // Case 2: Create new company
            $validated = $request->validate([
                'name'      => ['required', 'string', 'max:255'],
                'siret'     => ['required', 'string', 'size:14', 'unique:companies,siret', 'regex:/^[0-9]{14}$/'],
                'sector_id' => ['required', 'integer', 'exists:sectors,id'],
                'website'   => ['nullable', 'url', 'max:255'],
            ]);

            $company = $this->companyService->createCompany(
                $request->user(),
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Company created successfully',
                'data' => [
                    'company' => [
                        'id' => $company->id,
                        'name' => $company->name,
                        'siret' => $company->siret,
                        'sector_id' => $company->sector_id,
                            'sector'    => $company->sector_name,
                        'website' => $company->website,
                    ],
                    'action' => 'created'
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get user's company.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserCompany(Request $request): JsonResponse
    {
        $company = $this->companyService->getUserCompany($request->user());

        if (!$company) {
            return response()->json([
                'success' => true,
                'data' => [
                    'company' => null
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'siret' => $company->siret,
                    'sector_id' => $company->sector_id,
                            'sector'    => $company->sector_name,
                    'website' => $company->website,
                    'created_at' => $company->created_at,
                ]
            ]
        ]);
    }

    /**
     * Get all companies (admin only).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Optional: Add admin check here
        $companies = \App\Models\Company::with('users')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $companies
        ]);
    }
}
