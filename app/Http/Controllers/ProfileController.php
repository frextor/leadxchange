<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Interest;
use App\Models\ProfileVisitor;
use App\Models\Sector;
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

        // Track profile visit (not own profile)
        if ($id !== $currentUserId) {
            $visit = ProfileVisitor::where('profile_user_id', $id)
                ->where('visitor_id', $currentUserId)
                ->first();

            if ($visit) {
                $visit->increment('visit_count');
                $visit->update(['last_visited_at' => now(), 'is_new' => true]);
            } else {
                ProfileVisitor::create([
                    'profile_user_id' => $id,
                    'visitor_id'      => $currentUserId,
                    'visit_count'     => 1,
                    'last_visited_at' => now(),
                    'is_new'          => true,
                ]);
            }
        }

        return view('profile', [
            'user'          => $user,
            'profile'       => $targetUser->profile,
            'userInterests' => $targetUser->interests,
            'allInterests'  => Interest::orderBy('name')->get(),
            'sectors'       => Sector::orderBy('name')->get(),
            'cities'        => City::with('country:id,name')->orderBy('name')->get(),
            'completion'    => $this->profileService->getCompletionPercentage($targetUser),
            'missing'       => $id === $currentUserId ? $this->profileService->getMissingFields($targetUser) : [],
            'isOwnProfile'  => $id === $currentUserId,
        ]);
    }
}
