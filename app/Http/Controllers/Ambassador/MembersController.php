<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ambassador\Concerns\ScopesAmbassadorRegion;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\User;
use App\Services\AmbassadorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembersController extends Controller
{
    use ScopesAmbassadorRegion;

    public function __construct(private AmbassadorService $service) {}

    public function index(Request $request): View
    {
        $ambassador = $request->user()->load('city', 'region');
        $regionName = $this->regionName($ambassador);

        $members = $this->service->regionMembersQuery($ambassador)
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhereHas('company', fn ($c) => $c->where('name', 'like', "%{$request->search}%"));
            }))
            ->when($request->filled('plan'), fn ($q) => $q->whereHas('subscription.plan', fn ($p) => $p->where('name', $request->plan)))
            ->when($request->filled('sort'), fn ($q) => match ($request->sort) {
                'name'     => $q->orderBy('first_name'),
                'activity' => $q->latest('updated_at'),
                default    => $q->latest(),
            }, fn ($q) => $q->latest())
            ->paginate(20)
            ->withQueryString();

        $events = Event::where('created_by', $ambassador->id)->where('starts_at', '>', now())->limit(10)->get();

        return view('ambassador.members.index', compact('ambassador', 'members', 'regionName', 'events'));
    }

    public function inviteToEvent(Request $request, User $user): RedirectResponse
    {
        $ambassador = $request->user();

        if (!$this->isSameRegion($ambassador, $user)) {
            return back()->with('error', 'Cet utilisateur n\'est pas dans votre région.');
        }

        $request->validate(['event_id' => 'required|exists:events,id']);

        $event = Event::findOrFail($request->event_id);

        if ($event->created_by !== $ambassador->id) {
            return back()->with('error', 'Vous ne pouvez inviter qu\'à vos propres événements.');
        }

        EventInvitation::firstOrCreate([
            'event_id'   => $event->id,
            'user_id'    => $user->id,
            'invited_by' => $ambassador->id,
        ], ['status' => 'pending']);

        return back()->with('success', "{$user->first_name} a été invité(e) à l'événement.");
    }
}
