<?php

namespace App\Services;

use App\Models\Interest;
use App\Models\Profile;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function getProfile(User $user): array
    {
        $user->loadMissing(['profile', 'interests', 'company']);

        $sectorIds = array_unique(array_merge(
            $user->profile?->looking_for      ?? [],
            $user->profile?->services_offered ?? [],
        ));
        $sectorMap = $sectorIds
            ? Sector::whereIn('id', $sectorIds)->pluck('name', 'id')
            : collect();

        $profile = $user->profile;

        return [
            'id'           => $user->id,
            'first_name'   => $user->first_name,
            'last_name'    => $user->last_name,
            'email'        => $user->email,
            'gender'       => $user->gender,
            'city_birth'   => $user->city_birth,
            'city_living'  => $user->city_living,
            'birthday'     => $user->birthday?->format('Y-m-d'),
            'member_since' => $user->created_at?->format('F Y'),
            'profile'      => $profile ? array_merge($profile->toArray(), [
                'looking_for'      => collect($profile->looking_for ?? [])->map(fn($id) => ['id' => $id, 'name' => $sectorMap[$id] ?? null])->values(),
                'services_offered' => collect($profile->services_offered ?? [])->map(fn($id) => ['id' => $id, 'name' => $sectorMap[$id] ?? null])->values(),
            ]) : null,
            'interests'    => $user->interests,
            'company'      => $user->company,
            'completion'   => $this->getCompletionPercentage($user),
        ];
    }

    public function updateBasicInfo(User $user, array $data): void
    {
        $userFields = array_filter(
            array_intersect_key($data, array_flip(['first_name', 'last_name', 'gender', 'city_birth', 'city_living', 'birthday'])),
            fn($v) => $v !== null
        );

        if (!empty($userFields)) {
            $user->update($userFields);
        }
    }

    public function updateProfessional(User $user, array $data): void
    {
        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);
        $fields  = array_intersect_key($data, array_flip([
            'job_title', 'sector', 'experience_level', 'looking_for', 'services_offered',
        ]));
        $profile->fill(array_filter($fields, fn($v) => $v !== null));
        if (array_key_exists('looking_for', $data))      $profile->looking_for      = $data['looking_for']      ?? [];
        if (array_key_exists('services_offered', $data)) $profile->services_offered = $data['services_offered'] ?? [];
        $profile->save();
    }

    public function updateBio(User $user, array $data): void
    {
        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);
        $profile->fill([
            'bio'             => $data['bio']             ?? $profile->bio,
            'motto'           => $data['motto']           ?? $profile->motto,
            'open_to_network' => $data['open_to_network'] ?? $profile->open_to_network,
        ]);
        $profile->save();
    }

    public function updateAvatar(User $user, UploadedFile $file): string
    {
        $profile = $user->profile ?? Profile::create(['user_id' => $user->id]);

        if ($profile->avatar) {
            Storage::disk('public')->delete($profile->avatar);
        }

        $path = $file->store('avatars', 'public');
        $profile->update(['avatar' => $path]);

        return Storage::url($path);
    }

    public function syncInterests(User $user, array $interestIds): void
    {
        $user->interests()->sync($interestIds);
    }

    public function getCompletionPercentage(User $user): int
    {
        $user->loadMissing(['profile', 'interests', 'company']);
        $p = $user->profile;

        $checks = [
            fn() => !is_null($p?->avatar),
            fn() => !is_null($p?->bio) || !is_null($p?->motto),
            fn() => !is_null($p?->job_title),
            fn() => !is_null($p?->sector),
            fn() => !is_null($p?->experience_level),
            fn() => !is_null($user->city_living),
            fn() => !is_null($user->company_id),
            fn() => $user->interests->isNotEmpty(),
            fn() => !empty($p?->looking_for),
            fn() => !is_null($user->gender),
            fn() => !is_null($user->phone),
        ];

        $done = collect($checks)->filter(fn($c) => $c())->count();
        return (int) round(($done / count($checks)) * 100);
    }

    public function getMissingFields(User $user): array
    {
        $user->loadMissing(['profile', 'interests', 'company']);
        $p = $user->profile;
        $missing = [];

        if (is_null($p?->avatar))                                      $missing[] = ['key' => 'avatar',      'label' => 'Photo de profil', 'icon' => 'fa-camera'];
        if (is_null($p?->bio) && is_null($p?->motto))                  $missing[] = ['key' => 'bio',         'label' => 'Bio / Motto',      'icon' => 'fa-pen'];
        if (is_null($p?->job_title))                                    $missing[] = ['key' => 'job_title',   'label' => 'Poste',            'icon' => 'fa-briefcase'];
        if (is_null($p?->sector))                                       $missing[] = ['key' => 'sector',      'label' => 'Secteur',          'icon' => 'fa-industry'];
        if (is_null($p?->experience_level))                             $missing[] = ['key' => 'experience',  'label' => 'Expérience',        'icon' => 'fa-chart-line'];
        if (is_null($user->city_living))                                $missing[] = ['key' => 'location',    'label' => 'Localisation',     'icon' => 'fa-map-marker-alt'];
        if (is_null($user->company_id))                                 $missing[] = ['key' => 'company',     'label' => 'Entreprise',       'icon' => 'fa-building'];
        if ($user->interests->isEmpty())                                $missing[] = ['key' => 'interests',   'label' => 'Centres d\'intérêt', 'icon' => 'fa-star'];
        if (empty($p?->looking_for))                                    $missing[] = ['key' => 'looking_for', 'label' => 'Recherche',        'icon' => 'fa-search'];
        if (is_null($user->gender))                                     $missing[] = ['key' => 'gender',      'label' => 'Genre',            'icon' => 'fa-user'];
        if (is_null($user->phone))                                      $missing[] = ['key' => 'phone',       'label' => 'Téléphone',         'icon' => 'fa-phone'];

        return $missing;
    }
}
