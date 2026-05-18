<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $userSectorIds  = array_unique(array_merge(
            $user->profile?->looking_for      ?? [],
            $user->profile?->services_offered ?? [],
            $user->profile?->sector_ids       ?? [],
        ));
        $userCityId = $user->city_id;

        $query = Group::with(['sector:id,name', 'creator:id,first_name,last_name', 'city:id,name'])
            ->withCount('members')
            ->where('is_public', true);

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->filled('category')) {
            $query->where('sector_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $groups = $query->orderBy('members_count', 'desc')->get();

        $memberGroupIds = $user->groups()->pluck('groups.id')->toArray();

        $mapped = $groups->map(fn($g) => $this->formatGroup($g, $memberGroupIds, $userSectorIds, $userCityId, $user->id));

        return response()->json([
            'data' => [
                'nearby'      => $mapped->filter(fn($g) => $g['is_nearby'])->values(),
                'recommended' => $mapped->filter(fn($g) => $g['is_recommended'] && !$g['is_nearby'])->values(),
                'others'      => $mapped->filter(fn($g) => !$g['is_recommended'] && !$g['is_nearby'])->values(),
            ],
            'meta' => [
                'total'       => $groups->count(),
                'nearby'      => $mapped->filter(fn($g) => $g['is_nearby'])->count(),
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
        $group = Group::with(['sector:id,name', 'creator:id,first_name,last_name', 'city:id,name'])
            ->withCount('members')
            ->findOrFail($id);

        $user           = $request->user();
        $memberGroupIds = $user->groups()->pluck('groups.id')->toArray();

        return response()->json([
            'data' => $this->formatGroup($group, $memberGroupIds, [], $user->city_id, $user->id),
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
            'city_id'     => ['nullable', 'integer', 'exists:cities,id'],
            'cover_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpeg,png,webp,jpg', 'max:3072'],
            'is_public'   => ['nullable', 'boolean'],
        ]);

        $user       = $request->user();
        $coverPhoto = null;

        if ($request->hasFile('cover_photo')) {
            $coverPhoto = $request->file('cover_photo')->store('group-covers', 'public');
        }

        $group = Group::create([
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
            'sector_id'     => $validated['sector_id'] ?? null,
            'city_id'       => $validated['city_id'] ?? $user->city_id,
            'cover_color'   => $validated['cover_color'] ?? '#1E8F88',
            'cover_photo'   => $coverPhoto,
            'created_by'    => $user->id,
            'is_public'     => $validated['is_public'] ?? true,
            'members_count' => 1,
        ]);

        $group->members()->attach($user->id, ['role' => 'admin']);
        $group->load(['sector:id,name', 'creator:id,first_name,last_name', 'city:id,name']);
        $group->loadCount('members');

        return response()->json([
            'message' => 'Group created successfully.',
            'data'    => $this->formatGroup($group, [$group->id], [], $user->city_id, $user->id),
        ], 201);
    }

    /**
     * PUT /api/groups/{id}
     * Update a group (admin only).
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        $pivot = $group->members()->where('user_id', $user->id)->first();
        if (!$pivot || $pivot->pivot->role !== 'admin') {
            return response()->json(['message' => 'Only group admins can update this group.'], 403);
        }

        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'sector_id'   => ['nullable', 'integer', 'exists:sectors,id'],
            'city_id'     => ['nullable', 'integer', 'exists:cities,id'],
            'cover_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpeg,png,webp,jpg', 'max:3072'],
            'is_public'   => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('cover_photo')) {
            if ($group->cover_photo) {
                Storage::disk('public')->delete($group->cover_photo);
            }
            $validated['cover_photo'] = $request->file('cover_photo')->store('group-covers', 'public');
        }

        $group->update(array_filter($validated, fn($v) => $v !== null || array_key_exists('description', $validated)));
        $group->load(['sector:id,name', 'creator:id,first_name,last_name', 'city:id,name']);
        $group->loadCount('members');

        $memberGroupIds = $user->groups()->pluck('groups.id')->toArray();

        return response()->json([
            'message' => 'Group updated successfully.',
            'data'    => $this->formatGroup($group, $memberGroupIds, [], $user->city_id, $user->id),
        ]);
    }

    /**
     * POST /api/groups/{id}/invite
     * Invite a user to the group (admin only).
     * Body: { "user_id": 5 }
     */
    public function invite(int $id, Request $request): JsonResponse
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        $pivot = $group->members()->where('user_id', $user->id)->first();
        if (!$pivot || $pivot->pivot->role !== 'admin') {
            return response()->json(['message' => 'Only group admins can invite members.'], 403);
        }

        $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);

        $invitee = User::findOrFail($request->user_id);

        if ($group->isMember($invitee->id)) {
            return response()->json(['message' => 'User is already a member of this group.'], 422);
        }

        $group->members()->attach($invitee->id, ['role' => 'member']);
        $group->increment('members_count');

        return response()->json([
            'message' => 'User invited successfully.',
            'user'    => [
                'id'         => $invitee->id,
                'first_name' => $invitee->first_name,
                'last_name'  => $invitee->last_name,
            ],
        ]);
    }

    /**
     * GET /api/groups/{id}/members
     * List members of a group.
     */
    public function members(int $id, Request $request): JsonResponse
    {
        $group = Group::findOrFail($id);

        $members = $group->members()
            ->select('users.id', 'users.first_name', 'users.last_name')
            ->withPivot('role', 'joined_at')
            ->get()
            ->map(fn($u) => [
                'id'         => $u->id,
                'first_name' => $u->first_name,
                'last_name'  => $u->last_name,
                'role'       => $u->pivot->role,
                'joined_at'  => $u->pivot->joined_at,
            ]);

        return response()->json([
            'data'  => $members,
            'total' => $members->count(),
        ]);
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

    private function formatGroup(Group $group, array $memberGroupIds, array $userSectorIds, ?int $userCityId = null, ?int $authUserId = null): array
    {
        return [
            'id'              => $group->id,
            'name'            => $group->name,
            'description'     => $group->description,
            'cover_color'     => $group->cover_color,
            'cover_photo_url' => $group->cover_photo ? Storage::url($group->cover_photo) : null,
            'is_public'       => $group->is_public,
            'members_count'   => $group->members_count,
            'is_member'       => in_array($group->id, $memberGroupIds),
            'is_creator'      => $authUserId !== null && $group->created_by === $authUserId,
            'is_recommended'  => in_array($group->sector_id, $userSectorIds),
            'is_nearby'       => $userCityId !== null && $group->city_id === $userCityId,
            'sector'          => $group->sector  ? ['id' => $group->sector->id,  'name' => $group->sector->name]  : null,
            'city'            => $group->city    ? ['id' => $group->city->id,    'name' => $group->city->name]    : null,
            'creator'         => $group->creator ? ['id' => $group->creator->id, 'name' => $group->creator->first_name . ' ' . $group->creator->last_name] : null,
            'created_at'      => $group->created_at,
        ];
    }
}
