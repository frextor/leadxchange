<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegionSelectorController extends Controller
{
    /**
     * Stocke la ville/région choisie en session et redirige vers la page précédente.
     * La clé de session `selected_city_id` est lue par EventController, GroupController,
     * DashboardController et MemberController.
     */
    public function select(Request $request): RedirectResponse
    {
        $request->validate([
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
        ]);

        $cityId = $request->filled('city_id') ? (int) $request->city_id : null;

        // « Toutes les villes » = null explicite : la clé existe, donc les lecteurs ne retombent pas sur la ville du profil
        $request->session()->put('selected_city_id', $cityId);

        // Redirect to the intended page (events, groups, dashboard, connections…)
        $redirect = $request->input('redirect');
        if ($redirect && in_array($redirect, ['events', 'groups', 'dashboard', 'connections'])) {
            return redirect()->route($redirect . ($redirect === 'dashboard' ? '' : '.index'));
        }

        return redirect()->back();
    }
}
