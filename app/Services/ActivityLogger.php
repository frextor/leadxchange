<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * Write one activity log entry.
     * Never throws — logging must never crash the application.
     */
    public static function log(
        string  $event,
        string  $description,
        ?int    $causerId    = null,
        ?Model  $subject     = null,
        array   $properties  = [],
    ): void {
        try {
            ActivityLog::create([
                'causer_id'    => $causerId ?? (auth()->id()),
                'event'        => $event,
                'description'  => $description,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id'   => $subject?->id,
                'properties'   => empty($properties) ? null : $properties,
                'ip_address'   => request()->ip(),
                'user_agent'   => substr(request()->userAgent() ?? '', 0, 300),
                'created_at'   => now(),
            ]);
        } catch (\Throwable) {
            // Silently swallow — logging failures must not break business logic
        }
    }
}
