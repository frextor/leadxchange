<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Country::withCount('cities');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $countries = $query->orderBy('name')->paginate(40)->withQueryString();
        return view('admin.super_admin.countries.index', compact('countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'size:2', 'unique:countries,code'],
            'flag' => ['nullable', 'string', 'max:10'],
        ]);
        Country::create($request->only('name', 'code', 'flag'));
        ActivityLogger::log('admin.country.created', "Pays \"{$request->name}\" créé");
        return back()->with('success', "Pays \"{$request->name}\" créé.");
    }

    public function update(Request $request, Country $country): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'size:2', "unique:countries,code,{$country->id}"],
            'flag' => ['nullable', 'string', 'max:10'],
        ]);
        $country->update($request->only('name', 'code', 'flag'));
        ActivityLogger::log('admin.country.updated', "Pays \"{$country->name}\" mis à jour");
        return back()->with('success', 'Pays mis à jour.');
    }

    public function destroy(Country $country): RedirectResponse
    {
        if ($country->cities()->exists()) {
            return back()->with('error', 'Impossible : des villes sont liées à ce pays.');
        }
        $name = $country->name;
        $country->delete();
        ActivityLogger::log('admin.country.deleted', "Pays \"{$name}\" supprimé");
        return back()->with('success', 'Pays supprimé.');
    }
}
