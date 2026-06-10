<?php

return [
    'enabled' => env('PROFILE_VIDEO_ENABLED', true),

    'requires_approval' => env('PROFILE_VIDEO_REQUIRES_APPROVAL', true),

    'max_duration_seconds' => (int) env('PROFILE_VIDEO_MAX_DURATION_SECONDS', 60),

    'max_size_mb' => (int) env('PROFILE_VIDEO_MAX_SIZE_MB', 100),

    'ffmpeg_binary' => env('FFMPEG_BINARY', 'ffmpeg'),

    'ffprobe_binary' => env('FFPROBE_BINARY', 'ffprobe'),
];
