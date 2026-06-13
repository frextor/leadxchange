<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Services\SmtpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendQueuedEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60; // seconds between retries

    public function __construct(
        private readonly string   $to,
        private readonly string   $subject,
        private readonly string   $type,
        private readonly Mailable $mailable,
        private readonly ?string  $toName    = null,
        private readonly int      $logId     = 0,
        private readonly array    $metadata  = [],
    ) {}

    public function handle(SmtpService $smtp): void
    {
        // Ensure the dynamic SMTP config is applied in queue workers too
        $smtp->applyToMailConfig();

        $log = $this->logId ? EmailLog::find($this->logId) : null;

        try {
            Mail::to($this->to)->send($this->mailable);

            $log?->update(['status' => 'sent', 'sent_at' => now()]);

            Log::info('Queued email sent', [
                'to'      => $this->to,
                'subject' => $this->subject,
                'type'    => $this->type,
                'log_id'  => $this->logId,
            ]);
        } catch (\Exception $e) {
            $log?->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Queued email failed', [
                'to'      => $this->to,
                'subject' => $this->subject,
                'type'    => $this->type,
                'error'   => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            $this->fail($e);
        }
    }

    /**
     * Helper: dispatch a queued email and create the log entry immediately.
     */
    public static function dispatch(
        string   $to,
        string   $subject,
        string   $type,
        Mailable $mailable,
        ?string  $toName   = null,
        array    $metadata = [],
    ): EmailLog {
        $log = EmailLog::create([
            'recipient_email' => $to,
            'recipient_name'  => $toName,
            'subject'         => $subject,
            'type'            => $type,
            'status'          => 'pending',
            'metadata'        => $metadata,
            'mailer'          => 'smtp',
        ]);

        static::dispatchToQueue(
            to: $to,
            subject: $subject,
            type: $type,
            mailable: $mailable,
            toName: $toName,
            logId: $log->id,
            metadata: $metadata,
        );

        return $log;
    }

    private static function dispatchToQueue(...$args): void
    {
        // Use Laravel's bus to dispatch the job
        dispatch(new self(...$args));
    }
}
