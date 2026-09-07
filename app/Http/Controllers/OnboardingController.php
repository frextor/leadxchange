<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Market;
use App\Models\Sector;
use App\Services\ProfileVideoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Guided "welcome" wizard shown once, right after registration, before the
 * user reaches the dashboard for the first time (photo, entreprise,
 * préférences, présentation). Every field is optional — the user can skip
 * any step — the wizard just needs to be gone through once.
 */
class OnboardingController extends Controller
{
    public function __construct(private ProfileVideoService $profileVideoService) {}

    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        $user->loadMissing(['profile', 'company', 'region']);

        return view('onboarding.show', [
            'user'    => $user,
            'sectors' => Sector::orderBy('name')->get(['id', 'name']),
            'regions' => City::active()->orderBy('name')->get(['id', 'name']),
            'markets' => Market::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'avatar'                => ['nullable', 'image', 'max:4096'],
            'company_id'            => ['nullable', 'integer', 'exists:companies,id'],
            'company_name'          => ['nullable', 'string', 'max:255'],
            'region_id'             => ['nullable', 'integer', 'exists:cities,id'],
            'looking_for'           => ['nullable', 'array'],
            'looking_for.*'         => ['integer', 'exists:sectors,id'],
            'services_offered'      => ['nullable', 'array'],
            'services_offered.*'    => ['integer', 'exists:sectors,id'],
            'market_addressed_id'   => ['nullable', 'integer', 'exists:markets,id'],
            'market_target_id'      => ['nullable', 'integer', 'exists:markets,id'],
            'bio'                   => ['nullable', 'string', 'max:500'],
            'linkedin'              => ['nullable', 'string', 'max:255', 'url'],
            'presentation_video'    => ['nullable', 'file', 'mimes:mp4,mov,webm,avi,m4v', 'max:51200'],
        ]);

        // ── Avatar ───────────────────────────────────────────────────────
        if ($request->hasFile('avatar')) {
            if ($user->profile?->avatar) {
                Storage::disk('public')->delete($user->profile->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->profile()->updateOrCreate(['user_id' => $user->id], ['avatar' => $path]);
        }

        // ── Entreprise ───────────────────────────────────────────────────
        if (!empty($data['company_id'])) {
            $user->update(['company_id' => $data['company_id']]);
        } elseif (!empty($data['company_name'])) {
            $company = \App\Models\Company::create(['name' => $data['company_name']]);
            $user->update(['company_id' => $company->id]);
        }

        // ── Préférences ──────────────────────────────────────────────────
        if (!empty($data['region_id'])) {
            $user->update(['region_id' => $data['region_id']]);
        }

        $user->profile()->updateOrCreate(['user_id' => $user->id], array_filter([
            'looking_for'          => $data['looking_for']         ?? null,
            'services_offered'     => $data['services_offered']    ?? null,
            'market_addressed_id'  => $data['market_addressed_id'] ?? null,
            'market_target_id'     => $data['market_target_id']    ?? null,
            'bio'                  => $data['bio']                 ?? null,
            'linkedin'             => $data['linkedin']             ?? null,
        ], fn($v) => $v !== null));

        // ── Présentation video (best-effort — never blocks onboarding) ────
        if ($request->hasFile('presentation_video')) {
            try {
                $this->profileVideoService->store($user, $request->file('presentation_video'));
            } catch (\Throwable $e) {
                Log::warning('Onboarding presentation video upload skipped', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        $user->update(['onboarding_completed' => true]);

        return redirect()->route('dashboard')
            ->with('success', 'Votre profil est prêt. Bienvenue sur LeadXchange !');
    }

    /**
     * Skip the whole wizard from any step.
     */
    public function skip(Request $request)
    {
        $request->user()->update(['onboarding_completed' => true]);

        return redirect()->route('dashboard');
    }
}
