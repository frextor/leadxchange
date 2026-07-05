<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SectorController extends Controller
{
    public function index(): View
    {
        $sectors = Sector::withCount('companies')->orderBy('name')->paginate(40);
        $stats = [
            'total'             => Sector::count(),
            'with_companies'    => Sector::has('companies')->count(),
            'without_companies' => Sector::doesntHave('companies')->count(),
        ];
        return view('admin.super_admin.sectors.index', compact('sectors', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:100', 'unique:sectors,name']]);
        Sector::create(['name' => $request->name]);
        ActivityLogger::log('admin.sector.created', "Secteur \"{$request->name}\" créé");
        return back()->with('success', "Secteur \"{$request->name}\" créé.");
    }

    public function update(Request $request, Sector $sector): RedirectResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:100', "unique:sectors,name,{$sector->id}"]]);
        $sector->update(['name' => $request->name]);
        ActivityLogger::log('admin.sector.updated', "Secteur \"{$sector->name}\" mis à jour");
        return back()->with('success', 'Secteur mis à jour.');
    }

    public function destroy(Sector $sector): RedirectResponse
    {
        if ($sector->companies()->exists()) {
            return back()->with('error', 'Impossible : des entreprises utilisent ce secteur.');
        }
        $name = $sector->name;
        $sector->delete();
        ActivityLogger::log('admin.sector.deleted', "Secteur \"{$name}\" supprimé");
        return back()->with('success', 'Secteur supprimé.');
    }
}
