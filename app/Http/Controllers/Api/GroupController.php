<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\GroupPost;
use App\Models\GroupPostComment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GroupController extends Controller
{
    /**
     * GET /api/groups
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
        $userCityId     = $user->city_id;
        $memberGroupIds = $user->groups()->pluck('groups.id')->toArray();
        $userRole       = $user->groups()->pluck('role', 'groups.id')->toArray();

        $page    = max(1, (int) ($request->page    ?? 1));
        $perPage = min(50, max(1, (int) ($request->per_page ?? 20)));

        // ── Pending invitations (paginated) ──────────────────────────────
        $invitedGroupIds = GroupInvitation::where('user_id', $user->id)
            ->where('status', 'pending')
            ->pluck('group_id')
            ->toArray();
        $invitedQuery = Group::with(['sector:id,name', 'creator:id,first_name,last_name', 'city:id,name'])
            ->withCount('members')
            ->whereIn('id', $invitedGroupIds);
        if ($request->filled('search')) $invitedQuery->where('name', 'like', '%' . $request->search . '%');
        $invitedPaginator = $invitedQuery->paginate($perPage, ['*'], 'page', $page);
        $invited = $invitedPaginator->getCollection()
            ->map(fn($g) => $this->formatGroup($g, $memberGroupIds, $userSectorIds, $userCityId, $user->id, $userRole))
            ->values();

        // ── My groups (paginated) ─────────────────────────────────────────
        $myGroupsQuery = Group::with(['sector:id,name', 'creator:id,first_name,last_name', 'city:id,name'])
            ->withCount('members')
            ->whereIn('id', $memberGroupIds);
        if ($request->filled('search')) $myGroupsQuery->where('name', 'like', '%' . $request->search . '%');
        $myGroupsPaginator = $myGroupsQuery->paginate($perPage, ['*'], 'page', $page);
        $myGroups = $myGroupsPaginator->getCollection()
            ->map(fn($g) => $this->formatGroup($g, $memberGroupIds, $userSectorIds, $userCityId, $user->id, $userRole))
            ->values();

        // ── Public groups not yet joined (paginated) ──────────────────────
        $publicQuery = Group::with(['sector:id,name', 'creator:id,first_name,last_name', 'city:id,name'])
            ->withCount('members')
            ->where('is_public', true)
            ->whereNotIn('id', $memberGroupIds);

        if ($request->filled('city_id'))  $publicQuery->where('city_id', $request->city_id);
        if ($request->filled('category')) $publicQuery->where('sector_id', $request->category);
        if ($request->filled('search'))   $publicQuery->where('name', 'like', '%' . $request->search . '%');

        $publicPaginator = $publicQuery->orderBy('members_count', 'desc')->paginate($perPage, ['*'], 'page', $page);
        $mapped = $publicPaginator->getCollection()
            ->map(fn($g) => $this->formatGroup($g, $memberGroupIds, $userSectorIds, $userCityId, $user->id, $userRole));

        $lastPage = max($invitedPaginator->lastPage(), $myGroupsPaginator->lastPage(), $publicPaginator->lastPage());

        return response()->json([
            'data' => [
                'invited'     => $invited,
                'my_groups'   => $myGroups,
                'nearby'      => $mapped->filter(fn($g) => $g['is_nearby'])->values(),
                'recommended' => $mapped->filter(fn($g) => $g['is_recommended'] && !$g['is_nearby'])->values(),
                'others'      => $mapped->filter(fn($g) => !$g['is_recommended'] && !$g['is_nearby'])->values(),
            ],
            'meta' => [
                'current_page' => $page,
                'last_page'    => $lastPage,
                'per_page'     => $perPage,
            ],
        ]);
    }

    /**
     * GET /api/groups/mine?page=1&per_page=15
     * Groups the authenticated user belongs to (paginated).
     */
    public function mine(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min((int) ($request->per_page ?? 15), 50);

        $paginator = $user->groups()
            ->with(['sector:id,name', 'city:id,name'])
            ->withCount('members')
            ->withPivot('role')
            ->orderByPivot('role')   // owner → admin → member
            ->latest('group_user.created_at')
            ->paginate($perPage);

        $userRole = $paginator->getCollection()
            ->pluck('pivot.role', 'id')
            ->toArray();

        $data = $paginator->getCollection()->map(
            fn($g) => $this->formatGroup($g, $paginator->pluck('id')->toArray(), [], $user->city_id, $user->id, $userRole)
        );

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    /**
     * GET /api/groups/{id}
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $group = Group::with(['sector:id,name', 'creator:id,first_name,last_name', 'city:id,name'])
            ->withCount('members')
            ->findOrFail($id);

        $user           = $request->user();
        $memberGroupIds = $user->groups()->pluck('groups.id')->toArray();
        $userRole       = $user->groups()->pluck('role', 'groups.id')->toArray();

        return response()->json([
            'data' => $this->formatGroup($group, $memberGroupIds, [], $user->city_id, $user->id, $userRole),
        ]);
    }

    /**
     * POST /api/groups
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

        $group->members()->attach($user->id, ['role' => 'owner']);
        $group->load(['sector:id,name', 'creator:id,first_name,last_name', 'city:id,name']);
        $group->loadCount('members');

        return response()->json([
            'message' => 'Group created successfully.',
            'data'    => $this->formatGroup($group, [$group->id], [], $user->city_id, $user->id, [$group->id => 'owner']),
        ], 201);
    }

    /**
     * PUT /api/groups/{id}
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        if (!$group->isAdmin($user->id)) {
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
            if ($group->cover_photo) Storage::disk('public')->delete($group->cover_photo);
            $validated['cover_photo'] = $request->file('cover_photo')->store('group-covers', 'public');
        }

        $group->update(array_filter($validated, fn($v) => $v !== null));
        $group->load(['sector:id,name', 'creator:id,first_name,last_name', 'city:id,name']);
        $group->loadCount('members');

        $memberGroupIds = $user->groups()->pluck('groups.id')->toArray();
        $userRole       = $user->groups()->pluck('role', 'groups.id')->toArray();

        return response()->json([
            'message' => 'Group updated successfully.',
            'data'    => $this->formatGroup($group, $memberGroupIds, [], $user->city_id, $user->id, $userRole),
        ]);
    }

    /**
     * DELETE /api/groups/{id}
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $group = Group::findOrFail($id);

        if (!$group->isOwner($request->user()->id)) {
            return response()->json(['message' => 'Only the group owner can delete this group.'], 403);
        }

        if ($group->cover_photo) Storage::disk('public')->delete($group->cover_photo);
        $group->delete();

        return response()->json(['message' => 'Group deleted successfully.']);
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

        GroupInvitation::where('group_id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->update(['status' => 'accepted']);

        return response()->json([
            'message'       => 'Joined group successfully.',
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

        if ($group->isOwner($user->id)) {
            return response()->json(['message' => 'The owner cannot leave the group. Transfer ownership first.'], 422);
        }

        if (!$group->isMember($user->id)) {
            return response()->json(['message' => 'You are not a member.'], 422);
        }

        $group->members()->detach($user->id);
        $group->decrement('members_count');

        return response()->json(['message' => 'Left group successfully.']);
    }

    /**
     * GET /api/groups/{id}/members
     */
    public function members(int $id, Request $request): JsonResponse
    {
        $group = Group::findOrFail($id);

        abort_if(!$group->is_public && !$group->isMember($request->user()->id), 403);

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

        return response()->json(['data' => $members, 'total' => $members->count()]);
    }

    /**
     * POST /api/groups/{id}/invite
     * Creates a pending GroupInvitation (admin/owner only).
     */
    public function invite(int $id, Request $request): JsonResponse
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        if (!$group->isAdmin($user->id)) {
            return response()->json(['message' => 'Only group admins can invite members.'], 403);
        }

        $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);
        $targetId = (int) $request->user_id;

        if ($group->isMember($targetId)) {
            return response()->json(['message' => 'User is already a member of this group.'], 422);
        }

        GroupInvitation::updateOrCreate(
            ['group_id' => $id, 'user_id' => $targetId],
            ['invited_by' => $user->id, 'status' => 'pending']
        );

        $invitee = User::find($targetId);

        return response()->json([
            'message' => 'Invitation sent successfully.',
            'user'    => ['id' => $invitee->id, 'first_name' => $invitee->first_name, 'last_name' => $invitee->last_name],
        ]);
    }

    /**
     * GET /api/groups/invitations
     * Pending invitations for the authenticated user.
     */
    public function invitations(Request $request): JsonResponse
    {
        $invitations = GroupInvitation::with(['group:id,name,cover_color,cover_photo', 'inviter:id,first_name,last_name'])
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->map(fn($inv) => [
                'id'         => $inv->id,
                'group'      => [
                    'id'              => $inv->group->id,
                    'name'            => $inv->group->name,
                    'cover_color'     => $inv->group->cover_color,
                    'cover_photo_url' => $inv->group->cover_photo ? Storage::disk('public')->url($inv->group->cover_photo) : null,
                ],
                'inviter'    => $inv->inviter ? ['id' => $inv->inviter->id, 'name' => $inv->inviter->first_name . ' ' . $inv->inviter->last_name] : null,
                'created_at' => $inv->created_at,
            ]);

        return response()->json(['data' => $invitations, 'total' => $invitations->count()]);
    }

    /**
     * POST /api/groups/invitations/{invId}/accept
     */
    public function acceptInvitation(int $invId, Request $request): JsonResponse
    {
        $invitation = GroupInvitation::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->findOrFail($invId);

        $group = $invitation->group;

        if (!$group->isMember($invitation->user_id)) {
            $group->members()->attach($invitation->user_id, ['role' => 'member']);
            $group->increment('members_count');
        }

        $invitation->update(['status' => 'accepted']);

        return response()->json(['message' => 'Invitation accepted. You are now a member of "' . $group->name . '".']);
    }

    /**
     * POST /api/groups/invitations/{invId}/decline
     */
    public function declineInvitation(int $invId, Request $request): JsonResponse
    {
        $invitation = GroupInvitation::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->findOrFail($invId);

        $invitation->update(['status' => 'declined']);

        return response()->json(['message' => 'Invitation declined.']);
    }

    /**
     * POST /api/groups/{id}/members/{userId}/promote
     */
    public function promote(int $id, int $userId, Request $request): JsonResponse
    {
        $group = Group::findOrFail($id);

        if (!$group->isOwner($request->user()->id)) {
            return response()->json(['message' => 'Only the group owner can promote members.'], 403);
        }

        if (!$group->isMember($userId)) {
            return response()->json(['message' => 'User is not a member of this group.'], 404);
        }

        $group->members()->updateExistingPivot($userId, ['role' => 'admin']);

        return response()->json(['message' => 'Member promoted to admin.', 'user_id' => $userId, 'role' => 'admin']);
    }

    /**
     * POST /api/groups/{id}/members/{userId}/demote
     */
    public function demote(int $id, int $userId, Request $request): JsonResponse
    {
        $group = Group::findOrFail($id);

        if (!$group->isOwner($request->user()->id)) {
            return response()->json(['message' => 'Only the group owner can demote admins.'], 403);
        }

        if (!$group->isMember($userId)) {
            return response()->json(['message' => 'User is not a member of this group.'], 404);
        }

        $group->members()->updateExistingPivot($userId, ['role' => 'member']);

        return response()->json(['message' => 'Admin demoted to member.', 'user_id' => $userId, 'role' => 'member']);
    }

    /**
     * DELETE /api/groups/{id}/members/{userId}
     */
    public function removeMember(int $id, int $userId, Request $request): JsonResponse
    {
        $group  = Group::findOrFail($id);
        $caller = $request->user();

        if (!$group->isAdmin($caller->id)) {
            return response()->json(['message' => 'Only group admins can remove members.'], 403);
        }

        $targetRole = $group->userRole($userId);
        if (in_array($targetRole, ['owner', 'admin']) && !$group->isOwner($caller->id)) {
            return response()->json(['message' => 'You cannot remove an admin or owner.'], 403);
        }

        if ($userId === $caller->id && $targetRole === 'owner') {
            return response()->json(['message' => 'The owner cannot remove themselves.'], 422);
        }

        $group->members()->detach($userId);
        $group->decrement('members_count');

        return response()->json(['message' => 'Member removed from group.']);
    }

    /**
     * GET /api/groups/{id}/posts
     */
    public function posts(int $id, Request $request): JsonResponse
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        abort_if(!$group->is_public && !$group->isMember($user->id), 403);

        $posts = GroupPost::with(['author:id,first_name,last_name', 'comments.author:id,first_name,last_name'])
            ->where('group_id', $id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $posts->map(fn($p) => $this->formatPost($p, $user->id)),
            'meta' => ['current_page' => $posts->currentPage(), 'last_page' => $posts->lastPage(), 'total' => $posts->total()],
        ]);
    }

    /**
     * POST /api/groups/{id}/posts
     */
    public function storePost(int $id, Request $request): JsonResponse
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        if (!$group->isMember($user->id)) {
            return response()->json(['message' => 'You must be a member to post.'], 403);
        }

        $request->validate([
            'body'  => ['required_without:photo', 'nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('groups/posts', 'public');
        }

        $post = GroupPost::create([
            'group_id'   => $id,
            'user_id'    => $user->id,
            'type'       => 'post',
            'body'       => $request->body ?? '',
            'photo_path' => $photoPath,
        ]);

        $post->load('author:id,first_name,last_name');

        return response()->json(['message' => 'Post created.', 'data' => $this->formatPost($post, $user->id)], 201);
    }

    /**
     * DELETE /api/groups/{id}/posts/{postId}
     */
    public function destroyPost(int $id, int $postId, Request $request): JsonResponse
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();
        $post  = GroupPost::where('group_id', $id)->findOrFail($postId);

        if ($post->user_id !== $user->id && !$group->isAdmin($user->id)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($post->photo_path) Storage::disk('public')->delete($post->photo_path);
        $post->delete();

        return response()->json(['message' => 'Post deleted.']);
    }

    /**
     * POST /api/groups/{id}/posts/{postId}/comments
     */
    public function storeComment(int $id, int $postId, Request $request): JsonResponse
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        if (!$group->isMember($user->id)) {
            return response()->json(['message' => 'You must be a member to comment.'], 403);
        }

        $post = GroupPost::where('group_id', $id)->findOrFail($postId);

        $request->validate(['body' => ['required', 'string', 'max:1000']]);

        $comment = GroupPostComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body'    => $request->body,
        ]);

        $comment->load('author:id,first_name,last_name');

        return response()->json([
            'message' => 'Comment added.',
            'data'    => [
                'id'         => $comment->id,
                'body'       => $comment->body,
                'created_at' => $comment->created_at,
                'author'     => ['id' => $comment->author->id, 'name' => $comment->author->first_name . ' ' . $comment->author->last_name],
            ],
        ], 201);
    }

    /**
     * POST /api/groups/{id}/activities
     */
    public function storeActivity(int $id, Request $request): JsonResponse
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        if (!$group->isAdmin($user->id)) {
            return response()->json(['message' => 'Only admins and the owner can create activities.'], 403);
        }

        $request->validate([
            'activity_title' => ['required', 'string', 'max:150'],
            'body'           => ['nullable', 'string', 'max:1000'],
            'activity_date'  => ['required', 'date', 'after:now'],
        ]);

        $post = GroupPost::create([
            'group_id'       => $id,
            'user_id'        => $user->id,
            'type'           => 'activity',
            'activity_title' => $request->activity_title,
            'body'           => $request->body ?? '',
            'activity_date'  => $request->activity_date,
        ]);

        $post->load('author:id,first_name,last_name');

        return response()->json(['message' => 'Activity created.', 'data' => $this->formatPost($post, $user->id)], 201);
    }

    private function formatPost(GroupPost $post, int $authUserId): array
    {
        return [
            'id'             => $post->id,
            'type'           => $post->type,
            'body'           => $post->body,
            'photo_url'      => $post->photo_url,
            'activity_title' => $post->activity_title,
            'activity_date'  => $post->activity_date?->toIso8601String(),
            'created_at'     => $post->created_at,
            'is_own'         => $post->user_id === $authUserId,
            'author'         => $post->author ? ['id' => $post->author->id, 'name' => $post->author->first_name . ' ' . $post->author->last_name] : null,
            'comments'       => $post->relationLoaded('comments') ? $post->comments->map(fn($c) => [
                'id'         => $c->id,
                'body'       => $c->body,
                'created_at' => $c->created_at,
                'author'     => $c->author ? ['id' => $c->author->id, 'name' => $c->author->first_name . ' ' . $c->author->last_name] : null,
            ]) : [],
        ];
    }

    private function formatGroup(Group $group, array $memberGroupIds, array $userSectorIds, ?int $userCityId, ?int $authUserId, array $userRoles = []): array
    {
        return [
            'id'              => $group->id,
            'name'            => $group->name,
            'description'     => $group->description,
            'cover_color'     => $group->cover_color,
            'cover_photo_url' => $group->cover_photo ? Storage::disk('public')->url($group->cover_photo) : null,
            'is_public'       => $group->is_public,
            'members_count'   => $group->members_count,
            'is_member'       => in_array($group->id, $memberGroupIds),
            'user_role'       => $userRoles[$group->id] ?? null,
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
