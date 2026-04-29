<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * CompanyService
 * 
 * Handles all company-related business logic.
 */
class CompanyService
{
    /**
     * Create a new company and associate with user.
     *
     * @param User $user
     * @param array $data
     * @return Company
     * @throws \Exception
     */
    public function createCompany(User $user, array $data): Company
    {
        // Check if profile is completed
        if (!$user->hasCompletedProfile()) {
            throw new \Exception('Please complete your profile before creating a company');
        }

        // Check if user already has a company
        if ($user->company_id) {
            throw new \Exception('User already belongs to a company');
        }

        DB::beginTransaction();

        try {
            // Create company
            $company = Company::create([
                'name' => $data['name'],
                'siret' => $data['siret'],
                'sector' => $data['sector'],
                'website' => $data['website'] ?? null,
            ]);

            // Update user with company and mark onboarding as completed
            $user->update([
                'company_id' => $company->id,
                'onboarding_completed' => true,
            ]);

            DB::commit();

            Log::info('Company created', [
                'user_id' => $user->id,
                'company_id' => $company->id
            ]);

            return $company;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Company creation failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Join an existing company.
     *
     * @param User $user
     * @param int $companyId
     * @return Company
     * @throws \Exception
     */
    public function joinCompany(User $user, int $companyId): Company
    {
        $company = Company::find($companyId);

        if (!$company) {
            throw new \Exception('Company not found');
        }

        if ($user->company_id) {
            throw new \Exception('User already belongs to a company');
        }

        DB::beginTransaction();

        try {
            $user->update([
                'company_id' => $company->id,
                'onboarding_completed' => true,
            ]);

            DB::commit();

            Log::info('User joined company', [
                'user_id' => $user->id,
                'company_id' => $company->id
            ]);

            return $company;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to join company', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Search companies by name or SIRET.
     *
     * @param string $query
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchCompanies(string $query, int $limit = 10)
    {
        if (strlen($query) < 3) {
            return collect([]);
        }

        return Company::where('name', 'LIKE', "%{$query}%")
            ->orWhere('siret', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get(['id', 'name', 'siret', 'sector', 'website']);
    }

    /**
     * Get user's company.
     *
     * @param User $user
     * @return Company|null
     */
    public function getUserCompany(User $user): ?Company
    {
        return $user->company;
    }
}
