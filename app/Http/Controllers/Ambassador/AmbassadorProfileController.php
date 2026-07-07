<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Models\AmbassadorProfile;
use App\Services\AmbassadorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmbassadorProfileController extends Controller
{
    public function __construct(private AmbassadorService $service) {}

    public function index(Request $request): View
    {
        $ambassador = $request->user()->load('city', 'region', 'company', 'profile');
        $profile    = $this->service->syncProfile($ambassador);
        $kpis       = $this->service->dashboardKpis($ambassador);

        return view('ambassador.profile.index', compact('ambassador', 'profile', 'kpis'));
    }

    public function update(Request $request): RedirectResponse
    {
        $ambassador = $request->user();

        $data = $request->validate([
            'biography'   => 'nullable|string|max:2000',
            'linkedin_url'=> 'nullable|url|max:255',
        ]);

        AmbassadorProfile::updateOrCreate(
            ['user_id' => $ambassador->id],
            $data
        );

        return back()->with('success', 'Profil ambassadeur mis à jour.');
    }
}
