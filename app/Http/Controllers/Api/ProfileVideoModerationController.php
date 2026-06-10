<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Services\ProfileVideoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileVideoModerationController extends Controller
{
    public function __construct(private ProfileVideoService $profileVideoService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $status = $request->query('status', ProfileVideoService::STATUS_PENDING);

        $profiles = Profile::query()
            ->with('user:id,first_name,last_name,email')
            ->whereNotNull('presentation_video')
            ->when($status, fn ($query) => $query->where('presentation_video_status', $status))
            ->latest('presentation_video_uploaded_at')
            ->paginate((int) $request->query('per_page', 20));

        $profiles->getCollection()->transform(fn (Profile $profile) => $this->formatProfile($profile));

        return response()->json($profiles);
    }

    public function approve(Request $request, int $userId): JsonResponse
    {
        $this->authorizeAdmin($request);

        $profile = Profile::where('user_id', $userId)->whereNotNull('presentation_video')->firstOrFail();
        $this->profileVideoService->approve($profile, $request->user());

        return response()->json([
            'message' => 'Presentation video approved.',
            'data' => $this->formatProfile($profile->fresh('user')),
        ]);
    }

    public function reject(Request $request, int $userId): JsonResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $profile = Profile::where('user_id', $userId)->whereNotNull('presentation_video')->firstOrFail();
        $this->profileVideoService->reject($profile, $request->user(), $data['reason'] ?? null);

        return response()->json([
            'message' => 'Presentation video rejected.',
            'data' => $this->formatProfile($profile->fresh('user')),
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403, 'Admins only.');
    }

    private function formatProfile(Profile $profile): array
    {
        return [
            'user' => $profile->user ? [
                'id' => $profile->user->id,
                'first_name' => $profile->user->first_name,
                'last_name' => $profile->user->last_name,
                'email' => $profile->user->email,
            ] : null,
            'presentation_video' => [
                'url' => $profile->presentation_video_url,
                'status' => $profile->presentation_video_status,
                'rejection_reason' => $profile->presentation_video_rejection_reason,
                'uploaded_at' => $profile->presentation_video_uploaded_at,
                'reviewed_at' => $profile->presentation_video_reviewed_at,
                'reviewed_by' => $profile->presentation_video_reviewed_by,
            ],
        ];
    }
}
