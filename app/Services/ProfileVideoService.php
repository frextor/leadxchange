<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class ProfileVideoService
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function store(User $user, UploadedFile $file): void
    {
        if (!config('profile_video.enabled')) {
            throw ValidationException::withMessages([
                'presentation_video' => 'Presentation video uploads are disabled.',
            ]);
        }

        $this->ensureBinary(config('profile_video.ffprobe_binary'), 'ffprobe');
        $this->ensureBinary(config('profile_video.ffmpeg_binary'), 'ffmpeg');

        $maxDuration = (int) config('profile_video.max_duration_seconds', 300);
        $duration = $this->durationInSeconds($file->getRealPath());

        if ($duration > $maxDuration) {
            throw ValidationException::withMessages([
                'presentation_video' => 'Presentation video must not be longer than 1 minute.',
            ]);
        }

        $profile = $user->profile ?? Profile::create(['user_id' => $user->id]);
        $oldPath = $profile->presentation_video;

        $directory = 'presentation-videos';
        $filename = $user->id . '_' . now()->format('YmdHis') . '.mp4';
        $relativePath = $directory . '/' . $filename;
        $absoluteDirectory = Storage::disk('public')->path($directory);
        $absolutePath = Storage::disk('public')->path($relativePath);

        File::ensureDirectoryExists($absoluteDirectory);

        $process = new Process([
            config('profile_video.ffmpeg_binary'),
            '-y',
            '-i',
            $file->getRealPath(),
            '-c:v',
            'libx264',
            '-preset',
            'medium',
            '-crf',
            '28',
            '-c:a',
            'aac',
            '-b:a',
            '128k',
            '-movflags',
            '+faststart',
            $absolutePath,
        ]);
        $process->setTimeout(600);
        $process->run();

        if (!$process->isSuccessful() || !File::exists($absolutePath)) {
            Storage::disk('public')->delete($relativePath);
            throw ValidationException::withMessages([
                'presentation_video' => 'Presentation video could not be compressed.',
            ]);
        }

        $maxBytes = (int) config('profile_video.max_size_mb', 100) * 1024 * 1024;
        if (File::size($absolutePath) > $maxBytes) {
            Storage::disk('public')->delete($relativePath);
            throw ValidationException::withMessages([
                'presentation_video' => 'Compressed presentation video exceeds the maximum allowed size.',
            ]);
        }

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $profile->update([
            'presentation_video' => $relativePath,
            'presentation_video_status' => config('profile_video.requires_approval')
                ? self::STATUS_PENDING
                : self::STATUS_APPROVED,
            'presentation_video_rejection_reason' => null,
            'presentation_video_uploaded_at' => now(),
            'presentation_video_reviewed_at' => config('profile_video.requires_approval') ? null : now(),
            'presentation_video_reviewed_by' => null,
        ]);
    }

    public function storePendingUpload(User $user, UploadedFile $file): void
    {
        if (!config('profile_video.enabled')) {
            throw ValidationException::withMessages([
                'presentation_video' => 'Presentation video uploads are disabled.',
            ]);
        }

        $this->ensureBinary(config('profile_video.ffprobe_binary'), 'ffprobe');

        $maxDuration = (int) config('profile_video.max_duration_seconds', 300);
        $duration = $this->durationInSeconds($file->getRealPath());

        if ($duration > $maxDuration) {
            throw ValidationException::withMessages([
                'presentation_video' => 'Presentation video must not be longer than 1 minute.',
            ]);
        }

        $profile = $user->profile ?? Profile::create(['user_id' => $user->id]);
        $oldPath = $profile->presentation_video;

        $extension = $file->extension() ?: 'mp4';
        $filename = $user->id . '_' . now()->format('YmdHis') . '.' . $extension;
        $relativePath = $file->storeAs('presentation-videos', $filename, 'public');

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $profile->update([
            'presentation_video' => $relativePath,
            'presentation_video_status' => config('profile_video.requires_approval')
                ? self::STATUS_PENDING
                : self::STATUS_APPROVED,
            'presentation_video_rejection_reason' => null,
            'presentation_video_uploaded_at' => now(),
            'presentation_video_reviewed_at' => config('profile_video.requires_approval') ? null : now(),
            'presentation_video_reviewed_by' => null,
        ]);
    }

    public function approve(Profile $profile, User $admin): void
    {
        $profile->update([
            'presentation_video_status' => self::STATUS_APPROVED,
            'presentation_video_rejection_reason' => null,
            'presentation_video_reviewed_at' => now(),
            'presentation_video_reviewed_by' => $admin->id,
        ]);
    }

    public function reject(Profile $profile, User $admin, ?string $reason = null): void
    {
        $profile->update([
            'presentation_video_status' => self::STATUS_REJECTED,
            'presentation_video_rejection_reason' => $reason,
            'presentation_video_reviewed_at' => now(),
            'presentation_video_reviewed_by' => $admin->id,
        ]);
    }

    private function durationInSeconds(string $path): float
    {
        $process = new Process([
            config('profile_video.ffprobe_binary'),
            '-v',
            'error',
            '-show_entries',
            'format=duration',
            '-of',
            'default=noprint_wrappers=1:nokey=1',
            $path,
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw ValidationException::withMessages([
                'presentation_video' => 'Presentation video duration could not be verified.',
            ]);
        }

        return (float) trim($process->getOutput());
    }

    private function ensureBinary(string $binary, string $label): void
    {
        $process = new Process([$binary, '-version']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw ValidationException::withMessages([
                'presentation_video' => "{$label} is required to process presentation videos.",
            ]);
        }
    }
}
