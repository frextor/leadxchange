<?php

namespace App\Http\Controllers;

use App\Models\Interest;
use App\Models\User;
use App\Services\ProfileService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private UserService    $userService,
        private ProfileService $profileService,
    ) {}

    public function show(int $id, Request $request): View|RedirectResponse
    {
        $currentUserId = $request->user()->id;
        $user          = $this->userService->getProfileById($id, $currentUserId);

        if (!$user) {
            return redirect()->route('connections.index');
        }

        $targetUser = User::with(['profile', 'interests'])->find($id);

        return view('profile', [
            'user'          => $user,
            'profile'       => $targetUser->profile,
            'userInterests' => $targetUser->interests,
            'allInterests'  => Interest::orderBy('name')->get(),
            'completion'    => $this->profileService->getCompletionPercentage($targetUser),
            'missing'       => $id === $currentUserId ? $this->profileService->getMissingFields($targetUser) : [],
            'isOwnProfile'  => $id === $currentUserId,
        ]);
    }
}
