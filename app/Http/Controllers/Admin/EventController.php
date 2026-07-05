<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Sector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $query = Event::with(['creator', 'sector', 'city']);

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
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
        if ($request->filled('status')) {
            $request->status === 'upcoming'
                ? $query->where('starts_at', '>', now())
                : $query->where('ends_at', '<', now());
        }

        $events  = $query->orderByDesc('created_at')->paginate(25)->withQueryString();
        $sectors = Sector::orderBy('name')->get(['id', 'name']);

        $counts = [
            'total'    => Event::count(),
            'upcoming' => Event::where('starts_at', '>', now())->count(),
            'past'     => Event::where('ends_at', '<', now())->count(),
        ];

        return view('admin.events.index', compact('events', 'sectors', 'counts'));
    }

    public function destroy(Event $event): RedirectResponse
    {
        $title = $event->title;
        $event->delete();
        ActivityLogger::log('admin.event.deleted', "Événement \"{$title}\" supprimé");

        return redirect()->route('admin.events.index')
            ->with('success', "Événement \"{$title}\" supprimé.");
    }
}
