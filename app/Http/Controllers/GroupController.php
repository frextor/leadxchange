<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EnforcePlanLimits;
use App\Jobs\NotifyUsersNewGroupJob;
use App\Services\ActivityLogger;
use App\Models\City;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\GroupPost;
use App\Models\GroupPostComment;
use App\Models\Sector;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GroupController extends Controller
{
    use EnforcePlanLimits;

    public function index(Request $request)
    {
        $user = $request->user();
        $user->loadMissing(['profile', 'city']);

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
        $invitedGroupIds = $pendingInvitations->pluck('group_id')->toArray();

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
            ->whereNotIn('id', array_values(array_unique(array_merge($memberGroupIds, $invitedGroupIds))));

        if ($request->filled('category')) $publicQuery->where('sector_id', $request->category);
        if ($request->filled('search'))   $publicQuery->where('name', 'like', '%' . $request->search . '%');

        $publicGroups = $publicQuery->orderBy('members_count', 'desc')->get();

        // Ville active : session en priorité (sélecteur de région), sinon ville du profil
        $activeCityId = session('selected_city_id', $user->city_id);

        $nearby      = $publicGroups->filter(fn($g) => $activeCityId && $g->city_id === $activeCityId)->values();
        $recommended = $publicGroups->filter(fn($g) => in_array($g->sector_id, $userSectorIds)
            && (!$activeCityId || $g->city_id !== $activeCityId))->values();
        $others      = $publicGroups->filter(fn($g) => !in_array($g->sector_id, $userSectorIds)
            && (!$activeCityId || $g->city_id !== $activeCityId))->values();

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

        $memberIds   = $members->pluck('id')->toArray();
        $canInviteAll = $isAdmin && ($user->isConsulForCurrentCity() || $user->isAmbassador());

        $connections = $isAdmin
            ? User::with('profile')
                ->whereIn('id', $user->connectionIds())
                ->whereNotIn('id', $memberIds)
                ->orderBy('first_name')
                ->get()
            : collect();

        $hasPendingRequest = !$isMember && GroupInvitation::where('group_id', $id)
            ->where('user_id', $user->id)
            ->where('type', GroupInvitation::TYPE_REQUEST)
            ->where('status', 'pending')
            ->exists();

        $pendingRequests = $isAdmin
            ? GroupInvitation::with('user.profile')
                ->where('group_id', $id)
                ->where('type', GroupInvitation::TYPE_REQUEST)
                ->where('status', 'pending')
                ->latest()
                ->get()
            : collect();

        return view('groups.show', compact(
            'group', 'members', 'posts', 'isMember', 'isAdmin', 'isOwner', 'userRole',
            'connections', 'canInviteAll', 'hasPendingRequest', 'pendingRequests'
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

    public function searchUsers(Request $request)
    {
        $user  = $request->user();
        $q     = trim($request->get('q', ''));
        $groupId = (int) $request->get('group_id', 0);

        abort_unless($user->isConsulForCurrentCity() || $user->isAmbassador(), 403);

        $query = User::where('role', 'user')
            ->where('id', '!=', $user->id);

        if ($groupId) {
            $group = Group::findOrFail($groupId);
            abort_unless($group->isAdmin($user->id), 403);
            $memberIds = $group->members()->pluck('users.id')->toArray();
            $query->whereNotIn('id', $memberIds);
        }

        if ($q !== '') {
            $query->where(fn($q2) => $q2
                ->where('first_name', 'like', "%{$q}%")
                ->orWhere('last_name',  'like', "%{$q}%")
                ->orWhere('email',      'like', "%{$q}%")
            );
        }

        $users = $query->select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->limit(20)
            ->get()
            ->map(fn($u) => [
                'id'   => $u->id,
                'name' => $u->first_name . ' ' . $u->last_name,
                'email'=> $u->email,
            ]);

        return response()->json($users);
    }

    public function invite(Request $request, int $id)
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        abort_unless($group->isAdmin($user->id), 403);

        $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);

        $targetId = (int) $request->user_id;

        if ($group->isBlocked($targetId)) {
            return back()->with('error', 'Cet utilisateur est bloqué de ce groupe.');
        }

        if ($group->isMember($targetId)) {
            return back()->with('info', 'Cet utilisateur est déjà membre du groupe.');
        }

        GroupInvitation::updateOrCreate(
            ['group_id' => $id, 'user_id' => $targetId],
            ['invited_by' => $user->id, 'status' => 'pending', 'type' => GroupInvitation::TYPE_INVITATION]
        );

        $target = \App\Models\User::find($targetId);
        if ($target) {
            try {
                \App\Models\Notification::storeForUser(
                    $target,
                    'group_invitation',
                    'Invitation à rejoindre un groupe',
                    "{$user->first_name} {$user->last_name} vous invite à rejoindre le groupe « {$group->name} ».",
                    ['url' => route('groups.index'), 'group_id' => $id]
                );
            } catch (\Throwable) {}

            try {
                \Illuminate\Support\Facades\Mail::to($target->email)->send(
                    new \App\Mail\SystemNotificationMail(
                        recipientName: $target->first_name,
                        title:         'Vous avez été invité(e) à rejoindre « ' . $group->name . ' »',
                        body:          '<strong>' . $user->first_name . ' ' . $user->last_name . '</strong> vous invite à rejoindre le groupe <strong>' . $group->name . '</strong> sur LeadXchange. Connectez-vous pour accepter ou refuser cette invitation.',
                        actionLabel:   'Voir l\'invitation',
                        actionUrl:     route('groups.index'),
                        templateKey:   'group_invitation',
                    )
                );
            } catch (\Throwable) {}
        }

        ActivityLogger::log('group.invitation_sent', "Invitation envoyée à {$target?->first_name} {$target?->last_name} pour le groupe « {$group->name} »", $user->id, $group, ['invited_user_id' => $targetId]);

        return back()->with('success', 'Invitation envoyée.');
    }

    /**
     * Invite tous les membres de la région de l'ambassadeur dans un groupe.
     * Réservé aux ambassadeurs admin/owner du groupe.
     */
    public function inviteRegion(Request $request, int $id)
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        abort_unless($group->isAdmin($user->id), 403);
        abort_unless($user->isAmbassador(), 403);

        // Utilise les champs verrouillés à la nomination de l'ambassadeur
        $regionId = $user->ambassador_region_id ?? $user->region_id;
        $cityId   = $user->ambassador_city_id   ?? $user->city_id;

        $existingIds = $group->members()->pluck('users.id')
            ->merge(
                GroupInvitation::where('group_id', $id)
                    ->whereIn('status', ['pending', 'accepted'])
                    ->pluck('user_id')
            )
            ->unique()
            ->push($user->id)
            ->toArray();

        $query = User::where('role', 'user')->whereNotIn('id', $existingIds);

        if ($regionId) {
            $query->where('region_id', $regionId);
        } elseif ($cityId) {
            $query->where('city_id', $cityId);
        } else {
            return back()->with('error', 'Votre région n\'est pas définie.');
        }

        $members = $query->get(['id', 'first_name', 'last_name', 'email']);

        $sent = 0;
        foreach ($members as $member) {
            if ($group->isBlocked($member->id)) continue;

            GroupInvitation::updateOrCreate(
                ['group_id' => $id, 'user_id' => $member->id],
                ['invited_by' => $user->id, 'status' => 'pending', 'type' => GroupInvitation::TYPE_INVITATION]
            );

            try {
                \App\Models\Notification::storeForUser(
                    $member,
                    'group_invitation',
                    'Invitation à rejoindre un groupe',
                    "{$user->first_name} {$user->last_name} vous invite à rejoindre le groupe « {$group->name} ».",
                    ['url' => route('groups.index'), 'group_id' => $id]
                );
            } catch (\Throwable) {}

            $sent++;
        }

        ActivityLogger::log('group.region_invite', "Région entière invitée dans « {$group->name} » ({$sent} membres)", $user->id, $group);

        return back()->with('success', "{$sent} membre(s) de votre région invité(s) dans ce groupe.");
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

        if ($group->isBlocked($invitation->user_id)) {
            return back()->with('error', 'Vous avez été bloqué de ce groupe.');
        }

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

        if ($group->isOwner($userId)) {
            return back()->with('error', 'Le owner ne peut pas être retiré.');
        }

        if ($group->isBlocked($userId)) {
            return back()->with('error', 'Ce membre est déjà bloqué de ce groupe.');
        }

        if (!$group->isMember($userId)) {
            return back()->with('error', 'Cet utilisateur n\'est pas membre du groupe.');
        }

        $targetRole = $group->userRole($userId);
        // Admins cannot remove owners or other admins
        if (in_array($targetRole, ['owner', 'admin']) && !$group->isOwner($user->id)) {
            return back()->with('error', 'Vous n\'avez pas la permission de retirer un admin ou le owner.');
        }
        // Prevent owner from removing themselves (use leave instead)
        abort_if($userId === $user->id, 403);

        $group->members()->detach($userId);
        $group->decrement('members_count');

        return back()->with('success', 'Membre retiré du groupe.');
    }

    public function promoteAdmin(Request $request, int $id, int $userId)
    {
        $group = Group::findOrFail($id);
        abort_unless($group->isOwner($request->user()->id), 403, 'Réservé au owner.');

        if ($group->isBlocked($userId)) {
            return back()->with('error', 'Ce membre est bloqué de ce groupe.');
        }

        if ($group->isOwner($userId)) {
            return back()->with('error', 'Le owner ne peut pas être promu.');
        }

        if (!$group->isMember($userId)) {
            return back()->with('error', 'Cet utilisateur n\'est pas membre du groupe.');
        }

        if ($group->userRole($userId) === 'admin') {
            return back()->with('info', 'Ce membre est déjà administrateur.');
        }

        $group->members()->updateExistingPivot($userId, ['role' => 'admin']);

        $member = User::find($userId);
        if ($member) {
            app(FirebaseService::class)->sendGroupAdminAssignedNotification($member, $group, $request->user());
        }

        return back()->with('success', 'Membre promu administrateur.');
    }

    public function demoteAdmin(Request $request, int $id, int $userId)
    {
        $group = Group::findOrFail($id);
        abort_unless($group->isOwner($request->user()->id), 403, 'Réservé au owner.');

        if ($group->isBlocked($userId)) {
            return back()->with('error', 'Ce membre est bloqué de ce groupe.');
        }

        if ($group->isOwner($userId)) {
            return back()->with('error', 'Le owner ne peut pas être rétrogradé.');
        }

        if (!$group->isMember($userId)) {
            return back()->with('error', 'Cet utilisateur n\'est pas membre du groupe.');
        }

        $group->members()->updateExistingPivot($userId, ['role' => 'member']);

        return back()->with('success', 'Administrateur rétrogradé en membre.');
    }

    public function blockMember(Request $request, int $id, int $userId)
    {
        $group = Group::findOrFail($id);
        $user  = $request->user();

        abort_unless($group->isAdmin($user->id), 403);

        if ($group->isOwner($userId)) {
            return back()->with('error', 'Le owner ne peut pas être bloqué.');
        }

        if ($userId === $user->id) {
            return back()->with('error', 'Vous ne pouvez pas vous bloquer vous-même.');
        }

        if ($group->isBlocked($userId)) {
            return back()->with('error', 'Ce membre est déjà bloqué de ce groupe.');
        }

        if (!$group->isMember($userId)) {
            return back()->with('error', 'Cet utilisateur n\'est pas membre du groupe.');
        }

        $targetRole = $group->userRole($userId);
        if (in_array($targetRole, ['owner', 'admin']) && !$group->isOwner($user->id)) {
            return back()->with('error', 'Vous n\'avez pas la permission de bloquer un admin ou le owner.');
        }

        $group->members()->updateExistingPivot($userId, ['blocked_at' => now()]);
        GroupInvitation::where('group_id', $id)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->update(['status' => 'declined']);
        $group->decrement('members_count');

        return back()->with('success', 'Membre bloqué.');
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

        $user = $request->user();

        // ── Restriction région pour les Consuls ──────────────────────────────
        // Un consul peut créer uniquement dans sa propre région.
        // Un ambassadeur peut créer dans n'importe quelle région.
        if ($user->isConsul() && ! $user->isAmbassador()) {
            $consulRegionId = $user->consul_region_id ?? $user->region_id;
            if ($consulRegionId) {
                $chosenCityId = $validated['city_id'] ?? null;
                if ($chosenCityId) {
                    $cityRegion = \App\Models\City::find($chosenCityId)?->region_id ?? null;
                    if ($cityRegion && (int) $cityRegion !== (int) $consulRegionId) {
                        return back()->withErrors(['city_id' => 'En tant que Consul, vous ne pouvez créer des groupes que dans votre région.'])->withInput();
                    }
                }
            }
        }
        $photoPath = null;
        if ($request->hasFile('cover_photo')) {
            $photoPath = $request->file('cover_photo')->store('groups', 'public');
        }

        // Use the consul's locked city (not current profile city) for group creation
        $cityId = $user->isConsulForCurrentCity()
            ? ($user->consul_city_id ?? $user->city_id)
            : ($validated['city_id'] ?? $user->city_id);

        $group = Group::create([
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
            'sector_id'     => $validated['sector_id'] ?? null,
            'city_id'       => $cityId,
            'cover_color'   => $validated['cover_color'] ?? '#1E8F88',
            'cover_photo'   => $photoPath,
            'created_by'    => $user->id,
            'is_public'     => true,
            'members_count' => 1,
        ]);

        $group->members()->attach($user->id, ['role' => 'owner']);

        NotifyUsersNewGroupJob::dispatch($group);

        ActivityLogger::log('group.created', "Groupe « {$group->name} » créé", $user->id, $group);

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

        $group = Group::with(['creator'])->findOrFail($id);
        $user  = $request->user();

        if ($group->isBlocked($user->id)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Vous avez été bloqué de ce groupe.'], 403)
                : back()->with('error', 'Vous avez été bloqué de ce groupe.');
        }

        if ($group->isMember($user->id)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Vous êtes déjà membre de ce groupe.'], 422)
                : back()->with('info', 'Vous êtes déjà membre de ce groupe.');
        }

        $existing = GroupInvitation::where('group_id', $id)
            ->where('user_id', $user->id)
            ->where('type', GroupInvitation::TYPE_REQUEST)
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Votre demande est déjà en attente.'], 422)
                : back()->with('info', 'Votre demande est déjà en attente.');
        }

        GroupInvitation::create([
            'group_id'   => $id,
            'user_id'    => $user->id,
            'invited_by' => null,
            'type'       => GroupInvitation::TYPE_REQUEST,
            'status'     => 'pending',
        ]);

        // Notify all group admins + owner
        $adminIds = $group->members()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->pluck('users.id');

        $admins = \App\Models\User::whereIn('id', $adminIds)->get();

        foreach ($admins as $admin) {
            try {
                \App\Models\Notification::storeForUser(
                    $admin,
                    'group_join_request',
                    'Nouvelle demande d\'adhésion',
                    "{$user->first_name} {$user->last_name} souhaite rejoindre le groupe « {$group->name} ».",
                    ['url' => route('groups.show', $id), 'group_id' => $id, 'user_id' => $user->id]
                );
            } catch (\Throwable) {}

            try {
                \Illuminate\Support\Facades\Mail::to($admin->email)->send(
                    new \App\Mail\SystemNotificationMail(
                        recipientName: $admin->first_name,
                        title:         'Demande d\'adhésion au groupe « ' . $group->name . ' »',
                        body:          '<strong>' . $user->first_name . ' ' . $user->last_name . '</strong> souhaite rejoindre votre groupe <strong>' . $group->name . '</strong>. Consultez les demandes en attente pour accepter ou refuser.',
                        actionLabel:   'Voir la demande',
                        actionUrl:     route('groups.show', $id),
                        templateKey:   'group_join_request',
                    )
                );
            } catch (\Throwable) {}
        }

        ActivityLogger::log('group.join_requested', "{$user->first_name} {$user->last_name} a demandé à rejoindre le groupe « {$group->name} »", $user->id, $group);

        return $request->expectsJson()
            ? response()->json(['message' => 'Demande envoyée — le responsable du groupe vous répondra bientôt.'], 200)
            : back()->with('success', 'Demande envoyée — le responsable du groupe vous répondra bientôt.');
    }

    public function approveRequest(Request $request, int $id, int $userId)
    {
        $group = Group::findOrFail($id);
        abort_unless($group->isAdmin($request->user()->id), 403);

        $invitation = GroupInvitation::where('group_id', $id)
            ->where('user_id', $userId)
            ->where('type', GroupInvitation::TYPE_REQUEST)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($group->isBlocked($userId)) {
            return back()->with('error', 'Cet utilisateur est bloqué de ce groupe.');
        }

        if (!$group->isMember($userId)) {
            $group->members()->attach($userId, ['role' => 'member']);
            $group->increment('members_count');
        }
        $invitation->update(['status' => 'accepted']);

        $requester = \App\Models\User::find($userId);
        if ($requester) {
            try {
                \App\Models\Notification::storeForUser(
                    $requester,
                    'group_join_accepted',
                    'Demande acceptée',
                    "Votre demande pour rejoindre le groupe « {$group->name} » a été acceptée. Bienvenue !",
                    ['url' => route('groups.show', $id), 'group_id' => $id]
                );
            } catch (\Throwable) {}

            try {
                \Illuminate\Support\Facades\Mail::to($requester->email)->send(
                    new \App\Mail\SystemNotificationMail(
                        recipientName: $requester->first_name,
                        title:         'Vous avez rejoint « ' . $group->name . ' » !',
                        body:          'Bonne nouvelle ! Votre demande pour rejoindre le groupe <strong>' . $group->name . '</strong> a été <strong>acceptée</strong>. Vous pouvez maintenant accéder au groupe et participer aux échanges.',
                        actionLabel:   'Accéder au groupe',
                        actionUrl:     route('groups.show', $id),
                        templateKey:   'group_join_accepted',
                    )
                );
            } catch (\Throwable) {}
        }

        ActivityLogger::log('group.join_approved', "Demande de {$requester?->first_name} {$requester?->last_name} acceptée pour le groupe « {$group->name} »", $request->user()->id, $group, ['approved_user_id' => $userId]);

        return back()->with('success', 'Demande acceptée — ' . ($requester?->first_name ?? 'Utilisateur') . ' est maintenant membre.');
    }

    public function rejectRequest(Request $request, int $id, int $userId)
    {
        $group = Group::findOrFail($id);
        abort_unless($group->isAdmin($request->user()->id), 403);

        $invitation = GroupInvitation::where('group_id', $id)
            ->where('user_id', $userId)
            ->where('type', GroupInvitation::TYPE_REQUEST)
            ->where('status', 'pending')
            ->firstOrFail();

        $invitation->update(['status' => 'declined']);

        $requester = \App\Models\User::find($userId);
        if ($requester) {
            try {
                \App\Models\Notification::storeForUser(
                    $requester,
                    'group_join_rejected',
                    'Demande refusée',
                    "Votre demande pour rejoindre le groupe « {$group->name} » n'a pas été retenue.",
                    ['url' => route('groups.index')]
                );
            } catch (\Throwable) {}

            try {
                \Illuminate\Support\Facades\Mail::to($requester->email)->send(
                    new \App\Mail\SystemNotificationMail(
                        recipientName: $requester->first_name,
                        title:         'Demande non retenue — ' . $group->name,
                        body:          'Votre demande pour rejoindre le groupe <strong>' . $group->name . '</strong> n\'a pas été retenue pour le moment. Vous pouvez explorer d\'autres groupes et en faire la demande.',
                        actionLabel:   'Explorer les groupes',
                        actionUrl:     route('groups.index'),
                        templateKey:   'group_join_rejected',
                    )
                );
            } catch (\Throwable) {}
        }

        ActivityLogger::log('group.join_rejected', "Demande de {$requester?->first_name} {$requester?->last_name} refusée pour le groupe « {$group->name} »", $request->user()->id, $group, ['rejected_user_id' => $userId]);

        return back()->with('success', 'Demande refusée.');
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
