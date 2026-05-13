<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\Event;
use App\Models\Group;
use App\Models\Plan;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private ProfileService $profileService) {}

    public function index(Request $request)
    {
        $user = $request->user()->load(['profile', 'subscription.plan', 'interests', 'company']);

        $connectedIds = Connection::where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
        })->accepted()->get()->map(
            fn($c) => $c->sender_id === $user->id ? $c->receiver_id : $c->sender_id
        );

        $connectionCount = $connectedIds->count();
        $pendingCount    = Connection::where('receiver_id', $user->id)->pending()->count();
        $groupCount      = $user->groups()->count();

        $completion = $this->profileService->getCompletionPercentage($user);
        $missing    = $this->profileService->getMissingFields($user);

        $prospects = User::with(['profile', 'company'])
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $connectedIds->toArray())
            ->latest()
            ->limit(4)
            ->get();

        $plans = Plan::orderBy('price')->get();

        $memberGroupIds = $user->groups()->pluck('groups.id')->toArray();
        $featuredGroups = Group::with(['sector:id,name'])
            ->withCount('members')
            ->where('is_public', true)
            ->orderBy('members_count', 'desc')
            ->limit(3)
            ->get();

        $upcomingEvents    = Event::with(['sector:id,name'])
            ->where('is_public', true)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(3)
            ->get();
        $attendingEventIds = $user->events()->pluck('events.id')->toArray();

        return view('dashboard', compact(
            'user', 'connectionCount', 'pendingCount', 'groupCount',
            'completion', 'missing', 'prospects', 'plans',
            'featuredGroups', 'memberGroupIds',
            'upcomingEvents', 'attendingEventIds'
        ));
    }
}
