<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Mark subscriptions as canceled when cancel_at_period_end date has passed';

    public function handle(): void
    {
        $count = Subscription::where('status', 'active')
            ->where('cancel_at_period_end', true)
            ->where('current_period_end', '<', Carbon::now())
            ->update(['status' => 'canceled']);

        $this->info("Expired {$count} subscription(s).");
    }
}
