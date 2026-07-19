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
        $testMode = (bool) \App\Models\SystemSetting::get('payments.subscription_test_mode');

        $query = Subscription::where('status', 'active')
            ->whereNotNull('current_period_end')
            ->where('current_period_end', '<', Carbon::now());

        if (!$testMode) {
            // Production: only expire subscriptions the user explicitly cancelled
            $query->where('cancel_at_period_end', true);
        }
        // Test mode: expire all active subscriptions past their (short) period end

        $count = $query->update(['status' => 'canceled']);

        $this->info("Expired {$count} subscription(s)." . ($testMode ? ' [TEST MODE]' : ''));
    }
}
