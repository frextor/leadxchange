<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function show(int $id, Request $request): View|RedirectResponse
    {
        $currentUserId = $request->user()->id;
        $user = $this->userService->getProfileById($id, $currentUserId);

        if (!$user) {
            return redirect()->route('connections.index');
        }

        return view('profile', [
            'user'          => $user,
            'isOwnProfile'  => $id === $currentUserId,
        ]);
    }
}
