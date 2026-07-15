<?php

namespace App\Http\Controllers;

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

        $baseQuery = fn() => User::with(['profile', 'company', 'city'])
            ->where('role', 'user')
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $connectedIds->toArray());

        $prospects = collect();

        if ($popupEnabled) {
            $userInterestIds = $user->interests->pluck('id')->toArray();

            if ($popupCriteria === 'same_city' && $user->city_id) {
                $prospects = $baseQuery()
                    ->where('city_id', $user->city_id)
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
                // Priorité : ville + intérêt commun
                if ($user->city_id && !empty($userInterestIds)) {
                    $prospects = $baseQuery()
                        ->where('city_id', $user->city_id)
                        ->whereHas('interests', fn($q) => $q->whereIn('interests.id', $userInterestIds))
                        ->inRandomOrder()
                        ->limit($popupCount)
                        ->get();
                }
                // Complète si pas assez de résultats
                if ($prospects->count() < $popupCount && $user->city_id) {
                    $already = $prospects->pluck('id')->toArray();
                    $extra = $baseQuery()
                        ->where('city_id', $user->city_id)
                        ->whereNotIn('id', $already)
                        ->inRandomOrder()
                        ->limit($popupCount - $prospects->count())
                        ->get();
                    $prospects = $prospects->merge($extra);
                }
                // Dernier recours : aléatoire
                if ($prospects->count() < $popupCount) {
                    $already = $prospects->pluck('id')->toArray();
                    $extra = $baseQuery()
                        ->whereNotIn('id', $already)
                        ->inRandomOrder()
                        ->limit($popupCount - $prospects->count())
                        ->get();
                    $prospects = $prospects->merge($extra);
                }

            } else {
                // none ou fallback
                $prospects = $baseQuery()
                    ->inRandomOrder()
                    ->limit($popupCount)
                    ->get();
            }

            // Si critère filtré mais aucun résultat, fallback aléatoire
            if ($prospects->isEmpty() && $popupCriteria !== 'none') {
                $prospects = $baseQuery()->inRandomOrder()->limit($popupCount)->get();
            }
        }

        $plans = Plan::orderBy('price')->get();

        $memberGroupIds = $user->groups()->pluck('groups.id')->toArray();
        $featuredGroups = Group::with(['sector:id,name'])
            ->withCount('members')
            ->where('is_public', true)
            ->orderBy('members_count', 'desc')
            ->limit(3)
            ->get();

        $upcomingEvents    = Event::with(['sector:id,name'])
            ->where('is_public', true)
            ->where('starts_at', '>=', now())
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

        return view('dashboard', compact(
            'user', 'connectionCount', 'pendingCount', 'groupCount',
            'completion', 'missing', 'prospects', 'plans',
            'featuredGroups', 'memberGroupIds',
            'upcomingEvents', 'attendingEventIds',
            'leadStats', 'pendingLeads',
            'popupEnabled', 'popupFrequency',
            'popupTitle', 'popupSubtitle', 'popupBtnLater', 'popupBtnCta'
        ));
    }
}
