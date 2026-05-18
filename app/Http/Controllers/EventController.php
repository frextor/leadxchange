<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Event;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $sectors = Sector::orderBy('name')->get();
        $cities  = City::orderBy('name')->get();

        $query = Event::with(['sector:id,name', 'creator:id,first_name,last_name', 'city:id,name'])
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

        $all      = $query->orderBy('starts_at')->get();
        $upcoming = $all->filter(fn($e) => $e->starts_at->isFuture());
        $past     = $all->filter(fn($e) => $e->starts_at->isPast());

        $attendingEventIds = $user->events()->pluck('events.id')->toArray();
        $featured          = $upcoming->first();

        return view('events.index', compact(
            'upcoming', 'past', 'sectors', 'cities', 'attendingEventIds', 'featured'
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

        return view('events.show', compact('event', 'attendees', 'isAttending'));
    }

    public function store(Request $request)
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

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('events/covers', 'public');
        }

        $user  = $request->user();
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

        return redirect()->route('events.index')
            ->with('success', 'Event "' . $event->title . '" created successfully!');
    }

    public function join(Request $request, int $id)
    {
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

        return back()->with('success', 'You have registered for "' . $event->title . '".');
    }

    public function leave(Request $request, int $id)
    {
        $event = Event::findOrFail($id);
        $user  = $request->user();

        if ($event->isAttending($user->id)) {
            $event->attendees()->detach($user->id);
            $event->decrement('attendees_count');
        }

        return back()->with('success', 'You have cancelled your registration.');
    }
}
