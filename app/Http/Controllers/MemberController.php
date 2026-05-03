<?php

namespace App\Http\Controllers;

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
    public function index(): View
    {
        // Just return the view
        // JavaScript will call /api/users to fetch data
        return view('members');
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
