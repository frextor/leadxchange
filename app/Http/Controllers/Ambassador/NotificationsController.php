<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ambassador\Concerns\ScopesAmbassadorRegion;
use App\Models\EventInvitation;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationsController extends Controller
{
    use ScopesAmbassadorRegion;

    public function index(Request $request): View
    {
        $ambassador = $request->user()->load('city', 'region');

        // New members in region (last 30 days)
        $newMembers = User::with('profile', 'city')
            ->where('role', 'user')
            ->where(function ($q) use ($ambassador) {
                if ($ambassador->region_id) {
                    $q->where('region_id', $ambassador->region_id);
                } else {
                    $q->where('city_id', $ambassador->city_id);
                }
            })
            ->where('created_at', '>=', now()->subDays(30))
            ->latest()
            ->get();

        // Event registrations on ambassador events
        $eventRegistrations = \DB::table('event_user')
            ->join('events', 'event_user.event_id', '=', 'events.id')
            ->join('users', 'event_user.user_id', '=', 'users.id')
            ->where('events.created_by', $ambassador->id)
            ->where('event_user.registered_at', '>=', now()->subDays(30))
            ->select('users.first_name', 'users.last_name', 'events.title', 'event_user.registered_at')
            ->latest('event_user.registered_at')
            ->limit(20)
            ->get();

        // Leads activity
        $recentLeads = Lead::with('receiver.profile')
            ->where('sender_id', $ambassador->id)
            ->where('updated_at', '>=', now()->subDays(30))
            ->whereIn('status', ['accepted', 'rejected', 'converted'])
            ->latest('updated_at')
            ->limit(20)
            ->get();

        // Invitation responses
        $invitationResponses = EventInvitation::with('user.profile', 'event')
            ->where('invited_by', $ambassador->id)
            ->whereIn('status', ['accepted', 'declined'])
            ->where('updated_at', '>=', now()->subDays(30))
            ->latest()
            ->limit(20)
            ->get();

        return view('ambassador.notifications.index', compact(
            'ambassador', 'newMembers', 'eventRegistrations', 'recentLeads', 'invitationResponses'
        ));
    }
}
