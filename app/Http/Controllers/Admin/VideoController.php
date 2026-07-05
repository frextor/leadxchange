<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status', 'pending');

        $profiles = Profile::with('user')
            ->whereNotNull('presentation_video')
            ->where('presentation_video_status', $status)
            ->orderByDesc('presentation_video_uploaded_at')
            ->paginate(20)->withQueryString();

        $counts = [
            'pending'  => Profile::whereNotNull('presentation_video')->where('presentation_video_status', 'pending')->count(),
            'approved' => Profile::whereNotNull('presentation_video')->where('presentation_video_status', 'approved')->count(),
            'rejected' => Profile::whereNotNull('presentation_video')->where('presentation_video_status', 'rejected')->count(),
        ];

        return view('admin.videos.index', compact('profiles', 'status', 'counts'));
    }

    public function approve(Profile $profile): RedirectResponse
    {
        $profile->update([
            'presentation_video_status'           => 'approved',
            'presentation_video_reviewed_at'      => now(),
            'presentation_video_reviewed_by'      => auth()->id(),
            'presentation_video_rejection_reason' => null,
        ]);

        ActivityLogger::log('admin.video.approved', "Vidéo approuvée (profil #{$profile->user_id})", null, $profile);
        return back()->with('success', 'Vidéo approuvée et publiée sur le profil.');
    }

    public function reject(Request $request, Profile $profile): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $profile->update([
            'presentation_video_status'           => 'rejected',
            'presentation_video_reviewed_at'      => now(),
            'presentation_video_reviewed_by'      => auth()->id(),
            'presentation_video_rejection_reason' => $request->reason,
        ]);

        ActivityLogger::log('admin.video.rejected', "Vidéo rejetée (profil #{$profile->user_id})", null, $profile, ['reason' => $request->reason]);
        return back()->with('success', 'Vidéo rejetée. L\'utilisateur sera notifié.');
    }
}
