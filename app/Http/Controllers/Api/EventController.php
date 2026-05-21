<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing('profile');

        $attendingIds = $user->events()->pluck('events.id')->toArray();

        $userSectorIds = array_unique(array_merge(
            $user->profile?->looking_for      ?? [],
            $user->profile?->services_offered ?? [],
            $user->profile?->sector_ids       ?? [],
        ));

        // ── Pending invitations ──────────────────────────────────
        $invitations = EventInvitation::with(['event.sector', 'event.city', 'event.creator', 'inviter'])
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

        // ── My events (attending, upcoming) ───────────────────────
        $myEvents = Event::with(['sector:id,name', 'creator:id,first_name,last_name', 'city:id,name'])
            ->whereIn('id', $attendingIds)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->get();

        // ── Public upcoming not attending ─────────────────────────
        $publicQuery = Event::with(['sector:id,name', 'creator:id,first_name,last_name', 'city:id,name'])
            ->where('is_public', true)
            ->where('starts_at', '>=', now())
            ->whereNotIn('id', $attendingIds);

        if ($request->filled('search'))      $publicQuery->where('title', 'like', '%' . $request->search . '%');
        if ($request->filled('type'))        $publicQuery->where('type', $request->type);
        if ($request->filled('category'))    $publicQuery->where('category', $request->category);
        if ($request->filled('sector_id'))   $publicQuery->where('sector_id', $request->sector_id);
        if ($request->filled('city_id'))     $publicQuery->where('city_id', $request->city_id);
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

        $nearby      = $publicEvents->filter(fn($e) => $user->city_id && $e->city_id === $user->city_id)->values();
        $recommended = $publicEvents->filter(fn($e) => in_array($e->sector_id, $userSectorIds)
            && (!$user->city_id || $e->city_id !== $user->city_id))->values();
        $others      = $publicEvents->filter(fn($e) => !in_array($e->sector_id, $userSectorIds)
            && (!$user->city_id || $e->city_id !== $user->city_id))->values();

        // ── Past events (all public, paginated) ───────────────────
        $pastPage    = max(1, (int) $request->input('past_page', 1));
        $pastPerPage = 10;
        $pastEvents  = Event::with(['sector:id,name', 'creator:id,first_name,last_name', 'city:id,name'])
            ->where('is_public', true)
            ->where('starts_at', '<', now())
            ->orderBy('starts_at', 'desc')
            ->paginate($pastPerPage, ['*'], 'past_page', $pastPage);

        return response()->json([
            'data' => [
                'invitations' => $invitations,
                'my_events'   => $myEvents->map(fn($e) => $this->formatEvent($e, $attendingIds, $user->city_id))->values(),
                'nearby'      => $nearby->map(fn($e) => $this->formatEvent($e, $attendingIds, $user->city_id))->values(),
                'recommended' => $recommended->map(fn($e) => $this->formatEvent($e, $attendingIds, $user->city_id))->values(),
                'others'      => $others->map(fn($e) => $this->formatEvent($e, $attendingIds, $user->city_id))->values(),
                'past'        => $pastEvents->getCollection()->map(fn($e) => $this->formatEvent($e, $attendingIds, $user->city_id))->values(),
            ],
            'meta' => [
                'total_public'       => $publicEvents->count(),
                'invitations'        => $invitations->count(),
                'past_current_page'  => $pastEvents->currentPage(),
                'past_last_page'     => $pastEvents->lastPage(),
                'past_has_more'      => $pastEvents->hasMorePages(),
            ],
        ]);
    }

    public function invitations(Request $request): JsonResponse
    {
        $user         = $request->user();
        $attendingIds = $user->events()->pluck('events.id')->toArray();

        $invitations = EventInvitation::with(['event.sector', 'event.city', 'event.creator', 'inviter'])
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

        return response()->json(['message' => 'Invitation sent.']);
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
            'data'    => $this->formatEvent($event->load(['sector', 'city', 'creator']), $attendingIds, $request->user()->city_id),
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

        $events = Event::with(['sector:id,name', 'creator:id,first_name,last_name', 'city:id,name'])
            ->whereHas('attendees', fn($q) => $q->where('user_id', $user->id))
            ->orderBy('starts_at')
            ->paginate(20);

        $mapped = $events->getCollection()->map(fn($e) => $this->formatEvent($e, $attendingIds, $user->city_id));
        $events->setCollection($mapped);

        return response()->json(['data' => $events]);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $event = Event::with(['sector:id,name', 'creator:id,first_name,last_name', 'city:id,name'])
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
            'category'      => ['nullable', 'in:' . implode(',', array_keys(Event::$categoryLabels))],
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

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('events/covers', 'public');
        }

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
            'city_id'         => $validated['city_id'] ?? $user->city_id,
            'cover_color'     => $validated['cover_color'] ?? '#1E8F88',
            'cover_image'     => $coverImagePath,
            'price'           => $validated['price'] ?? null,
            'max_attendees'   => $validated['max_attendees'] ?? null,
            'created_by'      => $user->id,
            'is_public'       => true,
            'attendees_count' => 1,
        ]);

        $event->attendees()->attach($user->id, ['role' => 'organizer']);

        return response()->json([
            'message' => 'Event created successfully.',
            'data'    => $this->formatEvent($event->load(['sector', 'creator', 'city']), [$event->id], $user->city_id),
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
                'id'   => $event->creator->id,
                'name' => $event->creator->first_name . ' ' . $event->creator->last_name,
            ] : null,
            'created_at' => $event->created_at,
        ];
    }
}
