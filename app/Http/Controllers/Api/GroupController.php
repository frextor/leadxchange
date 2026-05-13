<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Sector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * GET /api/groups
     * List groups — recommended first based on user sector interests.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing('profile');

        $userSectorIds = array_unique(array_merge(
            $user->profile?->looking_for      ?? [],
            $user->profile?->services_offered ?? [],
            $user->profile?->sector_ids       ?? [],
        ));

        $query = Group::with(['sector:id,name', 'creator:id,first_name,last_name'])
            ->withCount('members')
            ->where('is_public', true);

        if ($request->filled('category')) {
            $query->where('sector_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $groups = $query->orderBy('members_count', 'desc')->get();

        $memberGroupIds = $user->groups()->pluck('groups.id')->toArray();

        $mapped = $groups->map(fn($g) => $this->formatGroup($g, $memberGroupIds, $userSectorIds));

        return response()->json([
            'data' => [
                'recommended' => $mapped->filter(fn($g) => $g['is_recommended'])->values(),
                'others'      => $mapped->filter(fn($g) => !$g['is_recommended'])->values(),
            ],
            'meta' => [
                'total'       => $groups->count(),
                'recommended' => $mapped->filter(fn($g) => $g['is_recommended'])->count(),
            ],
        ]);
    }

    /**
     * GET /api/groups/{id}
     * Single group details.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $group = Group::with(['sector:id,name', 'creator:id,first_name,last_name'])
            ->withCount('members')
            ->findOrFail($id);

        $memberGroupIds = $request->user()->groups()->pluck('groups.id')->toArray();

        return response()->json([
            'data' => $this->formatGroup($group, $memberGroupIds, []),
        ]);
    }

    /**
     * POST /api/groups
     * Create a group.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'sector_id'   => ['nullable', 'integer', 'exists:sectors,id'],
            'cover_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $group = Group::create([
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
            'sector_id'     => $validated['sector_id'] ?? null,
            'cover_color'   => $validated['cover_color'] ?? '#1E8F88',
            'created_by'    => $request->user()->id,
            'is_public'     => true,
            'members_count' => 1,
        ]);

        $group->members()->attach($request->user()->id, ['role' => 'admin']);
        $group->load(['sector:id,name', 'creator:id,first_name,last_name']);
        $group->loadCount('members');

        return response()->json([
            'message' => 'Group created successfully.',
            'data'    => $this->formatGroup($group, [$group->id], []),
        ], 201);
    }

    /**
     * POST /api/groups/{id}/join
     */
    public function join(int $id, Request $request): JsonResponse
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        if ($group->isMember($user->id)) {
            return response()->json(['message' => 'Already a member.'], 422);
        }

        $group->members()->attach($user->id, ['role' => 'member']);
        $group->increment('members_count');

        return response()->json([
            'message'      => 'Joined group successfully.',
            'members_count' => $group->members_count + 1,
        ]);
    }

    /**
     * DELETE /api/groups/{id}/leave
     */
    public function leave(int $id, Request $request): JsonResponse
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        if (!$group->isMember($user->id)) {
            return response()->json(['message' => 'You are not a member.'], 422);
        }

        $group->members()->detach($user->id);
        $group->decrement('members_count');

        return response()->json(['message' => 'Left group successfully.']);
    }

    private function formatGroup(Group $group, array $memberGroupIds, array $userSectorIds): array
    {
        return [
            'id'             => $group->id,
            'name'           => $group->name,
            'description'    => $group->description,
            'cover_color'    => $group->cover_color,
            'is_public'      => $group->is_public,
            'members_count'  => $group->members_count,
            'is_member'      => in_array($group->id, $memberGroupIds),
            'is_recommended' => in_array($group->sector_id, $userSectorIds),
            'sector'         => $group->sector ? ['id' => $group->sector->id, 'name' => $group->sector->name] : null,
            'creator'        => $group->creator ? ['id' => $group->creator->id, 'name' => $group->creator->first_name . ' ' . $group->creator->last_name] : null,
            'created_at'     => $group->created_at,
        ];
    }
}
