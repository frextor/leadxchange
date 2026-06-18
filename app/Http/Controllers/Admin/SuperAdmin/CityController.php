<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CityController extends Controller
{
    public function index(Request $request): View
    {
        $query = City::with('country');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        $cities    = $query->orderBy('name')->paginate(40)->withQueryString();
        $countries = Country::orderBy('name')->get(['id', 'name', 'flag']);
        $stats = [
            'total'     => City::count(),
            'active'    => City::where('is_active', true)->count(),
            'inactive'  => City::where('is_active', false)->count(),
            'countries' => City::whereNotNull('country_id')->distinct('country_id')->count('country_id'),
        ];

        return view('admin.super_admin.cities.index', compact('cities', 'countries', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'country_id' => ['required', 'exists:countries,id'],
        ]);
        City::create($request->only('name', 'country_id'));
        return back()->with('success', "Région \"{$request->name}\" créée.");
    }

    public function update(Request $request, City $city): RedirectResponse
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'country_id' => ['required', 'exists:countries,id'],
        ]);
        $city->update($request->only('name', 'country_id'));
        return back()->with('success', 'Région mise à jour.');
    }

    public function toggle(City $city): RedirectResponse
    {
        $city->update(['is_active' => !$city->is_active]);
        $status = $city->is_active ? 'activée' : 'désactivée';
        return back()->with('success', "Région \"{$city->name}\" {$status}.");
    }

    public function destroy(City $city): RedirectResponse
    {
        $city->delete();
        return back()->with('success', 'Région supprimée.');
    }
}
