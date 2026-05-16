<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json($this->profileService->getProfile($request->user()));
    }

    public function updateBasic(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name'     => 'sometimes|string|max:100',
            'last_name'      => 'sometimes|string|max:100',
            'gender'         => 'nullable|in:male,female,other',
            'birthday'       => 'nullable|date',
            'city_id' => 'nullable|integer|exists:cities,id',
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
            'completion'           => $this->profileService->getCompletionPercentage($user->fresh()),
        ]);
    }

    public function interests(): JsonResponse
    {
        return response()->json(Interest::orderBy('name')->get());
    }
}
