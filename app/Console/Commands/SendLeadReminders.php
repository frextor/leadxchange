<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Services\FirebaseService;
use Illuminate\Console\Command;

class SendLeadReminders extends Command
{
    protected $signature   = 'leads:send-reminders';
    protected $description = 'Send J+15 and J+25 push reminders to receivers who have not rated their lead';

    public function __construct(private FirebaseService $firebase)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->sendReminders(15, 'reminder_15_sent_at');
        $this->sendReminders(25, 'reminder_25_sent_at');

        return self::SUCCESS;
    }

    private function sendReminders(int $days, string $sentAtColumn): void
    {
        $from = now()->subDays($days + 1);
        $to   = now()->subDays($days);

        $leads = Lead::with('receiver')
            ->whereIn('status', [Lead::STATUS_ACCEPTED, Lead::STATUS_CONVERTED])
            ->whereBetween('created_at', [$from, $to])
            ->whereNull($sentAtColumn)
            ->whereDoesntHave('ratings')
            ->get();

        $count = 0;
        foreach ($leads as $lead) {
            $lead->update([$sentAtColumn => now()]);

            try {
                $this->firebase->sendLeadReminderNotification($lead, $days);
                $count++;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Lead reminder J+{$days} failed", [
                    'lead_id' => $lead->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        $this->info("J+{$days} reminders sent to {$count} receiver(s).");
    }
}
