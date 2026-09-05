<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyUsersNewEventJob;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\Group;
use App\Models\Notification;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
        ]);

        $user = $request->user();
        $user->loadMissing('profile');
        $activeCityId = $request->filled('city_id') ? (int) $request->city_id : $user->city_id;

        $attendingIds = $user->events()->pluck('events.id')->toArray();

        $userSectorIds = array_unique(array_merge(
            $user->profile?->looking_for      ?? [],
            $user->profile?->services_offered ?? [],
            $user->profile?->sector_ids       ?? [],
        ));

        // ── Pending invitations ──────────────────────────────────
        $invitations = EventInvitation::with(['event.sector', 'event.city', 'event.creator.profile', 'inviter'])
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereHas('event', fn($q) => $q->where('starts_at', '>=', now()))
            ->latest()
            ->get()
            ->map(fn($inv) => [
                'id'      => $inv->id,
                'status'  => $inv->status,
                'inviter' => $inv->inviter ? [
                    'id'   => $inv->inviter->id,
                    'name' => $inv->inviter->first_name . ' ' . $inv->inviter->last_name,
                ] : null,
                'event' => $this->formatEvent($inv->event, $attendingIds, $user->city_id),
            ]);

        $page    = max(1, (int) ($request->page ?? 1));
        $perPage = min(50, max(1, (int) ($request->per_page ?? 10)));

        $applyFilters = function ($query) use ($request) {
            if ($request->filled('search'))      $query->where('title', 'like', '%' . $request->search . '%');
            if ($request->filled('type'))        $query->where('type', $request->type);
            if ($request->filled('category'))    $query->where('category', $request->category);
            if ($request->filled('sector_id'))   $query->where('sector_id', $request->sector_id);
            if ($request->filled('city_id'))     $query->where('city_id', $request->city_id);
            if ($request->filled('price_filter')) {
                $request->price_filter === 'free'
                    ? $query->where(fn($q) => $q->whereNull('price')->orWhere('price', 0))
                    : $query->where('price', '>', 0);
            }
            if ($request->filled('when')) {
                $now = now();
                match ($request->when) {
                    'today'      => $query->whereDate('starts_at', $now->toDateString()),
                    'this_week'  => $query->whereBetween('starts_at', [$now->copy()->startOfDay(), $now->copy()->endOfWeek()]),
                    'this_month' => $query->whereBetween('starts_at', [$now->copy()->startOfDay(), $now->copy()->endOfMonth()]),
                    default      => null,
                };
            }
        };

        // ── My events (created by me, paginated) ───────────────────
        $myEventsQuery = Event::with(['sector:id,name', 'creator:id,first_name,last_name', 'creator.profile:user_id,avatar', 'city:id,name'])
            ->where('created_by', $user->id)
            ->where('starts_at', '>=', now());
        if ($activeCityId !== null) $myEventsQuery->where('city_id', $activeCityId);
        $applyFilters($myEventsQuery);
        $myEventsPaginator = $myEventsQuery->orderBy('starts_at')->paginate($perPage, ['*'], 'page', $page);

        // ── Events I participate in (not created by me, paginated) ─
        $participatingQuery = Event::with(['sector:id,name', 'creator:id,first_name,last_name', 'creator.profile:user_id,avatar', 'city:id,name'])
            ->whereIn('id', $attendingIds)
            ->where('created_by', '!=', $user->id)
            ->where('starts_at', '>=', now());
        if ($activeCityId !== null) $participatingQuery->where('city_id', $activeCityId);
        $applyFilters($participatingQuery);
        $participatingPaginator = $participatingQuery->orderBy('starts_at')->paginate($perPage, ['*'], 'page', $page);

        // ── Public upcoming not already mine/participating ─────────
        $publicQuery = Event::with(['sector:id,name', 'creator:id,first_name,last_name', 'creator.profile:user_id,avatar', 'city:id,name'])
            ->where('is_public', true)
            ->where('starts_at', '>=', now())
            ->where('created_by', '!=', $user->id)
            ->whereNotIn('id', $attendingIds);
        if ($activeCityId !== null) {
            $publicQuery->where('city_id', $activeCityId);
        }
        $applyFilters($publicQuery);

        $publicEvents = $publicQuery->orderBy('starts_at')->get();

        $recommendedCollection = $publicEvents->filter(fn($e) =>
            ($activeCityId && $e->city_id === $activeCityId) || in_array($e->sector_id, $userSectorIds)
        )->values();
        $allCollection = $publicEvents->filter(fn($e) => !$recommendedCollection->contains('id', $e->id))->values();

        $recommendedPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $recommendedCollection->forPage($page, $perPage)->values(),
            $recommendedCollection->count(),
            $perPage,
            $page
        );
        $allPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $allCollection->forPage($page, $perPage)->values(),
            $allCollection->count(),
            $perPage,
            $page
        );


        $lastPage = max(
            $myEventsPaginator->lastPage(),
            $participatingPaginator->lastPage(),
            $recommendedPaginator->lastPage(),
            $allPaginator->lastPage(),
        );

        return response()->json([
            'data' => [
                'invitations' => $invitations,
                'my_events'   => $myEventsPaginator->getCollection()->map(fn($e) => $this->formatEvent($e, $attendingIds, $activeCityId))->values(),
                'participating' => $participatingPaginator->getCollection()->map(fn($e) => $this->formatEvent($e, $attendingIds, $activeCityId))->values(),
                'recommended' => $recommendedPaginator->getCollection()->map(fn($e) => $this->formatEvent($e, $attendingIds, $activeCityId))->values(),
                'all'         => $allPaginator->getCollection()->map(fn($e) => $this->formatEvent($e, $attendingIds, $activeCityId))->values(),
                // Legacy keys kept during mobile transition.
                'nearby'      => $recommendedPaginator->getCollection()->filter(fn($e) => $activeCityId && $e->city_id === $activeCityId)->map(fn($e) => $this->formatEvent($e, $attendingIds, $activeCityId))->values(),
                'others'      => $allPaginator->getCollection()->map(fn($e) => $this->formatEvent($e, $attendingIds, $activeCityId))->values(),
                'past'        => [],
            ],
            'meta' => [
                'total_public'       => $publicEvents->count(),
                'invitations'        => $invitations->count(),
                'current_page'       => $page,
                'last_page'          => $lastPage,
                'per_page'           => $perPage,
                'has_more'           => $page < $lastPage,
                'past_current_page'  => 1,
                'past_last_page'     => 1,
                'past_has_more'      => false,
            ],
        ]);
    }

    public function invitations(Request $request): JsonResponse
    {
        $user         = $request->user();
        $attendingIds = $user->events()->pluck('events.id')->toArray();

        $invitations = EventInvitation::with(['event.sector', 'event.city', 'event.creator.profile', 'inviter'])
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereHas('event', fn($q) => $q->where('starts_at', '>=', now()))
            ->latest()
            ->get()
            ->map(fn($inv) => [
                'id'      => $inv->id,
                'status'  => $inv->status,
                'inviter' => $inv->inviter ? [
                    'id'   => $inv->inviter->id,
                    'name' => $inv->inviter->first_name . ' ' . $inv->inviter->last_name,
                ] : null,
                'event' => $this->formatEvent($inv->event, $attendingIds, $user->city_id),
            ]);

        return response()->json(['data' => $invitations]);
    }

    public function invite(int $id, Request $request): JsonResponse
    {
        $event = Event::findOrFail($id);
        $user  = $request->user();

        if ($event->created_by !== $user->id) {
            return response()->json(['message' => 'Only the organizer can send invitations.'], 403);
        }

        $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);
        $targetId = (int) $request->user_id;

        if ($event->isAttending($targetId)) {
            return response()->json(['message' => 'User is already attending this event.'], 422);
        }

        EventInvitation::updateOrCreate(
            ['event_id' => $id, 'user_id' => $targetId],
            ['invited_by' => $user->id, 'status' => 'pending']
        );

        $invitee = User::find($targetId);
        if ($invitee) {
            app(FirebaseService::class)->sendEventInviteNotification($invitee, $event, $user);
        }

        return response()->json(['message' => 'Invitation sent.']);
    }

    public function inviteBulk(int $id, Request $request): JsonResponse
    {
        $event = Event::findOrFail($id);
        $user  = $request->user();

        if ($event->created_by !== $user->id) {
            return response()->json(['message' => 'Only the organizer can send invitations.'], 403);
        }

        $validated = $request->validate([
            'user_ids'   => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $targetIds = collect($validated['user_ids'])
            ->map(fn($targetId) => (int) $targetId)
            ->unique()
            ->values();

        $attendingIds = $event->attendees()
            ->whereIn('users.id', $targetIds)
            ->pluck('users.id')
            ->all();

        $invitableIds = $targetIds
            ->reject(fn($targetId) => in_array($targetId, $attendingIds, true))
            ->values();

        $created = 0;
        $updated = 0;

        $firebase = app(FirebaseService::class);

        foreach ($invitableIds as $targetId) {
            $invitation = EventInvitation::updateOrCreate(
                ['event_id' => $id, 'user_id' => $targetId],
                ['invited_by' => $user->id, 'status' => 'pending']
            );

            $invitation->wasRecentlyCreated ? $created++ : $updated++;

            $invitee = User::find($targetId);
            if ($invitee) {
                $firebase->sendEventInviteNotification($invitee, $event, $user);
            }
        }

        return response()->json([
            'message' => 'Invitations sent.',
            'data' => [
                'requested_count' => $targetIds->count(),
                'sent_count'      => $invitableIds->count(),
                'created_count'   => $created,
                'updated_count'   => $updated,
                'skipped_count'   => count($attendingIds),
                'skipped_user_ids' => array_values($attendingIds),
            ],
        ]);
    }

    public function inviteGroup(int $id, Request $request): JsonResponse
    {
        $event = Event::findOrFail($id);
        $user  = $request->user();

        if ($event->created_by !== $user->id) {
            return response()->json(['message' => 'Only the organizer can invite groups.'], 403);
        }

        $request->validate(['group_id' => ['required', 'integer', 'exists:groups,id']]);
        $group = Group::findOrFail($request->group_id);

        if (! $group->isAdmin($user->id)) {
            return response()->json(['message' => 'You must be an owner or admin of this group to invite it.'], 403);
        }

        $attendingIds = $event->attendees()->pluck('users.id')->toArray();
        $members = $group->members()
            ->whereNotIn('users.id', $attendingIds)
            ->whereNotIn('users.id', [$user->id])
            ->get(['users.id']);

        $firebase = app(FirebaseService::class);
        $sent = 0;

        foreach ($members as $member) {
            EventInvitation::updateOrCreate(
                ['event_id' => $id, 'user_id' => $member->id],
                ['invited_by' => $user->id, 'status' => 'pending']
            );

            $invitee = User::find($member->id);
            if ($invitee) {
                $firebase->sendEventInviteNotification($invitee, $event, $user);
                try {
                    Notification::storeForUser(
                        $invitee,
                        'event_invitation',
                        'Invitation à un événement',
                        "{$user->first_name} {$user->last_name} vous invite à l'événement « {$event->title} ».",
                        ['event_id' => $id]
                    );
                } catch (\Throwable) {}
            }

            $sent++;
        }

        ActivityLogger::log(
            'event.group_invite',
            "Groupe « {$group->name} » invité à l'événement « {$event->title} » ({$sent} membres)",
            $user->id,
            $event
        );

        return response()->json([
            'message' => "{$sent} membre(s) du groupe « {$group->name} » invité(s).",
            'data'    => [
                'sent_count'    => $sent,
                'skipped_count' => count($attendingIds),
            ],
        ]);
    }

    public function acceptInvitation(int $invId, Request $request): JsonResponse
    {
        $invitation = EventInvitation::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->findOrFail($invId);

        $event = $invitation->event;

        if (!$event->isAttending($invitation->user_id)) {
            if ($event->max_attendees !== null && $event->attendees_count >= $event->max_attendees) {
                $invitation->update(['status' => 'declined']);
                return response()->json(['message' => 'Event is at full capacity.'], 422);
            }
            $event->attendees()->attach($invitation->user_id, ['role' => 'attendee']);
            $event->increment('attendees_count');
        }

        $invitation->update(['status' => 'accepted']);
        $event->refresh();

        $attendingIds = $request->user()->events()->pluck('events.id')->toArray();

        return response()->json([
            'message' => 'Invitation accepted.',
            'data'    => $this->formatEvent($event->load(['sector', 'city', 'creator', 'creator.profile']), $attendingIds, $request->user()->city_id),
        ]);
    }

    public function declineInvitation(int $invId, Request $request): JsonResponse
    {
        $invitation = EventInvitation::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->findOrFail($invId);

        $invitation->update(['status' => 'declined']);

        return response()->json(['message' => 'Invitation declined.']);
    }

    public function mine(Request $request): JsonResponse
    {
        $user         = $request->user();
        $attendingIds = $user->events()->pluck('events.id')->toArray();

        $events = Event::with(['sector:id,name', 'creator:id,first_name,last_name', 'creator.profile:user_id,avatar', 'city:id,name'])
            ->whereHas('attendees', fn($q) => $q->where('user_id', $user->id))
            ->orderBy('starts_at')
            ->paginate(20);

        $mapped = $events->getCollection()->map(fn($e) => $this->formatEvent($e, $attendingIds, $user->city_id));
        $events->setCollection($mapped);

        return response()->json(['data' => $events]);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $event = Event::with(['sector:id,name', 'creator:id,first_name,last_name', 'creator.profile:user_id,avatar', 'city:id,name'])
            ->findOrFail($id);

        $user         = $request->user();
        $attendingIds = $user->events()->pluck('events.id')->toArray();

        return response()->json(['data' => $this->formatEvent($event, $attendingIds, $user->city_id)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'         => ['required', 'string', 'max:150'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'type'          => ['required', 'in:virtual,in_person,hybrid'],
            'scope'         => ['nullable', 'in:regional,private'],
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
        ]);

        $user = $request->user();
        $user->loadMissing('subscription.plan');

        if (!$user->isAmbassador() && !$user->isConsul() && !$user->canFeature('can_create_events')) {
            return response()->json([
                'message' => 'Only approved ambassadors, consuls or eligible plans can create events.',
            ], 403);
        }

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('events/covers', 'public');
        }

        $scope    = $validated['scope'] ?? 'regional';
        $isPublic = $scope !== 'private';

        $event = Event::create([
            'title'           => $validated['title'],
            'description'     => $validated['description'] ?? null,
            'type'            => $validated['type'],
            'scope'           => $scope,
            'category'        => $validated['category'] ?? null,
            'location'        => $validated['location'] ?? null,
            'meeting_link'    => $validated['meeting_link'] ?? null,
            'starts_at'       => $validated['starts_at'],
            'ends_at'         => $validated['ends_at'] ?? null,
            'sector_id'       => $validated['sector_id'] ?? null,
            'city_id'         => $validated['city_id'] ?? $user->city_id,
            'cover_color'     => $validated['cover_color'] ?? '#1E8F88',
            'cover_image'     => $coverImagePath,
            'price'           => $validated['price'] ?? null,
            'max_attendees'   => $validated['max_attendees'] ?? null,
            'created_by'      => $user->id,
            'is_public'       => $isPublic,
            'attendees_count' => 1,
        ]);

        $event->attendees()->attach($user->id, ['role' => 'organizer']);

        // Auto-invite members of groups owned/admined by organizer whose name matches the event title
        if ($scope === 'private') {
            $firebase = app(FirebaseService::class);
            $matchingGroups = $user->groups()
                ->wherePivotIn('role', ['owner', 'admin'])
                ->where('name', $event->title)
                ->get();

            foreach ($matchingGroups as $group) {
                $members = $group->members()
                    ->whereNot('users.id', $user->id)
                    ->get(['users.id']);

                foreach ($members as $member) {
                    EventInvitation::updateOrCreate(
                        ['event_id' => $event->id, 'user_id' => $member->id],
                        ['invited_by' => $user->id, 'status' => 'pending']
                    );
                    $invitee = User::find($member->id);
                    if ($invitee) {
                        $firebase->sendEventInviteNotification($invitee, $event, $user);
                    }
                }
            }
        }

        if ($scope === 'regional') {
            NotifyUsersNewEventJob::dispatch($event);
        }

        return response()->json([
            'message' => 'Event created successfully.',
            'data'    => $this->formatEvent($event->load(['sector', 'creator', 'creator.profile', 'city']), [$event->id], $user->city_id),
        ], 201);
    }

    public function join(int $id, Request $request): JsonResponse
    {
        $event = Event::findOrFail($id);
        $user  = $request->user();

        if ($event->isAttending($user->id)) {
            return response()->json(['message' => 'Already registered for this event.'], 422);
        }

        if ($event->max_attendees !== null && $event->attendees_count >= $event->max_attendees) {
            return response()->json(['message' => 'Event is at full capacity.'], 422);
        }

        if ($event->price > 0) {
            $paid = \App\Models\EventPayment::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->where('status', 'succeeded')
                ->exists();

            if (!$paid) {
                return response()->json(['message' => 'Payment required to join this event.'], 402);
            }
        }

        $event->attendees()->attach($user->id, ['role' => 'attendee']);
        $event->increment('attendees_count');

        EventInvitation::where('event_id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->update(['status' => 'accepted']);

        $event->refresh();

        return response()->json([
            'message'         => 'Successfully registered.',
            'attendees_count' => $event->attendees_count,
        ]);
    }

    public function leave(int $id, Request $request): JsonResponse
    {
        $event = Event::findOrFail($id);
        $user  = $request->user();

        if ($event->created_by === $user->id) {
            return response()->json(['message' => 'The organizer cannot leave the event.'], 422);
        }

        if ($event->isAttending($user->id)) {
            $event->attendees()->detach($user->id);
            $event->decrement('attendees_count');
            $event->refresh();
        }

        return response()->json([
            'message'         => 'Registration cancelled.',
            'attendees_count' => $event->attendees_count,
        ]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $event = Event::findOrFail($id);
        $user  = $request->user();

        if ($event->created_by !== $user->id) {
            return response()->json(['message' => 'Only the organizer can delete this event.'], 403);
        }

        if ($event->cover_image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($event->cover_image);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully.']);
    }

    public function attendees(int $id, Request $request): JsonResponse
    {
        $event = Event::findOrFail($id);

        $attendees = $event->attendees()
            ->with(['company:id,name', 'profile:user_id,avatar,job_title'])
            ->withPivot('role', 'registered_at')
            ->orderByPivot('registered_at')
            ->paginate(20);

        $attendees->getCollection()->transform(fn($user) => [
            'id'         => $user->id,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'job_title'  => $user->profile?->job_title,
            'avatar'     => $user->profile?->avatar_url,
            'company'    => $user->company?->name,
            'role'       => $user->pivot->role,
        ]);

        return response()->json(['data' => $attendees]);
    }

    public function removeAttendee(int $id, int $userId, Request $request): JsonResponse
    {
        $event = Event::findOrFail($id);
        $user  = $request->user();

        if ($event->created_by !== $user->id) {
            return response()->json(['message' => 'Only the organizer can remove attendees.'], 403);
        }

        if ($userId === $user->id) {
            return response()->json(['message' => 'You cannot remove yourself as organizer.'], 422);
        }

        if ($event->attendees()->where('user_id', $userId)->exists()) {
            $event->attendees()->detach($userId);
            $event->decrement('attendees_count');
            $event->refresh();
        }

        return response()->json([
            'message'         => 'Attendee removed.',
            'attendees_count' => $event->attendees_count,
        ]);
    }

    private function formatEvent(Event $event, array $attendingIds, ?int $userCityId = null): array
    {
        return [
            'id'              => $event->id,
            'title'           => $event->title,
            'description'     => $event->description,
            'type'            => $event->type,
            'location'        => $event->location,
            'meeting_link'    => $event->meeting_link,
            'starts_at'       => $event->starts_at->toIso8601String(),
            'ends_at'         => $event->ends_at?->toIso8601String(),
            'category'        => $event->category,
            'cover_color'     => $event->cover_color,
            'cover_image'     => $event->cover_url,
            'price'           => $event->price,
            'is_free'         => $event->is_free,
            'is_public'       => $event->is_public,
            'max_attendees'   => $event->max_attendees,
            'attendees_count' => $event->attendees_count,
            'is_attending'    => in_array($event->id, $attendingIds),
            'is_upcoming'     => $event->starts_at->isFuture(),
            'is_nearby'       => $userCityId !== null && $event->city_id === $userCityId,
            'sector'          => $event->sector ? ['id' => $event->sector->id, 'name' => $event->sector->name] : null,
            'city'            => $event->city   ? ['id' => $event->city->id,   'name' => $event->city->name]   : null,
            'creator'         => $event->creator ? [
                'id'     => $event->creator->id,
                'name'   => $event->creator->first_name . ' ' . $event->creator->last_name,
                'avatar' => $event->creator->profile?->avatar_url,
            ] : null,
            'created_at' => $event->created_at,
        ];
    }
}
