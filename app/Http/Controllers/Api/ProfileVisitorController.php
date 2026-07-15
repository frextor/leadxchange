<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Connection;
use App\Models\ProfileVisitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileVisitorController extends Controller
{
    /**
     * GET /api/profile/visitors
     *
     * Returns paginated list of users who visited the authenticated user's profile.
     * Also marks all as seen when filter=all.
     */
    public function index(Request $request): JsonResponse
    {
        $me     = $request->user();
        $filter = $request->get('filter', 'all');
        $page   = (int) $request->get('page', 1);

        $query = ProfileVisitor::with([
                'visitor:id,first_name,last_name,email,city_id,company_id',
                'visitor.company:id,name,sector_id',
                'visitor.company.sector:id,name',
                'visitor.profile:id,user_id,job_title,avatar,open_to_network',
                'visitor.city:id,name',
            ])
            ->whereHas('visitor', fn($q) => $q->regular())
            ->where('profile_user_id', $me->id)
            ->orderByDesc('last_visited_at');

        if ($filter === 'new') {
            $query->where('is_new', true);
        }

        $paginated = $query->paginate(10, ['*'], 'page', $page);

        // Mark all as seen when loading the full list
        if ($filter === 'all' && $page === 1) {
            ProfileVisitor::where('profile_user_id', $me->id)
                ->where('is_new', true)
                ->update(['is_new' => false]);
        }

        $myId = $me->id;
        $items = $paginated->getCollection()->filter(fn ($v) => $v->visitor !== null)->map(function (ProfileVisitor $visit) use ($myId) {
            $u = $visit->visitor;

            // Connection status
            $conn = Connection::where(function ($q) use ($myId, $u) {
                $q->where('sender_id', $myId)->where('receiver_id', $u->id);
            })->orWhere(function ($q) use ($myId, $u) {
                $q->where('sender_id', $u->id)->where('receiver_id', $myId);
            })->first();

            return [
                'id'               => $u->id,
                'first_name'       => $u->first_name,
                'last_name'        => $u->last_name,
                'email'            => $u->email,
                'city'      => $u->city?->name,
                'avatar'           => $u->profile?->avatar_url,
                'job_title'        => $u->profile?->job_title,
                'company'          => $u->company ? ['id' => $u->company->id, 'name' => $u->company->name, 'sector' => $u->company->sector ? ['id' => $u->company->sector->id, 'name' => $u->company->sector->name] : null] : null,
                'connection_status' => $conn?->status,
                'connection_id'     => $conn?->id,
                'i_am_sender'       => $conn ? ($conn->sender_id === $myId) : false,
                'i_am_receiver'     => $conn ? ($conn->receiver_id === $myId) : false,
                'visit_count'       => $visit->visit_count,
                'visited_at_human'  => $visit->last_visited_at->diffForHumans(),
                'is_new'            => $visit->is_new,
            ];
        });

        $newCount = ProfileVisitor::where('profile_user_id', $myId)->where('is_new', true)->count();

        return response()->json([
            'success'      => true,
            'data'         => $items,
            'new_count'    => $newCount,
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
        ]);
    }
}
