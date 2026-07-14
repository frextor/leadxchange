<?php

namespace App\Http\Controllers\Consul;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $consul = $request->user();

        $groups = Group::wherePivotIn('role', ['owner', 'admin'])
            ->whereHas('members', fn($q) => $q->where('users.id', $consul->id)->whereIn('group_user.role', ['owner', 'admin']))
            ->withCount('members')
            ->orderBy('name')
            ->get();

        // Quick stats
        $totalMembers = $groups->sum('members_count');
        $totalGroups  = $groups->count();

        $upcomingEvents = \App\Models\Event::whereHas('attendees', fn($q) =>
                $q->where('users.id', $consul->id)->where('event_user.role', 'organizer'))
            ->where('starts_at', '>', now())
            ->with('city')
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        return view('consul.dashboard', compact(
            'consul', 'groups', 'totalMembers', 'totalGroups', 'upcomingEvents'
        ));
    }

    public function groupMembers(Request $request, Group $group): View
    {
        $consul = $request->user();

        abort_unless(
            $group->members()->where('users.id', $consul->id)->whereIn('group_user.role', ['owner', 'admin'])->exists(),
            403
        );

        $members = $group->members()
            ->with(['profile:id,user_id,job_title,avatar', 'city:id,name', 'subscription.plan:id,name'])
            ->orderBy('users.first_name')
            ->get();

        return view('consul.group-members', compact('consul', 'group', 'members'));
    }

    public function groupEvents(Request $request, Group $group): View
    {
        $consul = $request->user();

        abort_unless(
            $group->members()->where('users.id', $consul->id)->whereIn('group_user.role', ['owner', 'admin'])->exists(),
            403
        );

        $memberIds = $group->members()->pluck('users.id')->toArray();

        $events = \App\Models\Event::where('title', $group->name)
            ->orWhereHas('attendees', fn($q) => $q->whereIn('users.id', $memberIds))
            ->with(['city:id,name', 'creator:id,first_name,last_name', 'attendees.profile:id,user_id,avatar'])
            ->withCount('attendees')
            ->orderBy('starts_at', 'desc')
            ->get();

        return view('consul.group-events', compact('consul', 'group', 'events', 'memberIds'));
    }
}
