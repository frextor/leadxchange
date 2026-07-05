<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyUsersNewEventJob;
use App\Services\ActivityLogger;
use App\Models\City;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->loadMissing(['profile', 'city']);

        $userSectorIds = array_unique(array_merge(
            $user->profile?->looking_for      ?? [],
            $user->profile?->services_offered ?? [],
            $user->profile?->sector_ids       ?? [],
        ));

        $sectors      = Sector::orderBy('name')->get();
        $cities       = City::active()->orderBy('name')->get();
        $attendingIds = $user->events()->pluck('events.id')->toArray();

        // ── Pending invitations ──────────────────────────────────
        $pendingInvitations = EventInvitation::with(['event.sector', 'event.city', 'inviter'])
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereHas('event', fn($q) => $q->where('starts_at', '>=', now()))
            ->latest()
            ->get();

        // ── My events (attending, upcoming) ───────────────────────
        $myEventsQuery = Event::with(['sector:id,name', 'city:id,name', 'creator:id,first_name,last_name'])
            ->whereIn('id', $attendingIds)
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

        $nearby      = $publicEvents->filter(fn($e) => $user->city_id && $e->city_id === $user->city_id)->values();
        $recommended = $publicEvents->filter(fn($e) => in_array($e->sector_id, $userSectorIds)
            && (!$user->city_id || $e->city_id !== $user->city_id))->values();
        $others      = $publicEvents->filter(fn($e) => !in_array($e->sector_id, $userSectorIds)
            && (!$user->city_id || $e->city_id !== $user->city_id))->values();

        // ── Past events (attending) ───────────────────────────────
        $pastEvents = Event::with(['sector:id,name', 'city:id,name'])
            ->whereIn('id', $attendingIds)
            ->where('starts_at', '<', now())
            ->orderBy('starts_at', 'desc')
            ->limit(6)
            ->get();

        // Connections for invite modal (organizer only needs it)
        $eventConnections = User::whereIn('id', $user->connectionIds())
            ->with('profile:id,user_id,job_title')
            ->orderBy('first_name')
            ->get()
            ->map(fn($u) => [
                'id'        => $u->id,
                'name'      => $u->first_name . ' ' . $u->last_name,
                'job_title' => $u->profile?->job_title,
            ]);

        return view('events.index', compact(
            'sectors', 'cities',
            'pendingInvitations', 'myEvents', 'nearby', 'recommended', 'others', 'pastEvents',
            'attendingIds', 'userSectorIds', 'eventConnections'
        ));
    }

    public function show(Request $request, int $id)
    {
        $user  = $request->user();
        $event = Event::with(['sector', 'city', 'creator.profile'])
            ->withCount('attendees')
            ->findOrFail($id);

        abort_if(!$event->is_public, 403);

        $attendees   = $event->attendees()->with('profile', 'company:id,name')->orderByPivot('role')->get();
        $isAttending = $event->isAttending($user->id);
        $isOrganizer = $event->created_by === $user->id;

        $eventConnections = collect();
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
        }

        return view('events.show', compact('event', 'attendees', 'isAttending', 'isOrganizer', 'eventConnections'));
    }

    public function store(Request $request)
    {
        if (! $request->user()->canFeature('can_organize_group_events')) {
            return back()->with('upgrade_feature', 'can_organize_group_events');
        }

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

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('events/covers', 'public');
        }

        $user   = $request->user();
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
            'is_public'       => true,
            'attendees_count' => 1,
        ]);

        $event->attendees()->attach($user->id, ['role' => 'organizer']);

        NotifyUsersNewEventJob::dispatch($event);

        ActivityLogger::log('event.created', "Événement « {$event->title} » créé", $user->id, $event);

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
            return back()->with('info', 'You are already registered for this event.');
        }

        if ($event->max_attendees !== null && $event->attendees_count >= $event->max_attendees) {
            return back()->with('error', 'This event is at full capacity.');
        }

        $event->attendees()->attach($user->id, ['role' => 'attendee']);
        $event->increment('attendees_count');

        EventInvitation::where('event_id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->update(['status' => 'accepted']);

        return back()->with('success', 'You have registered for "' . $event->title . '".');
    }

    public function leave(Request $request, int $id)
    {
        $event = Event::findOrFail($id);
        $user  = $request->user();

        if ($event->created_by === $user->id) {
            return back()->with('error', 'The organizer cannot leave the event.');
        }

        if ($event->isAttending($user->id)) {
            $event->attendees()->detach($user->id);
            $event->decrement('attendees_count');
        }

        return back()->with('success', 'You have cancelled your registration.');
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
            ->with('success', 'Event "' . $event->title . '" has been deleted.');
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

        return back()->with('success', 'Attendee removed.');
    }

    // ── Invitations ──────────────────────────────────────────────

    public function invite(Request $request, int $id)
    {
        $event = Event::findOrFail($id);
        $user  = $request->user();

        abort_if($event->created_by !== $user->id, 403);

        $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);
        $targetId = (int) $request->user_id;

        if ($event->isAttending($targetId)) {
            return back()->with('info', 'This user is already attending the event.');
        }

        EventInvitation::updateOrCreate(
            ['event_id' => $id, 'user_id' => $targetId],
            ['invited_by' => $user->id, 'status' => 'pending']
        );

        return back()->with('success', 'Invitation sent.');
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
                return back()->with('error', 'This event is at full capacity.');
            }
            $event->attendees()->attach($invitation->user_id, ['role' => 'attendee']);
            $event->increment('attendees_count');
        }

        $invitation->update(['status' => 'accepted']);

        return redirect()->route('events.show', $event->id)
            ->with('success', 'You are now registered for "' . $event->title . '".');
    }

    public function declineInvitation(Request $request, int $invId)
    {
        $invitation = EventInvitation::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->findOrFail($invId);

        $invitation->update(['status' => 'declined']);

        return back()->with('success', 'Invitation declined.');
    }
}
