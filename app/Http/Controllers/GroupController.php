<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EnforcePlanLimits;
use App\Jobs\NotifyUsersNewGroupJob;
use App\Models\City;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\GroupPost;
use App\Models\GroupPostComment;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GroupController extends Controller
{
    use EnforcePlanLimits;

    public function index(Request $request)
    {
        $user = $request->user();
        $user->loadMissing('profile');

        $userSectorIds  = array_unique(array_merge(
            $user->profile?->looking_for      ?? [],
            $user->profile?->services_offered ?? [],
            $user->profile?->sector_ids       ?? [],
        ));

        $sectors        = Sector::orderBy('name')->get();
        $cities         = City::active()->orderBy('name')->get();
        $memberGroupIds = $user->groups()->pluck('groups.id')->toArray();
        $userRoles      = $user->groups()->pluck('group_user.role', 'groups.id')->toArray();

        // ── Pending invitations ──────────────────────────────────
        $pendingInvitations = GroupInvitation::with(['group.sector', 'inviter'])
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->get();

        // ── My groups (member of) ─────────────────────────────────
        $myGroupsQuery = Group::with(['sector', 'creator', 'city'])
            ->withCount('members')
            ->whereIn('id', $memberGroupIds);
        if ($request->filled('search')) {
            $myGroupsQuery->where('name', 'like', '%' . $request->search . '%');
        }
        $myGroups = $myGroupsQuery->get()
            ->sortBy(fn($g) => match($userRoles[$g->id] ?? 'member') {
                'owner' => 0, 'admin' => 1, default => 2,
            })->values();

        // ── Public groups not yet joined ──────────────────────────
        $publicQuery = Group::with(['sector', 'creator', 'city'])
            ->withCount('members')
            ->where('is_public', true)
            ->whereNotIn('id', $memberGroupIds);

        if ($request->filled('category')) $publicQuery->where('sector_id', $request->category);
        if ($request->filled('search'))   $publicQuery->where('name', 'like', '%' . $request->search . '%');

        $publicGroups = $publicQuery->orderBy('members_count', 'desc')->get();

        $nearby      = $publicGroups->filter(fn($g) => $user->city_id && $g->city_id === $user->city_id)->values();
        $recommended = $publicGroups->filter(fn($g) => in_array($g->sector_id, $userSectorIds)
            && (!$user->city_id || $g->city_id !== $user->city_id))->values();
        $others      = $publicGroups->filter(fn($g) => !in_array($g->sector_id, $userSectorIds)
            && (!$user->city_id || $g->city_id !== $user->city_id))->values();

        // Used only for sidebar sector counts
        $groups = $publicGroups->merge($myGroups);

        // Connections list for the invite modal (admin/owner only needs it)
        $groupConnections = User::whereIn('id', $user->connectionIds())
            ->with('profile:id,user_id,job_title')
            ->orderBy('first_name')
            ->get()
            ->map(fn($u) => [
                'id'        => $u->id,
                'name'      => $u->first_name . ' ' . $u->last_name,
                'job_title' => $u->profile?->job_title,
            ]);

        return view('groups.index', compact(
            'groups', 'sectors', 'cities',
            'myGroups', 'userRoles', 'memberGroupIds',
            'nearby', 'recommended', 'others',
            'userSectorIds', 'pendingInvitations', 'groupConnections'
        ));
    }

    public function show(Request $request, int $id)
    {
        $user  = $request->user();
        $group = Group::with(['sector', 'city', 'creator.profile'])
            ->withCount('members')
            ->findOrFail($id);

        abort_if(!$group->is_public && !$group->isMember($user->id), 403);

        $userRole = $group->userRole($user->id);
        $isMember = $userRole !== null;
        $isAdmin  = in_array($userRole, ['owner', 'admin']);
        $isOwner  = $userRole === 'owner';

        $members  = $group->members()->with('profile', 'company:id,name')->orderByRaw("FIELD(group_user.role,'owner','admin','member')")->get();
        $posts    = GroupPost::with(['author.profile', 'comments.author.profile'])
            ->where('group_id', $id)
            ->latest()
            ->paginate(20);

        $connections = $isAdmin
            ? User::with('profile')
                ->whereIn('id', $user->connectionIds())
                ->whereNotIn('id', $members->pluck('id')->toArray())
                ->orderBy('first_name')
                ->get()
            : collect();

        return view('groups.show', compact(
            'group', 'members', 'posts', 'isMember', 'isAdmin', 'isOwner', 'userRole', 'connections'
        ));
    }

    // ── Posts ───────────────────────────────────────────────

    public function storePost(Request $request, int $id)
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        abort_unless($group->isMember($user->id), 403);

        $request->validate([
            'body'  => ['required_without:photo', 'nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('groups/posts', 'public');
        }

        GroupPost::create([
            'group_id'   => $id,
            'user_id'    => $user->id,
            'type'       => 'post',
            'body'       => $request->body ?? '',
            'photo_path' => $photoPath,
        ]);

        return back()->with('success', 'Publication ajoutée.');
    }

    public function storeActivity(Request $request, int $id)
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        abort_unless($group->isAdmin($user->id), 403, 'Réservé aux admins et au owner.');

        $request->validate([
            'activity_title' => ['required', 'string', 'max:150'],
            'body'           => ['nullable', 'string', 'max:1000'],
            'activity_date'  => ['required', 'date', 'after:now'],
        ]);

        GroupPost::create([
            'group_id'       => $id,
            'user_id'        => $user->id,
            'type'           => 'activity',
            'activity_title' => $request->activity_title,
            'body'           => $request->body ?? '',
            'activity_date'  => $request->activity_date,
        ]);

        return back()->with('success', 'Activité créée.');
    }

    public function destroyPost(Request $request, int $id, int $postId)
    {
        $user  = $request->user();
        $group = Group::findOrFail($id);
        $post  = GroupPost::where('group_id', $id)->findOrFail($postId);

        abort_unless($post->user_id === $user->id || $group->isAdmin($user->id), 403);

        if ($post->photo_path) Storage::disk('public')->delete($post->photo_path);
        $post->delete();

        return back()->with('success', 'Publication supprimée.');
    }

    public function storeComment(Request $request, int $id, int $postId)
    {
        $group = Group::findOrFail($id);
        abort_unless($group->isMember($request->user()->id), 403);

        $post = GroupPost::where('group_id', $id)->findOrFail($postId);
        $request->validate(['body' => ['required', 'string', 'max:1000']]);

        GroupPostComment::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'body'    => $request->body,
        ]);

        return back()->with('success', 'Commentaire ajouté.');
    }

    // ── Invitations ──────────────────────────────────────────

    public function invite(Request $request, int $id)
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        abort_unless($group->isAdmin($user->id), 403);

        $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);

        $targetId = (int) $request->user_id;

        if ($group->isMember($targetId)) {
            return back()->with('info', 'Cet utilisateur est déjà membre du groupe.');
        }

        GroupInvitation::updateOrCreate(
            ['group_id' => $id, 'user_id' => $targetId],
            ['invited_by' => $user->id, 'status' => 'pending']
        );

        return back()->with('success', 'Invitation envoyée.');
    }

    public function acceptInvitation(Request $request, int $invId)
    {
        if ($redirect = $this->requirePermission('can_join_pole')) {
            return $redirect;
        }

        $invitation = GroupInvitation::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->findOrFail($invId);

        $group = $invitation->group;

        if (!$group->isMember($invitation->user_id)) {
            $group->members()->attach($invitation->user_id, ['role' => 'member']);
            $group->increment('members_count');
        }

        $invitation->update(['status' => 'accepted']);

        return redirect()->route('groups.show', $group->id)
            ->with('success', 'Vous avez rejoint le groupe "' . $group->name . '".');
    }

    public function declineInvitation(Request $request, int $invId)
    {
        $invitation = GroupInvitation::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->findOrFail($invId);

        $invitation->update(['status' => 'declined']);

        return back()->with('success', 'Invitation refusée.');
    }

    // ── Member management ────────────────────────────────────

    public function removeMember(Request $request, int $id, int $userId)
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        abort_unless($group->isAdmin($user->id), 403);

        $targetRole = $group->userRole($userId);
        // Admins cannot remove owners or other admins
        if (in_array($targetRole, ['owner', 'admin']) && !$group->isOwner($user->id)) {
            return back()->with('error', 'Vous n\'avez pas la permission de retirer un admin ou le owner.');
        }
        // Prevent owner from removing themselves (use leave instead)
        abort_if($userId === $user->id && $targetRole === 'owner', 403);

        $group->members()->detach($userId);
        $group->decrement('members_count');

        return back()->with('success', 'Membre retiré du groupe.');
    }

    public function promoteAdmin(Request $request, int $id, int $userId)
    {
        $group = Group::findOrFail($id);
        abort_unless($group->isOwner($request->user()->id), 403, 'Réservé au owner.');

        $group->members()->updateExistingPivot($userId, ['role' => 'admin']);

        return back()->with('success', 'Membre promu administrateur.');
    }

    public function demoteAdmin(Request $request, int $id, int $userId)
    {
        $group = Group::findOrFail($id);
        abort_unless($group->isOwner($request->user()->id), 403, 'Réservé au owner.');

        $group->members()->updateExistingPivot($userId, ['role' => 'member']);

        return back()->with('success', 'Administrateur rétrogradé en membre.');
    }

    // ── Group CRUD ───────────────────────────────────────────

    public function store(Request $request)
    {
        if ($redirect = $this->requirePermission('can_create_pole', 'Votre plan ne permet pas de créer un groupe.')) {
            return $redirect;
        }

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'sector_id'   => ['nullable', 'integer', 'exists:sectors,id'],
            'city_id'     => ['nullable', 'integer', 'exists:cities,id'],
            'cover_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user      = $request->user();
        $photoPath = null;
        if ($request->hasFile('cover_photo')) {
            $photoPath = $request->file('cover_photo')->store('groups', 'public');
        }

        $group = Group::create([
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
            'sector_id'     => $validated['sector_id'] ?? null,
            'city_id'       => $validated['city_id'] ?? $user->city_id,
            'cover_color'   => $validated['cover_color'] ?? '#1E8F88',
            'cover_photo'   => $photoPath,
            'created_by'    => $user->id,
            'is_public'     => true,
            'members_count' => 1,
        ]);

        $group->members()->attach($user->id, ['role' => 'owner']);

        NotifyUsersNewGroupJob::dispatch($group);

        return redirect()->route('groups.show', $group->id)
            ->with('success', 'Groupe "' . $group->name . '" créé avec succès !');
    }

    public function destroy(Request $request, int $id)
    {
        $group = Group::findOrFail($id);
        abort_unless($group->isOwner($request->user()->id), 403, 'Réservé au owner.');

        if ($group->cover_photo && !str_starts_with($group->cover_photo, 'http')) Storage::disk('public')->delete($group->cover_photo);
        $group->delete();

        return redirect()->route('groups.index')
            ->with('success', 'Groupe supprimé.');
    }

    public function join(Request $request, int $id)
    {
        if ($redirect = $this->requirePermission('can_join_pole', 'Votre plan ne permet pas de rejoindre des groupes.')) {
            return $redirect;
        }

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

        if ($group->isOwner($user->id)) {
            return back()->with('error', 'Le owner ne peut pas quitter le groupe. Transférez la propriété d\'abord.');
        }

        if ($group->isMember($user->id)) {
            $group->members()->detach($user->id);
            $group->decrement('members_count');
        }

        return redirect()->route('groups.index')->with('success', 'Vous avez quitté le groupe.');
    }
}
