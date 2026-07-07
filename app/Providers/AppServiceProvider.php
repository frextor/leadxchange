<?php

namespace App\Providers;

use App\Models\ConsulRequest;
use App\Models\Lead;
use App\Models\LeadRating;
use App\Observers\LeadObserver;
use App\Observers\LeadRatingObserver;
use App\Policies\ConsulRequestPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Lead::observe(LeadObserver::class);
        LeadRating::observe(LeadRatingObserver::class);

        Gate::policy(ConsulRequest::class, ConsulRequestPolicy::class);
    }
}
