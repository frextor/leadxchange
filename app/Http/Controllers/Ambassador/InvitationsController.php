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

class InvitationsController extends Controller
{
    use ScopesAmbassadorRegion;

    public function __construct(private AmbassadorService $service) {}

    public function index(Request $request): View
    {
        $ambassador = $request->user();

        $kpis = [
            'sent'     => $this->service->invitationsSentCount($ambassador),
            'pending'  => $this->service->invitationsPendingCount($ambassador),
            'accepted' => $this->service->invitationsAcceptedCount($ambassador),
            'declined' => EventInvitation::where('invited_by', $ambassador->id)->where('status', 'declined')->count(),
        ];

        $invitations = EventInvitation::with(['user.profile', 'event'])
            ->where('invited_by', $ambassador->id)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $events = Event::where('created_by', $ambassador->id)->where('starts_at', '>', now())->get();

        // Members in region eligible to invite
        $eligibleMembers = $this->service->regionMembersQuery($ambassador)
            ->with('profile')
            ->limit(50)
            ->get();

        return view('ambassador.invitations.index', compact(
            'ambassador', 'kpis', 'invitations', 'events', 'eligibleMembers'
        ));
    }

    public function send(Request $request, User $user): RedirectResponse
    {
        $ambassador = $request->user();

        if (!$this->isSameRegion($ambassador, $user)) {
            return back()->with('error', 'Cet utilisateur n\'est pas dans votre région.');
        }

        $request->validate(['event_id' => 'required|exists:events,id']);

        EventInvitation::firstOrCreate([
            'event_id'   => $request->event_id,
            'user_id'    => $user->id,
            'invited_by' => $ambassador->id,
        ], ['status' => 'pending']);

        return back()->with('success', "{$user->first_name} a été invité(e).");
    }
}
