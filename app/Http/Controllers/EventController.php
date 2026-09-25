<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyUsersNewEventJob;
use App\Services\ActivityLogger;
use App\Services\EventService;
use App\Models\City;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function __construct(private EventService $eventService) {}

    /** Onglets de la maquette : Prochains événements · En présentiel · En distanciel */
    private const MODES = [
        'presentiel' => ['in_person', 'hybrid'],
        'distanciel' => ['virtual', 'hybrid'],
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        $user->loadMissing(['profile', 'city']);

        $userSectorIds = array_unique(array_merge(
            $user->profile?->looking_for      ?? [],
            $user->profile?->services_offered ?? [],
            $user->profile?->sector_ids       ?? [],
        ));

        $attendingIds = $user->events()->pluck('events.id')->toArray();

        // ── Pending invitations ──────────────────────────────────
        $pendingInvitations = EventInvitation::with(['event.sector', 'event.city', 'inviter'])
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereHas('event', fn($q) => $q->where('starts_at', '>=', now()))
            ->latest()
            ->get();

        // Include private events the user is invited to (even if not yet attending)
        $invitedPrivateIds = EventInvitation::where('user_id', $user->id)
            ->where('status', 'pending')
            ->pluck('event_id')
            ->toArray();

        // ── My events (attending, upcoming) ───────────────────────
        $myEventsQuery = Event::with(['sector:id,name', 'city:id,name', 'creator:id,first_name,last_name'])
            ->where(fn($q) => $q->whereIn('id', $attendingIds)
                ->orWhereIn('id', $invitedPrivateIds))
            ->where('starts_at', '>=', now());
        if ($request->filled('search')) {
            $myEventsQuery->where('title', 'like', '%' . $request->search . '%');
        }
        $myEvents = $myEventsQuery->orderBy('starts_at')->get();

        // ── Public upcoming not attending ─────────────────────────
        $publicQuery = Event::with(['sector:id,name', 'city:id,name', 'creator:id,first_name,last_name'])
            ->where('is_public', true)
            ->where('starts_at', '>=', now())
            ->whereNotIn('id', $attendingIds);

        if ($request->filled('search'))   $publicQuery->where('title', 'like', '%' . $request->search . '%');
        if ($request->filled('type'))     $publicQuery->where('type', $request->type);
        if ($request->filled('category')) $publicQuery->where('category', $request->category);
        if ($request->filled('price_filter')) {
            $request->price_filter === 'free'
                ? $publicQuery->where(fn($q) => $q->whereNull('price')->orWhere('price', 0))
                : $publicQuery->where('price', '>', 0);
        }
        if ($request->filled('when')) {
            $now = now();
            match ($request->when) {
                'today'      => $publicQuery->whereDate('starts_at', $now->toDateString()),
                'this_week'  => $publicQuery->whereBetween('starts_at', [$now->startOfDay(), $now->copy()->endOfWeek()]),
                'this_month' => $publicQuery->whereBetween('starts_at', [$now->startOfDay(), $now->copy()->endOfMonth()]),
                default      => null,
            };
        }

        $publicEvents = $publicQuery->orderBy('starts_at')->get();

        // Ville active : session en priorité (sélecteur de région), sinon ville du profil
        $activeCityId = session('selected_city_id', $user->city_id);

        $nearby      = $publicEvents->filter(fn($e) => $activeCityId && $e->city_id === $activeCityId)->values();
        $recommended = $publicEvents->filter(fn($e) => in_array($e->sector_id, $userSectorIds)
            && (!$activeCityId || $e->city_id !== $activeCityId))->values();
        $others      = $publicEvents->filter(fn($e) => !in_array($e->sector_id, $userSectorIds)
            && (!$activeCityId || $e->city_id !== $activeCityId))->values();

        // ── Past events (attending) ───────────────────────────────
        $pastEvents = Event::with(['sector:id,name', 'city:id,name'])
            ->whereIn('id', $attendingIds)
            ->where('starts_at', '<', now())
            ->orderBy('starts_at', 'desc')
            ->limit(6)
            ->get();

        // ── Design lx2 (maquette) : onglet de mode + 3 blocs ─────────────
        $mode      = array_key_exists($request->query('mode'), self::MODES) ? $request->query('mode') : null;
        $byMode    = fn($c) => $mode ? $c->filter(fn($e) => in_array($e->type, self::MODES[$mode]))->values() : $c;
        $invitedIds = $pendingInvitations->pluck('event_id')->all();

        $myEvents    = $byMode($myEvents->reject(fn($e) => in_array($e->id, $invitedIds) && ! in_array($e->id, $attendingIds))->values());
        $suggested   = $byMode($nearby->concat($recommended)->concat($others)->values());
        $pastEvents  = $byMode($pastEvents);
        $pendingInvitations = $mode
            ? $pendingInvitations->filter(fn($i) => in_array($i->event->type, self::MODES[$mode]))->values()
            : $pendingInvitations;

        $this->eventService->attachPreviewAttendees(
            $myEvents->concat($suggested)->concat($pastEvents)->concat($pendingInvitations->pluck('event'))
        );

        return view('events.index', compact(
            'mode', 'pendingInvitations', 'myEvents', 'suggested', 'pastEvents', 'attendingIds'
        ));
    }

    public function show(Request $request, int $id)
    {
        $user  = $request->user();
        $event = Event::with(['sector', 'city', 'creator.profile'])
            ->withCount('attendees')
            ->findOrFail($id);

        $isOrganizer = $event->created_by === $user->id;

        if (! $event->is_public) {
            $isInvited = EventInvitation::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->exists();
            abort_if(! $isOrganizer && ! $isInvited, 403);
        }

        $attendees   = $event->attendees()->with('profile', 'company:id,name')->orderByPivot('role')->get();
        $isAttending = $event->isAttending($user->id);

        $eventConnections = collect();
        $organizerGroups  = collect();
        if ($isOrganizer) {
            $attendeeIds = $attendees->pluck('id')->toArray();
            $eventConnections = User::whereIn('id', $user->connectionIds())
                ->whereNotIn('id', $attendeeIds)
                ->with('profile:id,user_id,job_title')
                ->orderBy('first_name')
                ->get()
                ->map(fn($u) => [
                    'id'        => $u->id,
                    'name'      => $u->first_name . ' ' . $u->last_name,
                    'job_title' => $u->profile?->job_title,
                ]);

            // Only groups with the same name as the event
            $organizerGroups = $user->groups()
                ->wherePivotIn('role', ['owner', 'admin'])
                ->where('name', $event->title)
                ->withCount('members')
                ->get(['groups.id', 'groups.name']);
        }

        // Group with exact same name as event (pre-selection hint for private events)
        $matchingGroup = $organizerGroups->firstWhere('name', $event->title);

        $pendingInvitation = $isAttending ? null : EventInvitation::where('event_id', $event->id)
            ->where('user_id', $user->id)->where('status', 'pending')->first();
        $connectedIds = $user->connectionIds();

        return view('events.show', compact(
            'event', 'attendees', 'isAttending', 'isOrganizer', 'eventConnections', 'organizerGroups', 'matchingGroup',
            'pendingInvitation', 'connectedIds'
        ));
    }

    /** Écran « Créer un événement » (maquette › pageCreateEvent). */
    public function create(Request $request)
    {
        $user = $request->user();

        if (! $user->canFeature('can_create_events')) {
            return back()->with('upgrade_feature', 'can_create_events');
        }

        return view('events.create', [
            'sectors'        => Sector::orderBy('name')->get(['id', 'name']),
            'cities'         => City::active()->orderBy('name')->get(['id', 'name']),
            'categoryLabels' => Event::categoryLabels(),
            'user'           => $user->loadMissing('city'),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // Formulaire lx2 : date et heure saisies séparément ; tarif « Gratuit » → pas de prix
        foreach (['starts_at', 'ends_at'] as $f) {
            if (! $request->filled($f) && $request->filled($f . '_date')) {
                $request->merge([$f => $request->input($f . '_date') . ' ' . ($request->input($f . '_time') ?: '00:00')]);
            }
        }
        if ($request->input('pricing') === 'free') {
            $request->merge(['price' => null]);
        }

        if (! $user->canFeature('can_create_events')) {
            return back()->with('upgrade_feature', 'can_create_events');
        }

        $validated = $request->validate([
            'title'         => ['required', 'string', 'max:150'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'type'          => ['required', 'in:virtual,in_person,hybrid'],
            'category'      => ['nullable', 'in:' . implode(',', array_keys(Event::categoryLabels()))],
            'location'      => ['nullable', 'string', 'max:255'],
            'meeting_link'  => ['nullable', 'url', 'max:500'],
            'starts_at'     => ['required', 'date', 'after:now'],
            'ends_at'       => ['nullable', 'date', 'after:starts_at'],
            'sector_id'     => ['nullable', 'integer', 'exists:sectors,id'],
            'city_id'       => ['nullable', 'integer', 'exists:cities,id'],
            'cover_color'   => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'cover_image'   => ['nullable', 'image', 'max:2048'],
            'price'         => ['nullable', 'numeric', 'min:0'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'is_private'    => ['nullable', 'boolean'],
        ]);

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('events/covers', 'public');
        }

        $isPrivate = (bool) ($validated['is_private'] ?? false);

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
                        return back()->withErrors(['city_id' => 'En tant que Consul, vous ne pouvez créer des événements que dans votre région.'])->withInput();
                    }
                }
            }
        }

        $cityId = $user->isConsul() ? $user->city_id : ($validated['city_id'] ?? $user->city_id);

        $event = Event::create([
            'title'           => $validated['title'],
            'description'     => $validated['description'] ?? null,
            'type'            => $validated['type'],
            'category'        => $validated['category'] ?? null,
            'location'        => $validated['location'] ?? null,
            'meeting_link'    => $validated['meeting_link'] ?? null,
            'starts_at'       => $validated['starts_at'],
            'ends_at'         => $validated['ends_at'] ?? null,
            'sector_id'       => $validated['sector_id'] ?? null,
            'city_id'         => $cityId,
            'region_id'       => $user->region_id ?? $cityId,
            'cover_color'     => $validated['cover_color'] ?? '#1E8F88',
            'cover_image'     => $coverImagePath,
            'price'           => $validated['price'] ?? null,
            'max_attendees'   => $validated['max_attendees'] ?? null,
            'created_by'      => $user->id,
            'is_public'       => ! $isPrivate,
            'scope'           => $isPrivate ? 'private' : 'regional',
            'attendees_count' => 1,
        ]);

        $event->attendees()->attach($user->id, ['role' => 'organizer']);

        // Auto-invite members of groups the organizer owns/admins with the same name
        if ($isPrivate) {
            $matchingGroups = $user->groups()
                ->wherePivotIn('role', ['owner', 'admin'])
                ->where('name', $event->title)
                ->get();

            foreach ($matchingGroups as $group) {
                $members = $group->members()
                    ->whereNot('users.id', $user->id)
                    ->get(['users.id', 'users.first_name', 'users.last_name']);

                foreach ($members as $member) {
                    EventInvitation::updateOrCreate(
                        ['event_id' => $event->id, 'user_id' => $member->id],
                        ['invited_by' => $user->id, 'status' => 'pending']
                    );
                    try {
                        \App\Models\Notification::storeForUser(
                            $member,
                            'event_invitation',
                            'Invitation à un événement privé',
                            "{$user->first_name} {$user->last_name} vous invite à l'événement privé « {$event->title} ».",
                            ['url' => route('events.show', $event->id), 'event_id' => $event->id]
                        );
                    } catch (\Throwable) {}
                }
            }
        } else {
            NotifyUsersNewEventJob::dispatch($event);
        }

        ActivityLogger::log('event.created', "Événement « {$event->title} » créé" . ($isPrivate ? ' (privé)' : ''), $user->id, $event);

        return redirect()->route('events.show', $event->id)
            ->with('success', 'Événement "' . $event->title . '" créé avec succès !');
    }

    public function join(Request $request, int $id)
    {
        if (! $request->user()->canFeature('can_participate_events')) {
            return back()->with('upgrade_feature', 'can_participate_events');
        }

        $event = Event::findOrFail($id);
        $user  = $request->user();

        if ($event->isAttending($user->id)) {
            return back()->with('info', 'Vous êtes déjà inscrit à cet événement.');
        }

        if ($event->max_attendees !== null && $event->attendees_count >= $event->max_attendees) {
            return back()->with('error', 'Cet événement est complet.');
        }

        $event->attendees()->attach($user->id, ['role' => 'attendee']);
        $event->increment('attendees_count');

        EventInvitation::where('event_id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->update(['status' => 'accepted']);

        return back()->with('success', 'Vous êtes inscrit à « ' . $event->title . ' ».');
    }

    public function leave(Request $request, int $id)
    {
        $event = Event::findOrFail($id);
        $user  = $request->user();

        if ($event->created_by === $user->id) {
            return back()->with('error', 'L\'organisateur ne peut pas quitter son événement.');
        }

        if ($event->isAttending($user->id)) {
            $event->attendees()->detach($user->id);
            $event->decrement('attendees_count');
        }

        return back()->with('success', 'Votre inscription a été annulée.');
    }

    public function destroy(Request $request, int $id)
    {
        $event = Event::findOrFail($id);
        abort_if($event->created_by !== $request->user()->id, 403);

        if ($event->cover_image) {
            Storage::disk('public')->delete($event->cover_image);
        }

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'L\'événement « ' . $event->title . ' » a été supprimé.');
    }

    public function removeAttendee(Request $request, int $id, int $userId)
    {
        $event = Event::findOrFail($id);
        abort_if($event->created_by !== $request->user()->id, 403);
        abort_if($userId === $request->user()->id, 422);

        if ($event->attendees()->where('user_id', $userId)->exists()) {
            $event->attendees()->detach($userId);
            $event->decrement('attendees_count');
        }

        return back()->with('success', 'Participant retiré.');
    }

    // ── Invitations ──────────────────────────────────────────────

    public function inviteGroup(Request $request, int $id)
    {
        $event = Event::findOrFail($id);
        $user  = $request->user();

        abort_if($event->created_by !== $user->id, 403);

        $request->validate(['group_id' => ['required', 'integer', 'exists:groups,id']]);
        $group = \App\Models\Group::findOrFail($request->group_id);

        abort_unless($group->isAdmin($user->id), 403);

        $attendingIds = $event->attendees()->pluck('users.id')->toArray();
        $members = $group->members()
            ->whereNotIn('users.id', $attendingIds)
            ->whereNotIn('users.id', [$user->id])
            ->get(['users.id', 'users.first_name', 'users.last_name', 'users.email']);

        $sent = 0;
        foreach ($members as $member) {
            EventInvitation::updateOrCreate(
                ['event_id' => $id, 'user_id' => $member->id],
                ['invited_by' => $user->id, 'status' => 'pending']
            );

            try {
                \App\Models\Notification::storeForUser(
                    $member,
                    'event_invitation',
                    'Invitation à un événement',
                    "{$user->first_name} {$user->last_name} vous invite à l'événement « {$event->title} ».",
                    ['url' => route('events.show', $id), 'event_id' => $id]
                );
            } catch (\Throwable) {}

            $sent++;
        }

        ActivityLogger::log('event.group_invite', "Groupe « {$group->name} » invité à l'événement « {$event->title} » ({$sent} membres)", $user->id, $event);

        return back()->with('success', "{$sent} membre(s) du groupe « {$group->name} » invité(s).");
    }

    public function invite(Request $request, int $id)
    {
        $event = Event::findOrFail($id);
        $user  = $request->user();

        abort_if($event->created_by !== $user->id, 403);

        $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);
        $targetId = (int) $request->user_id;

        if ($event->isAttending($targetId)) {
            return back()->with('info', 'Ce membre participe déjà à l\'événement.');
        }

        EventInvitation::updateOrCreate(
            ['event_id' => $id, 'user_id' => $targetId],
            ['invited_by' => $user->id, 'status' => 'pending']
        );

        return back()->with('success', 'Invitation envoyée.');
    }

    public function acceptInvitation(Request $request, int $invId)
    {
        if (! $request->user()->canFeature('can_participate_events')) {
            return back()->with('upgrade_feature', 'can_participate_events');
        }

        $invitation = EventInvitation::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->findOrFail($invId);

        $event = $invitation->event;

        if (!$event->isAttending($invitation->user_id)) {
            if ($event->max_attendees !== null && $event->attendees_count >= $event->max_attendees) {
                $invitation->update(['status' => 'declined']);
                return back()->with('error', 'Cet événement est complet.');
            }
            $event->attendees()->attach($invitation->user_id, ['role' => 'attendee']);
            $event->increment('attendees_count');
        }

        $invitation->update(['status' => 'accepted']);

        return redirect()->route('events.show', $event->id)
            ->with('success', 'Vous êtes inscrit à « ' . $event->title . ' ».');
    }

    public function declineInvitation(Request $request, int $invId)
    {
        $invitation = EventInvitation::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->findOrFail($invId);

        $invitation->update(['status' => 'declined']);

        return back()->with('success', 'Invitation déclinée.');
    }
}
