<?php

use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (Plan::all() as $plan) {
            $features = is_array($plan->features) ? $plan->features : [];

            // create_groups: false for basic, true for others
            if (!array_key_exists('create_groups', $features)) {
                $features['create_groups'] = $plan->name === 'basic' ? false : true;
            }

            // attend_events: false for basic, true for others
            if (!array_key_exists('attend_events', $features)) {
                $features['attend_events'] = $plan->name === 'basic' ? false : true;
            }

            $plan->update(['features' => $features]);
        }
    }

    public function down(): void
    {
        foreach (Plan::all() as $plan) {
            $features = is_array($plan->features) ? $plan->features : [];
            unset($features['create_groups'], $features['attend_events']);
            $plan->update(['features' => $features]);
        }
    }
};
