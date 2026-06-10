<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Sector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(Request $request): View
    {
        $query = Group::with(['creator', 'sector', 'city']);

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->filled('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }
        if ($request->filled('visibility')) {
            $query->where('is_public', $request->visibility === 'public');
        }

        $groups  = $query->orderByDesc('created_at')->paginate(25)->withQueryString();
        $sectors = Sector::orderBy('name')->get(['id', 'name']);

        $counts = [
            'total'   => Group::count(),
            'public'  => Group::where('is_public', true)->count(),
            'private' => Group::where('is_public', false)->count(),
        ];

        return view('admin.groups.index', compact('groups', 'sectors', 'counts'));
    }

    public function destroy(Group $group): RedirectResponse
    {
        $name = $group->name;
        $group->delete();

        return redirect()->route('admin.groups.index')
            ->with('success', "Groupe \"{$name}\" supprimé.");
    }
}
