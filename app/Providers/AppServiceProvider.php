<?php

namespace App\Providers;

use App\Models\ConsulRequest;
use App\Models\Group;
use App\Models\Lead;
use App\Models\LeadRating;
use App\Observers\LeadObserver;
use App\Observers\LeadRatingObserver;
use App\Policies\ConsulRequestPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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

        // Inject consul groups into all consul layout views
        View::composer('consul.layouts.consul', function ($view) {
            $user = auth()->user();
            if ($user?->isConsul()) {
                $groups = Group::whereHas('members', fn($q) =>
                        $q->where('users.id', $user->id)->whereIn('group_user.role', ['owner', 'admin']))
                    ->withCount('members')
                    ->orderBy('name')
                    ->get();
                $view->with('__consulGroups', $groups);
            }
        });
    }
}
