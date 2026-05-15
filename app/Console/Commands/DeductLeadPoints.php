<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;

class DeductLeadPoints extends Command
{
    protected $signature   = 'leads:deduct-points';
    protected $description = 'Deduct 1 point from receivers who have not rated their lead within 15 days';

    public function handle(): int
    {
        $cutoff = now()->subDays(15);

        // Leads that are accepted or converted, older than 15 days,
        // have no rating, and haven't had the deduction applied yet.
        $leads = Lead::with('receiver')
            ->where('status', '!=', Lead::STATUS_REJECTED)
            ->where('created_at', '<=', $cutoff)
            ->where('points_deducted', false)
            ->whereDoesntHave('ratings')
            ->get();

        $count = 0;
        foreach ($leads as $lead) {
            $lead->update(['points_deducted' => true]);

            if ($lead->receiver) {
                $lead->receiver->adjustPoints(-1);
                $count++;
            }
        }

        $this->info("Deducted 1 point from {$count} receiver(s).");

        return self::SUCCESS;
    }
}
