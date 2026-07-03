<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Services\PointsService;
use Illuminate\Console\Command;

class ProcessExpiredLeadRatings extends Command
{
    protected $signature   = 'leads:process-expired-ratings';
    protected $description = 'CGU §6.2.3 — Revert points for leads not rated within 15 days';

    public function handle(PointsService $points): int
    {
        $expired = Lead::with(['sender', 'receiver'])
            ->whereNotNull('rating_due_at')
            ->where('rating_due_at', '<', now())
            ->whereNull('rated_bonus_at')
            ->where('expiry_processed', false)
            ->whereIn('status', ['accepted', 'converted'])
            ->get();

        $this->info("Processing {$expired->count()} expired lead(s)…");

        foreach ($expired as $lead) {
            try {
                $points->processExpiredLead($lead);
                $this->line("  ✓ Lead #{$lead->id} — sender={$lead->sender?->first_name} receiver={$lead->receiver?->first_name}");
            } catch (\Throwable $e) {
                $this->error("  ✗ Lead #{$lead->id}: {$e->getMessage()}");
            }
        }

        $this->info('Done.');
        return 0;
    }
}
