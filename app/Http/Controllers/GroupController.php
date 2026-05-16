<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Sector;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->loadMissing('profile');

        $userSectorIds = array_unique(array_merge(
            $user->profile?->looking_for      ?? [],
            $user->profile?->services_offered ?? [],
            $user->profile?->sector_ids       ?? [],
        ));

        $sectors = Sector::orderBy('name')->get();

        $query = Group::with(['sector', 'creator'])
            ->withCount('members')
            ->where('is_public', true);

        if ($request->filled('category')) {
            $query->where('sector_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $groups = $query->orderBy('members_count', 'desc')->get();

        $recommended    = $groups->filter(fn($g) => in_array($g->sector_id, $userSectorIds));
        $others         = $groups->filter(fn($g) => !in_array($g->sector_id, $userSectorIds));
        $memberGroupIds = $user->groups()->pluck('groups.id')->toArray();

        return view('groups.index', compact(
            'groups', 'sectors', 'recommended', 'others',
            'userSectorIds', 'memberGroupIds'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'sector_id'   => ['nullable', 'integer', 'exists:sectors,id'],
            'cover_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $photoPath = null;
        if ($request->hasFile('cover_photo')) {
            $photoPath = $request->file('cover_photo')->store('groups', 'public');
        }

        $group = Group::create([
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
            'sector_id'     => $validated['sector_id'] ?? null,
            'cover_color'   => $validated['cover_color'] ?? '#1E8F88',
            'cover_photo'   => $photoPath,
            'created_by'    => $request->user()->id,
            'is_public'     => true,
            'members_count' => 1,
        ]);

        $group->members()->attach($request->user()->id, ['role' => 'admin']);

        return redirect()->route('groups.index')
            ->with('success', 'Groupe "' . $group->name . '" créé avec succès !');
    }

    public function join(Request $request, int $id)
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        if (!$group->isMember($user->id)) {
            $group->members()->attach($user->id, ['role' => 'member']);
            $group->increment('members_count');
        }

        return back()->with('success', 'Vous avez rejoint le groupe "' . $group->name . '".');
    }

    public function leave(Request $request, int $id)
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        if ($group->isMember($user->id)) {
            $group->members()->detach($user->id);
            $group->decrement('members_count');
        }

        return back()->with('success', 'Vous avez quitté le groupe.');
    }
}
