<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Connection;
use App\Models\EnterpriseQuoteRequest;
use App\Models\Event;
use App\Models\Group;
use App\Models\Lead;
use App\Models\Plan;
use App\Models\PointsHistory;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\EventService;
use App\Services\LeadService;
use App\Services\ProfileService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private ProfileService $profileService,
        private LeadService    $leadService,
        private EventService   $eventService,
    ) {}

    public function index(Request $request)
    {
        if (! $request->user()->onboarding_completed) {
            return redirect()->route('onboarding.show');
        }

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

            } elseif ($popupCriteria === 'same_region') {
                // Même région (region_id) que l'utilisateur connecté
                if ($user->region_id) {
                    $prospects = User::with(['profile', 'company', 'city'])
                        ->where('role', 'user')
                        ->where('id', '!=', $user->id)
                        ->whereNotIn('id', $connectedIds->toArray())
                        ->where('region_id', $user->region_id)
                        ->inRandomOrder()
                        ->limit($popupCount)
                        ->get();
                }

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

        // ── Suggestions de contacts (section de la page) ─────────────────────
        // Indépendantes du popup de bienvenue : toujours calculées, même si le popup est désactivé.
        // On évite les membres avec qui une demande est déjà en cours (dans un sens ou l'autre).
        $pendingConnections = Connection::where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
        })->pending()->get();
        $pendingSentIds     = $pendingConnections->where('sender_id', $user->id)->pluck('receiver_id')->values();
        $pendingReceivedIds = $pendingConnections->where('receiver_id', $user->id)->pluck('sender_id')->values();
        $pendingWithIds     = $pendingSentIds->merge($pendingReceivedIds);

        $suggestions = $baseQuery()
            ->inRandomOrder()
            ->limit(6)
            ->get();

        $suggestionsCityWidened = false;
        if ($suggestions->isEmpty() && $selectedCityId) {
            // Aucun membre dans la ville choisie : on élargit à toutes les villes
            $suggestionsCityWidened = true;
            $suggestions = User::with(['profile', 'company', 'city'])
                ->where('role', 'user')
                ->where('id', '!=', $user->id)
                ->whereNotIn('id', $connectedIds->merge($pendingWithIds)->toArray())
                ->inRandomOrder()
                ->limit(6)
                ->get();
        }

        // Note moyenne reçue par chaque membre suggéré (moyenne des notes des leads qu'il a envoyés)
        $suggestionRatings = $this->leadService->averageRatingsForSenders($suggestions->pluck('id'));

        // Points gagnés (cumul des crédits de points, hors débits)
        $pointsEarned = (int) PointsHistory::where('user_id', $user->id)->where('delta', '>', 0)->sum('delta');

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

        // 3 avatars par carte (pile d'avatars de la maquette) — 2 requêtes pour toutes les cartes
        $this->eventService->attachPreviewAttendees($upcomingEvents);
        $this->eventService->attachPreviewMembers($featuredGroups);

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

        // ── Proposition Enterprise en attente ────────────────────────────────
        $pendingEnterpriseProposal = EnterpriseQuoteRequest::where('user_id', $user->id)
            ->where('status', 'proposed')
            ->whereNull('proposal_accepted_at')
            ->latest('proposal_sent_at')
            ->first();

        // ── Mon activité : séries quotidiennes (lecture seule, 90 jours, découpées côté vue) ──
        $since90 = now()->subDays(89)->startOfDay();
        $sentByDay = Lead::where('sender_id', $user->id)->where('created_at', '>=', $since90)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')->groupBy('d')->pluck('c', 'd');
        $receivedByDay = Lead::where('receiver_id', $user->id)->where('created_at', '>=', $since90)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')->groupBy('d')->pluck('c', 'd');
        $convertedByDay = Lead::where('sender_id', $user->id)->where('status', Lead::STATUS_CONVERTED)
            ->where('converted_at', '>=', $since90)
            ->selectRaw('DATE(converted_at) as d, COUNT(*) as c')->groupBy('d')->pluck('c', 'd');

        $buildSeries = function (int $days) use ($sentByDay, $receivedByDay, $convertedByDay) {
            $series = ['labels' => [], 'sent' => [], 'received' => [], 'converted' => []];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $key  = $date->format('Y-m-d');
                $series['labels'][]    = $date->format('d/m');
                $series['sent'][]      = (int) ($sentByDay[$key] ?? 0);
                $series['received'][]  = (int) ($receivedByDay[$key] ?? 0);
                $series['converted'][] = (int) ($convertedByDay[$key] ?? 0);
            }
            return $series;
        };
        $activity = ['7' => $buildSeries(7), '30' => $buildSeries(30), '90' => $buildSeries(90)];

        // ── Tendances du mois (mois en cours vs mois précédent) ──
        $thisMonth = now()->startOfMonth();
        $prevStart = now()->subMonthNoOverflow()->startOfMonth();
        $prevEnd   = $thisMonth->copy()->subSecond();
        $trend = fn (int $now, int $prev) => [
            'now' => $now,
            'pct' => $prev > 0 ? (int) round(($now - $prev) / $prev * 100) : null,
        ];
        $kpiTrends = [
            'sent'      => $trend(
                Lead::where('sender_id', $user->id)->where('created_at', '>=', $thisMonth)->count(),
                Lead::where('sender_id', $user->id)->whereBetween('created_at', [$prevStart, $prevEnd])->count(),
            ),
            'converted' => $trend(
                Lead::where('sender_id', $user->id)->where('status', Lead::STATUS_CONVERTED)->where('converted_at', '>=', $thisMonth)->count(),
                Lead::where('sender_id', $user->id)->where('status', Lead::STATUS_CONVERTED)->whereBetween('converted_at', [$prevStart, $prevEnd])->count(),
            ),
            'points'    => $trend(
                (int) PointsHistory::where('user_id', $user->id)->where('delta', '>', 0)->where('created_at', '>=', $thisMonth)->sum('delta'),
                (int) PointsHistory::where('user_id', $user->id)->where('delta', '>', 0)->whereBetween('created_at', [$prevStart, $prevEnd])->sum('delta'),
            ),
        ];

        // ── À faire : uniquement les actions issues de données existantes ──
        $todoItems = collect();
        if ($completion < 100) {
            $todoItems->push(['icon' => 'user', 'title' => 'Compléter mon profil', 'count' => null, 'meta' => $completion . ' %', 'url' => route('profile.me')]);
        }
        if (($leadStats['pending'] ?? 0) > 0) {
            $todoItems->push(['icon' => 'inbox', 'title' => 'Traiter les nouveaux leads', 'count' => $leadStats['pending'], 'meta' => null, 'url' => route('leads.index', ['box' => 'received'])]);
        }
        if ($pendingCount > 0) {
            $todoItems->push(['icon' => 'user-plus', 'title' => 'Répondre aux demandes de connexion', 'count' => $pendingCount, 'meta' => null, 'url' => route('connections.index')]);
        }

        // Opportunités : aucune source de données n'existe encore. Structure prête, liste vide.
        $opportunities = collect();

        return view('dashboard', compact(
            'activity', 'kpiTrends', 'todoItems', 'opportunities',
            'user', 'connectionCount', 'pendingCount', 'groupCount',
            'completion', 'missing', 'prospects', 'plans',
            'featuredGroups', 'memberGroupIds',
            'upcomingEvents', 'attendingEventIds',
            'leadStats', 'pendingLeads', 'suggestions', 'suggestionRatings', 'suggestionsCityWidened', 'pendingSentIds', 'pendingReceivedIds', 'pointsEarned',
            'popupEnabled', 'popupFrequency',
            'popupTitle', 'popupSubtitle', 'popupBtnLater', 'popupBtnCta',
            'negativeBalancePopup', 'pointsNeeded', 'pointsPricePerUnit',
            'cities', 'selectedCityId', 'selectedCity',
            'pendingEnterpriseProposal'
        ));
    }
}
