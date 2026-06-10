<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InterestController extends Controller
{
    public function index(): View
    {
        $interests = Interest::withCount('users')->orderBy('name')->paginate(40);
        return view('admin.super_admin.interests.index', compact('interests'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:10'],
        ]);
        Interest::create($request->only('name', 'icon'));
        return back()->with('success', "Intérêt \"{$request->name}\" créé.");
    }

    public function update(Request $request, Interest $interest): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:10'],
        ]);
        $interest->update($request->only('name', 'icon'));
        return back()->with('success', 'Intérêt mis à jour.');
    }

    public function destroy(Interest $interest): RedirectResponse
    {
        $interest->delete();
        return back()->with('success', 'Intérêt supprimé.');
    }
}
