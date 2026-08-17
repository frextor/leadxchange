<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegionSelectorController extends Controller
{
    /**
     * Stocke la ville/région choisie en session et redirige vers la page précédente.
     * La clé de session `selected_city_id` est lue par EventController et GroupController.
     */
    public function select(Request $request): RedirectResponse
    {
        $request->validate([
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
        ]);

        $cityId = $request->filled('city_id') ? (int) $request->city_id : null;

        if ($cityId) {
            $request->session()->put('selected_city_id', $cityId);
        } else {
            // "Toutes les régions" → supprime le filtre
            $request->session()->forget('selected_city_id');
        }

        // Redirect to the intended page (events, groups, dashboard…)
        $redirect = $request->input('redirect');
        if ($redirect && in_array($redirect, ['events', 'groups', 'dashboard'])) {
            return redirect()->route($redirect . ($redirect === 'dashboard' ? '' : '.index'));
        }

        return redirect()->back();
    }
}
