<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsulRequest;
use App\Models\Interest;
use App\Services\ConsulService;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService,
        private ConsulService  $consulService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json($this->profileService->getProfile($request->user()));
    }

    public function updateLocation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'city_id' => 'required|integer|exists:cities,id',
        ]);

        $this->profileService->updateBasicInfo($request->user(), $data);

        $user = $request->user()->fresh()->load('city:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Location updated',
            'city'    => [
                'id'   => $user->city_id,
                'name' => $user->city?->name,
            ],
        ]);
    }

    public function updateBasic(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name'     => 'sometimes|string|max:100',
            'last_name'      => 'sometimes|string|max:100',
            'gender'         => 'nullable|in:male,female,other',
            'birthday'       => 'nullable|date',
            'phone'          => 'nullable|string|max:20',
            'city_id'        => 'nullable|integer|exists:cities,id',
        ]);

        $this->profileService->updateBasicInfo($request->user(), $data);

        return response()->json([
            'message'    => 'Informations mises à jour',
            'completion' => $this->profileService->getCompletionPercentage($request->user()->fresh()),
        ]);
    }

    public function updateProfessional(Request $request): JsonResponse
    {
        $data = $request->validate([
            'job_title'          => 'nullable|string|max:150',
            'sector'             => 'nullable|string|max:100',
            'experience_level'   => 'nullable|in:junior,mid,senior,expert',
            'looking_for'        => 'nullable|array',
            'looking_for.*'      => 'integer|exists:sectors,id',
            'services_offered'   => 'nullable|array',
            'services_offered.*' => 'integer|exists:sectors,id',
        ]);

        $this->profileService->updateProfessional($request->user(), $data);

        return response()->json([
            'message'    => 'Profil professionnel mis à jour',
            'completion' => $this->profileService->getCompletionPercentage($request->user()->fresh()),
        ]);
    }

    public function updateBio(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bio'             => 'nullable|string|max:1000',
            'motto'           => 'nullable|string|max:500',
            'open_to_network' => 'nullable|boolean',
        ]);

        $this->profileService->updateBio($request->user(), $data);

        return response()->json([
            'message'    => 'Bio mise à jour',
            'completion' => $this->profileService->getCompletionPercentage($request->user()->fresh()),
        ]);
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate(['avatar' => 'required|image|mimes:jpeg,png,webp,jpg|max:3072']);

        $url = $this->profileService->updateAvatar($request->user(), $request->file('avatar'));

        return response()->json([
            'message'    => 'Photo mise à jour',
            'avatar_url' => $url,
            'completion' => $this->profileService->getCompletionPercentage($request->user()->fresh()),
        ]);
    }

    public function updatePresentationVideo(Request $request): JsonResponse
    {
        $request->validate([
            'presentation_video' => [
                'required',
                'file',
                'mimes:mp4,mov,webm,avi,m4v',
                'max:' . config('profile_video.max_upload_size_kb', 51200),
            ],
        ]);

        $presentationVideo = $this->profileService->updatePresentationVideo(
            $request->user(),
            $request->file('presentation_video'),
        );

        return response()->json([
            'message' => 'Presentation video uploaded for review.',
            'data' => [
                'presentation_video' => $presentationVideo,
            ],
            'presentation_video' => $presentationVideo,
        ]);
    }

    public function syncInterests(Request $request): JsonResponse
    {
        $request->validate([
            'interests'   => 'required|array',
            'interests.*' => 'integer|exists:interests,id',
        ]);

        $this->profileService->syncInterests($request->user(), $request->interests);

        return response()->json([
            'message'    => 'Intérêts mis à jour',
            'completion' => $this->profileService->getCompletionPercentage($request->user()->fresh()),
        ]);
    }

    public function complete(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->update(['onboarding_completed' => true]);

        return response()->json([
            'message'              => 'Profil marqué comme complété.',
            'onboarding_completed' => true,
            'profile_completed'    => $user->fresh()->hasCompletedProfile(),
            'completion'           => $this->profileService->getCompletionPercentage($user->fresh()),
        ]);
    }

    public function requestAmbassador(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('subscription.plan');
        $planName = $user->subscription?->plan?->name;

        if (!in_array($planName, ['vip', 'enterprise'], true)) {
            return response()->json([
                'message' => 'An active VIP or Enterprise plan is required to request Ambassador status.',
                'ambassador_status' => $user->ambassador_status ?? 'none',
            ], 403);
        }

        if ($user->ambassador_status === 'approved') {
            return response()->json([
                'message' => 'Ambassador status is already approved.',
                'ambassador_status' => 'approved',
            ], 422);
        }

        if ($user->ambassador_status === 'pending') {
            return response()->json([
                'message' => 'Ambassador request is already pending.',
                'ambassador_status' => 'pending',
            ], 422);
        }

        $user->update([
            'ambassador_status' => 'pending',
            'ambassador_requested_at' => now(),
            'ambassador_reviewed_at' => null,
            'ambassador_reviewed_by' => null,
            'ambassador_rejection_reason' => null,
        ]);

        ConsulRequest::firstOrCreate(
            ['user_id' => $user->id, 'status' => ConsulRequest::STATUS_PENDING],
        );

        return response()->json([
            'message' => 'Ambassador request submitted.',
            'ambassador_status' => 'pending',
        ]);
    }

    public function requestConsul(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('subscription.plan');

        if ($user->isConsul()) {
            return response()->json([
                'message'      => 'Vous êtes déjà Consul.',
                'consul_status' => 'approved',
            ], 422);
        }

        if ($user->consul_status === 'pending') {
            return response()->json([
                'message'      => 'Vous avez déjà une demande en attente.',
                'consul_status' => 'pending',
            ], 422);
        }

        if (! $this->consulService->hasPremiumAccess($user)) {
            return response()->json([
                'message'      => 'Vous devez avoir un abonnement Premium pour demander le statut Consul.',
                'consul_status' => null,
            ], 422);
        }

        $user->update(['consul_status' => 'pending']);

        $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
        foreach ($admins as $admin) {
            try {
                \App\Models\Notification::storeForUser(
                    $admin,
                    'consul_request_submitted',
                    'Nouvelle demande Consul',
                    "{$user->first_name} {$user->last_name} (Premium) demande le statut Consul.",
                    ['user_id' => $user->id]
                );
            } catch (\Throwable) {}
        }

        return response()->json([
            'message'      => 'Consul request submitted.',
            'consul_status' => 'pending',
        ]);
    }

    public function interests(): JsonResponse
    {
        return response()->json(Interest::orderBy('name')->get());
    }
}
