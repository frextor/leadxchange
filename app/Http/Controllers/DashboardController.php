<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Connection;
use App\Models\Event;
use App\Models\Group;
use App\Models\Lead;
use App\Models\Plan;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\LeadService;
use App\Services\ProfileService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private ProfileService $profileService,
        private LeadService    $leadService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user()->load(['profile', 'subscription.plan', 'interests', 'company']);

        $connectedIds = Connection::where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
        })->accepted()->get()->map(
            fn($c) => $c->sender_id === $user->id ? $c->receiver_id : $c->sender_id
        );

        $connectionCount = $connectedIds->count();
        $pendingCount    = Connection::where('receiver_id', $user->id)->pending()->count();
        $groupCount      = $user->groups()->count();

        $completion = $this->profileService->getCompletionPercentage($user);
        $missing    = $this->profileService->getMissingFields($user);

        // ── Popup de bienvenue : critères paramétrables ──────────────────────
        $popupEnabled   = SystemSetting::get('welcome_popup_enabled', true);
        $popupCriteria  = SystemSetting::get('welcome_popup_criteria', 'none');
        $popupCount     = (int) SystemSetting::get('welcome_popup_count', 3);
        $popupFrequency = SystemSetting::get('welcome_popup_frequency', 'once');
        $popupTitle     = SystemSetting::get('welcome_popup_title', '');
        $popupSubtitle  = SystemSetting::get('welcome_popup_subtitle', '');
        $popupBtnLater  = SystemSetting::get('welcome_popup_btn_later', '');
        $popupBtnCta    = SystemSetting::get('welcome_popup_btn_cta', '');

        // La ville active (sélecteur hero) est lue dès maintenant pour filtrer aussi les prospects
        $selectedCityId = session('selected_city_id', $user->city_id);

        $baseQuery = fn() => User::with(['profile', 'company', 'city'])
            ->where('role', 'user')
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $connectedIds->toArray())
            ->when($selectedCityId, fn($q) => $q->where('city_id', $selectedCityId));

        $prospects = collect();

        if ($popupEnabled) {
            $userInterestIds = $user->interests->pluck('id')->toArray();

            if ($popupCriteria === 'same_city') {
                $prospects = $baseQuery()
                    ->inRandomOrder()
                    ->limit($popupCount)
                    ->get();

            } elseif ($popupCriteria === 'same_interest' && !empty($userInterestIds)) {
                $prospects = $baseQuery()
                    ->whereHas('interests', fn($q) => $q->whereIn('interests.id', $userInterestIds))
                    ->inRandomOrder()
                    ->limit($popupCount)
                    ->get();

            } elseif ($popupCriteria === 'both') {
                // Priorité : intérêt commun (la ville est déjà dans baseQuery)
                if (!empty($userInterestIds)) {
                    $prospects = $baseQuery()
                        ->whereHas('interests', fn($q) => $q->whereIn('interests.id', $userInterestIds))
                        ->inRandomOrder()
                        ->limit($popupCount)
                        ->get();
                }
                // Complète si pas assez
                if ($prospects->count() < $popupCount) {
                    $already = $prospects->pluck('id')->toArray();
                    $prospects = $prospects->merge(
                        $baseQuery()->whereNotIn('id', $already)->inRandomOrder()
                            ->limit($popupCount - $prospects->count())->get()
                    );
                }

            } else {
                // none ou fallback
                $prospects = $baseQuery()
                    ->inRandomOrder()
                    ->limit($popupCount)
                    ->get();
            }

            // Fallback sans filtre ville si aucun résultat
            if ($prospects->isEmpty() && $popupCriteria !== 'none') {
                $prospects = User::with(['profile', 'company', 'city'])
                    ->where('role', 'user')
                    ->where('id', '!=', $user->id)
                    ->whereNotIn('id', $connectedIds->toArray())
                    ->inRandomOrder()->limit($popupCount)->get();
            }
        }

        $plans = Plan::orderBy('price')->get();

        // ── Sélecteur de région ──────────────────────────────────────────────
        $cities       = City::active()->orderBy('name')->get();
        $selectedCity = $selectedCityId ? $cities->firstWhere('id', $selectedCityId) : null;

        $memberGroupIds = $user->groups()->pluck('groups.id')->toArray();
        $featuredGroups = Group::with(['sector:id,name'])
            ->withCount('members')
            ->where('is_public', true)
            ->when($selectedCityId, fn($q) => $q->where('city_id', $selectedCityId))
            ->orderBy('members_count', 'desc')
            ->limit(3)
            ->get();

        $upcomingEvents = Event::with(['sector:id,name', 'city:id,name'])
            ->where('is_public', true)
            ->where('starts_at', '>=', now())
            ->when($selectedCityId, fn($q) => $q->where('city_id', $selectedCityId))
            ->orderBy('starts_at')
            ->limit(3)
            ->get();
        $attendingEventIds = $user->events()->pluck('events.id')->toArray();

        $leadStats         = $this->leadService->getDashboardStats($user);
        $pendingLeads      = Lead::with(['sender:id,first_name,last_name'])
            ->where('receiver_id', $user->id)
            ->where('status', Lead::STATUS_NEW)
            ->latest()
            ->limit(3)
            ->get();

        // ── Popup solde négatif depuis > 2 mois ─────────────────────────────
        $negativeBalancePopup = false;
        $pointsNeeded         = 0;
        $pointsPricePerUnit   = (float) SystemSetting::get('points.price_per_unit', 5.00);

        if (
            ($user->points_balance ?? 0) < 0 &&
            $user->points_negative_since &&
            $user->points_negative_since->lt(now()->subMonths(2))
        ) {
            $negativeBalancePopup = true;
            $pointsNeeded         = abs((int) $user->points_balance);
        }

        return view('dashboard', compact(
            'user', 'connectionCount', 'pendingCount', 'groupCount',
            'completion', 'missing', 'prospects', 'plans',
            'featuredGroups', 'memberGroupIds',
            'upcomingEvents', 'attendingEventIds',
            'leadStats', 'pendingLeads',
            'popupEnabled', 'popupFrequency',
            'popupTitle', 'popupSubtitle', 'popupBtnLater', 'popupBtnCta',
            'negativeBalancePopup', 'pointsNeeded', 'pointsPricePerUnit',
            'cities', 'selectedCityId', 'selectedCity'
        ));
    }
}
