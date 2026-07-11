<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ambassador\Concerns\ScopesAmbassadorRegion;
use App\Models\City;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\Sector;
use App\Services\AmbassadorService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventsController extends Controller
{
    use ScopesAmbassadorRegion;

    public function __construct(private AmbassadorService $service) {}

    public function index(Request $request): View
    {
        $ambassador = $request->user()->load('city', 'region');
        $regionName = $this->regionName($ambassador);

        $baseQuery = $this->service->ambassadorEventsQuery($ambassador)->with('city', 'sector');

        $tab      = $request->get('tab', 'upcoming');
        $upcoming = (clone $baseQuery)->where('starts_at', '>', now())->orderBy('starts_at')->paginate(12);
        $past     = (clone $baseQuery)->where('ends_at', '<', now())->latest('ends_at')->paginate(12);

        $stats = [
            'total'     => $this->service->totalEventsCount($ambassador),
            'upcoming'  => $this->service->upcomingEventsCount($ambassador),
            'attendees' => $this->service->ambassadorEventsQuery($ambassador)->sum('attendees_count'),
        ];

        return view('ambassador.events.index', compact(
            'ambassador', 'regionName', 'upcoming', 'past', 'stats', 'tab'
        ));
    }

    public function create(Request $request): View
    {
        $ambassador = $request->user();
        $sectors    = Sector::orderBy('name')->get();
        $cities     = City::orderBy('name')->limit(200)->get();

        return view('ambassador.events.create', compact('ambassador', 'sectors', 'cities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $ambassador = $request->user();

        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string|max:5000',
            'type'          => 'required|in:virtual,in_person,hybrid',
            'category'      => 'nullable|string|max:100',
            'location'      => 'nullable|string|max:255',
            'meeting_link'  => 'nullable|url|max:500',
            'starts_at'     => 'required|date|after:now',
            'ends_at'       => 'required|date|after:starts_at',
            'max_attendees' => 'nullable|integer|min:1',
            'price'         => 'nullable|numeric|min:0',
            'sector_id'     => 'nullable|exists:sectors,id',
            'city_id'       => 'nullable|exists:cities,id',
            'cover_color'   => 'nullable|string|max:20',
        ]);

        $data['created_by'] = $ambassador->id;
        $data['region_id']  = $ambassador->region_id ?? $ambassador->city_id;
        $data['is_public']  = true;

        Event::create($data);

        return redirect()->route('ambassador.events.index')->with('success', 'Événement créé avec succès.');
    }

    public function show(Event $event): View
    {
        $ambassador = auth()->user();
        $this->authorizeEvent($ambassador, $event);

        $registrations = $event->users()->withPivot('role', 'registered_at')->latest('registered_at')->get();
        $invitations   = $event->invitations()->with('user')->latest()->get();

        $organizerGroups = collect();
        $matchingGroup   = null;
        if ($event->created_by === $ambassador->id) {
            $organizerGroups = $ambassador->groups()
                ->wherePivotIn('role', ['owner', 'admin'])
                ->withCount('members')
                ->orderByRaw('name = ? DESC', [$event->title])
                ->orderBy('name')
                ->get(['groups.id', 'groups.name']);
            $matchingGroup = $organizerGroups->firstWhere('name', $event->title);
        }

        return view('ambassador.events.show', compact('event', 'registrations', 'invitations', 'ambassador', 'organizerGroups', 'matchingGroup'));
    }

    public function inviteGroup(Request $request, Event $event): RedirectResponse
    {
        $ambassador = $request->user();
        abort_if($event->created_by !== $ambassador->id, 403);

        $request->validate(['group_id' => ['required', 'integer', 'exists:groups,id']]);
        $group = \App\Models\Group::findOrFail($request->group_id);
        abort_unless($group->isAdmin($ambassador->id), 403);

        $attendingIds = $event->attendees()->pluck('users.id')->toArray();
        $members = $group->members()
            ->whereNotIn('users.id', $attendingIds)
            ->whereNotIn('users.id', [$ambassador->id])
            ->get(['users.id', 'users.first_name', 'users.last_name']);

        $sent = 0;
        foreach ($members as $member) {
            EventInvitation::updateOrCreate(
                ['event_id' => $event->id, 'user_id' => $member->id],
                ['invited_by' => $ambassador->id, 'status' => 'pending']
            );
            try {
                \App\Models\Notification::storeForUser(
                    $member,
                    'event_invitation',
                    'Invitation à un événement',
                    "{$ambassador->first_name} {$ambassador->last_name} vous invite à l'événement « {$event->title} ».",
                    ['url' => route('events.show', $event->id), 'event_id' => $event->id]
                );
            } catch (\Throwable) {}
            $sent++;
        }

        ActivityLogger::log('event.group_invite', "Groupe « {$group->name} » invité à « {$event->title} » ({$sent} membres)", $ambassador->id, $event);

        return back()->with('success', "{$sent} membre(s) du groupe « {$group->name} » invité(s).");
    }

    public function edit(Event $event): View
    {
        $ambassador = auth()->user();
        $this->authorizeEvent($ambassador, $event);

        $sectors = Sector::orderBy('name')->get();
        $cities  = City::orderBy('name')->limit(200)->get();

        return view('ambassador.events.edit', compact('event', 'sectors', 'cities', 'ambassador'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $ambassador = $request->user();
        $this->authorizeEvent($ambassador, $event);

        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string|max:5000',
            'type'          => 'required|in:virtual,in_person,hybrid',
            'category'      => 'nullable|string|max:100',
            'location'      => 'nullable|string|max:255',
            'meeting_link'  => 'nullable|url|max:500',
            'starts_at'     => 'required|date',
            'ends_at'       => 'required|date|after:starts_at',
            'max_attendees' => 'nullable|integer|min:1',
            'price'         => 'nullable|numeric|min:0',
            'sector_id'     => 'nullable|exists:sectors,id',
            'city_id'       => 'nullable|exists:cities,id',
        ]);

        $event->update($data);

        return redirect()->route('ambassador.events.show', $event)->with('success', 'Événement mis à jour.');
    }

    public function cancel(Event $event): RedirectResponse
    {
        $ambassador = auth()->user();
        $this->authorizeEvent($ambassador, $event);

        $event->update(['is_public' => false]);

        return back()->with('success', 'Événement annulé.');
    }

    public function exportParticipants(Event $event)
    {
        $ambassador = auth()->user();
        $this->authorizeEvent($ambassador, $event);

        $rows   = $event->users()->withPivot('role', 'registered_at')->get();
        $output = "Prénom,Nom,Email,Rôle,Date inscription\n";

        foreach ($rows as $u) {
            $output .= implode(',', [
                $u->first_name,
                $u->last_name,
                $u->email,
                $u->pivot->role ?? 'attendee',
                $u->pivot->registered_at ?? '',
            ]) . "\n";
        }

        return response($output, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="participants-' . $event->id . '.csv"',
        ]);
    }

    private function authorizeEvent(mixed $ambassador, Event $event): void
    {
        abort_unless($event->created_by === $ambassador->id || $event->region_id === $ambassador->region_id, 403);
    }
}
