<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;

class ExpireLeads extends Command
{
    protected $signature   = 'leads:expire';
    protected $description = 'Expire pending leads whose deadline has passed without a response';

    public function handle(): int
    {
        $count = Lead::where('status', Lead::STATUS_NEW)
            ->whereNotNull('deadline')
            ->where('deadline', '<', now())
            ->update(['status' => Lead::STATUS_EXPIRED]);

        $this->info("Expired {$count} lead(s).");

        return self::SUCCESS;
    }
}
