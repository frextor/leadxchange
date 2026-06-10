<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Interest;
use App\Models\Profile;
use App\Models\ProfileVisitor;
use App\Models\Sector;
use App\Models\User;
use App\Services\ProfileService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        $videoFlash = session('video_status');

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

    public function uploadVideo(Request $request): RedirectResponse
    {
        $request->validate([
            'video' => ['required', 'file', 'mimes:mp4,webm,mov,avi', 'max:102400'],
        ], [
            'video.required' => 'Veuillez sélectionner une vidéo.',
            'video.mimes'    => 'Format accepté : MP4, WebM, MOV, AVI.',
            'video.max'      => 'La vidéo ne doit pas dépasser 100 Mo.',
        ]);

        $user    = $request->user();
        $profile = $user->profile ?? Profile::create(['user_id' => $user->id]);

        if ($profile->presentation_video) {
            Storage::disk('public')->delete($profile->presentation_video);
        }

        $path = $request->file('video')->store("profiles/videos/{$user->id}", 'public');

        $profile->update([
            'presentation_video'                  => $path,
            'presentation_video_status'           => 'pending',
            'presentation_video_uploaded_at'      => now(),
            'presentation_video_rejection_reason' => null,
            'presentation_video_reviewed_at'      => null,
            'presentation_video_reviewed_by'      => null,
        ]);

        return redirect()->route('profile.show', $user->id)
            ->with('success', 'Vidéo uploadée. Elle sera visible après validation par notre équipe.');
    }

    public function deleteVideo(Request $request): RedirectResponse
    {
        $user    = $request->user();
        $profile = $user->profile;

        if ($profile?->presentation_video) {
            Storage::disk('public')->delete($profile->presentation_video);
            $profile->update([
                'presentation_video'                  => null,
                'presentation_video_status'           => null,
                'presentation_video_uploaded_at'      => null,
                'presentation_video_rejection_reason' => null,
                'presentation_video_reviewed_at'      => null,
                'presentation_video_reviewed_by'      => null,
            ]);
        }

        return redirect()->route('profile.show', $user->id)
            ->with('success', 'Vidéo de présentation supprimée.');
    }
}
