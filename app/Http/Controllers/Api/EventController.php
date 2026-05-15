<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Sector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Event::with(['sector:id,name', 'creator:id,first_name,last_name'])
            ->where('is_public', true);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }
        if ($request->filled('price_filter')) {
            if ($request->price_filter === 'free') {
                $query->where(fn($q) => $q->whereNull('price')->orWhere('price', 0));
            } elseif ($request->price_filter === 'paid') {
                $query->where('price', '>', 0);
            }
        }
        if ($request->filled('when')) {
            $now = now();
            match ($request->when) {
                'today'      => $query->whereDate('starts_at', $now->toDateString()),
                'this_week'  => $query->whereBetween('starts_at', [$now->startOfDay(), $now->copy()->endOfWeek()]),
                'this_month' => $query->whereBetween('starts_at', [$now->startOfDay(), $now->copy()->endOfMonth()]),
                default      => null,
            };
        }

        $events = $query->orderBy('starts_at')->get();
        $attendingIds = $request->user()->events()->pluck('events.id')->toArray();

        $mapped   = $events->map(fn($e) => $this->formatEvent($e, $attendingIds));
        $upcoming = $mapped->filter(fn($e) => $e['is_upcoming'])->values();
        $past     = $mapped->filter(fn($e) => !$e['is_upcoming'])->values();

        return response()->json([
            'data' => ['upcoming' => $upcoming, 'past' => $past],
            'meta' => ['total' => $events->count()],
        ]);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $event = Event::with(['sector:id,name', 'creator:id,first_name,last_name'])
            ->findOrFail($id);

        $attendingIds = $request->user()->events()->pluck('events.id')->toArray();

        return response()->json(['data' => $this->formatEvent($event, $attendingIds)]);
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
            'cover_color'   => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'cover_image'   => ['nullable', 'image', 'max:2048'],
            'price'         => ['nullable', 'numeric', 'min:0'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
        ]);

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
            'cover_color'     => $validated['cover_color'] ?? '#1E8F88',
            'cover_image'     => $coverImagePath,
            'price'           => $validated['price'] ?? null,
            'max_attendees'   => $validated['max_attendees'] ?? null,
            'created_by'      => $request->user()->id,
            'is_public'       => true,
            'attendees_count' => 1,
        ]);

        $event->attendees()->attach($request->user()->id, ['role' => 'organizer']);

        return response()->json([
            'message' => 'Event created successfully.',
            'data'    => $this->formatEvent($event->load(['sector', 'creator']), [$event->id]),
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

    private function formatEvent(Event $event, array $attendingIds): array
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
            'sector'          => $event->sector ? ['id' => $event->sector->id, 'name' => $event->sector->name] : null,
            'creator'         => $event->creator ? [
                'id'   => $event->creator->id,
                'name' => $event->creator->first_name . ' ' . $event->creator->last_name,
            ] : null,
            'created_at'      => $event->created_at,
        ];
    }
}
