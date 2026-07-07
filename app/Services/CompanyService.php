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
        DB::beginTransaction();

        try {
            // Create company
            $company = Company::create([
                'name'      => $data['name'],
                'siret'     => $data['siret'],
                'sector_id' => $data['sector_id'] ?? null,
                'website'   => $data['website'] ?? null,
            ]);
            $company->load('sector');

            // Update user with company and mark onboarding as completed
            $user->update([
                'company_id'           => $company->id,
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
        $company = Company::with('sector')->find($companyId);

        if (!$company) {
            throw new \Exception('Company not found');
        }

        DB::beginTransaction();

        try {
            $user->update([
                'company_id'           => $company->id,
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

        return Company::with('sector')
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('siret', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get(['id', 'name', 'siret', 'sector_id', 'website']);
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
