<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CloseExpiredLeadRatings extends Command
{
    protected $signature   = 'leads:close-expired-ratings';
    protected $description = 'Log leads whose 30-day rating window has expired (CCTP: notation obligatoire 30j max)';

    public function handle(): int
    {
        $cutoff = now()->subDays(30);

        $leads = Lead::with('receiver')
            ->whereIn('status', [Lead::STATUS_ACCEPTED, Lead::STATUS_CONVERTED])
            ->where('created_at', '<=', $cutoff)
            ->whereDoesntHave('ratings')
            ->get();

        foreach ($leads as $lead) {
            Log::info('leads:close-expired-ratings — rating window expired', [
                'lead_id'     => $lead->id,
                'receiver_id' => $lead->receiver_id,
                'created_at'  => $lead->created_at->toDateString(),
            ]);
        }

        $this->info("Found {$leads->count()} lead(s) with expired rating window.");

        return self::SUCCESS;
    }
}
