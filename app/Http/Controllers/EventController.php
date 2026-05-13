<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Sector;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $sectors = Sector::orderBy('name')->get();

        $query = Event::with(['sector:id,name', 'creator:id,first_name,last_name'])
            ->where('is_public', true);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('category')) {
            $query->where('sector_id', $request->category);
        }

        $all      = $query->orderBy('starts_at')->get();
        $upcoming = $all->filter(fn($e) => $e->starts_at->isFuture());
        $past     = $all->filter(fn($e) => $e->starts_at->isPast());

        $attendingEventIds = $user->events()->pluck('events.id')->toArray();

        return view('events.index', compact('upcoming', 'past', 'sectors', 'attendingEventIds'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'         => ['required', 'string', 'max:150'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'type'          => ['required', 'in:virtual,in_person,hybrid'],
            'location'      => ['nullable', 'string', 'max:255'],
            'meeting_link'  => ['nullable', 'url', 'max:500'],
            'starts_at'     => ['required', 'date', 'after:now'],
            'ends_at'       => ['nullable', 'date', 'after:starts_at'],
            'sector_id'     => ['nullable', 'integer', 'exists:sectors,id'],
            'cover_color'   => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
        ]);

        $event = Event::create([
            ...$validated,
            'created_by'      => $request->user()->id,
            'is_public'       => true,
            'attendees_count' => 1,
        ]);

        $event->attendees()->attach($request->user()->id, ['role' => 'organizer']);

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
