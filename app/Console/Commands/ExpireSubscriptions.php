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
        $query = Subscription::where('status', 'active')
            ->whereNotNull('current_period_end')
            ->where('current_period_end', '<', Carbon::now());

        // In test mode expire all active subscriptions past period end.
        // In production only expire those the user explicitly cancelled.
        if (!(int) config('services.stripe.test_subscription_minutes', 0)) {
            $query->where('cancel_at_period_end', true);
        }

        $count = $query->update(['status' => 'canceled']);

        $this->info("Expired {$count} subscription(s).");
    }
}
