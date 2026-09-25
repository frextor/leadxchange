<?php

namespace App\Http\Controllers;

use App\Models\ProfileVisitor;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * MemberController (WEB)
 * 
 * Handles member/network web pages.
 * Uses UserService for business logic (Hybrid architecture).
 */
class MemberController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display members listing page.
     * 
     * GET /connections
     * 
     * @return View
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Design lx2 (maquette) : Pour toi · Contacts · Visiteurs — l'ancien onglet « search » ouvre « Pour toi »
        $tab = $request->get('tab', 'recommendations');
        $tab = in_array($tab, ['recommendations', 'contacts', 'visitors'], true) ? $tab : 'recommendations';

        return view('members.index', [
            'sectors'         => \App\Models\Sector::orderBy('name')->get(['id', 'name']),
            'newVisitorCount' => ProfileVisitor::where('profile_user_id', $user->id)
                                    ->where('is_new', true)->count(),
            'initialTab'      => $tab,
            'canViewName'     => $user->canFeature('can_view_member_name'),
            'canInvite'       => $user->canFeature('can_send_invitations'),
        ]);
    }

    /**
     * Alternative: Server-side rendered version with initial data.
     * 
     * Uncomment this if you want to pre-load first page of users.
     * 
     * @param Request $request
     * @return View
     */
    // public function indexWithData(Request $request): View
    // {
    //     $currentUserId = $request->user()->id;
    //     $page = $request->get('page', 1);
    //     $search = $request->get('search', '');
    //
    //     // Call service
    //     $users = $this->userService->getPaginatedUsers(
    //         $currentUserId,
    //         $page,
    //         $search,
    //         10
    //     );
    //
    //     return view('members', [
    //         'users' => $users,
    //         'search' => $search,
    //     ]);
    // }
}
