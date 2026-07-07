<?php

namespace App\Services;

use App\Models\AmbassadorObjective;
use App\Models\AmbassadorProfile;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AmbassadorService
{
    // ── Region members ────────────────────────────────────────────────────────

    public function regionMembersQuery(User $ambassador)
    {
        $q = User::with(['profile', 'subscription.plan', 'city'])
            ->where('role', 'user')
            ->where('ambassador_status', '!=', 'approved'); // exclude other ambassadors

        if ($ambassador->region_id) {
            $q->where('region_id', $ambassador->region_id);
        } else {
            $q->where('city_id', $ambassador->city_id);
        }

        return $q;
    }

    public function regionMembersCount(User $ambassador): int
    {
        return $this->regionMembersQuery($ambassador)->count();
    }

    public function newMembersThisMonth(User $ambassador): int
    {
        return $this->regionMembersQuery($ambassador)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    // ── Events ────────────────────────────────────────────────────────────────

    public function ambassadorEventsQuery(User $ambassador)
    {
        return Event::where(function ($q) use ($ambassador) {
            $q->where('created_by', $ambassador->id);
            if ($ambassador->region_id) {
                $q->orWhere('region_id', $ambassador->region_id);
            }
        });
    }

    public function upcomingEventsCount(User $ambassador): int
    {
        return $this->ambassadorEventsQuery($ambassador)
            ->where('starts_at', '>', now())
            ->count();
    }

    public function totalEventsCount(User $ambassador): int
    {
        return $this->ambassadorEventsQuery($ambassador)->count();
    }

    // ── Leads ─────────────────────────────────────────────────────────────────

    public function leadsGeneratedCount(User $ambassador): int
    {
        return Lead::where('sender_id', $ambassador->id)->count();
    }

    public function leadsReceivedCount(User $ambassador): int
    {
        return Lead::where('receiver_id', $ambassador->id)->count();
    }

    public function leadsConversionRate(User $ambassador): float
    {
        $sent      = Lead::where('sender_id', $ambassador->id)->count();
        $converted = Lead::where('sender_id', $ambassador->id)->where('status', 'converted')->count();

        return $sent > 0 ? round($converted / $sent * 100, 1) : 0.0;
    }

    public function monthlyLeadsChart(User $ambassador, int $months = 6): array
    {
        $labels   = [];
        $generated = [];
        $accepted  = [];
        $rejected  = [];
        $fr        = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

        for ($i = $months - 1; $i >= 0; $i--) {
            $d       = now()->subMonths($i);
            $labels[] = $fr[$d->month - 1];

            $base = Lead::where('sender_id', $ambassador->id)
                ->whereYear('created_at', $d->year)
                ->whereMonth('created_at', $d->month);

            $generated[] = (clone $base)->count();
            $accepted[]  = (clone $base)->where('status', 'accepted')->count();
            $rejected[]  = (clone $base)->where('status', 'rejected')->count();
        }

        return compact('labels', 'generated', 'accepted', 'rejected');
    }

    // ── Invitations ───────────────────────────────────────────────────────────

    public function invitationsSentCount(User $ambassador): int
    {
        return EventInvitation::where('invited_by', $ambassador->id)->count();
    }

    public function invitationsAcceptedCount(User $ambassador): int
    {
        return EventInvitation::where('invited_by', $ambassador->id)->where('status', 'accepted')->count();
    }

    public function invitationsPendingCount(User $ambassador): int
    {
        return EventInvitation::where('invited_by', $ambassador->id)->where('status', 'pending')->count();
    }

    // ── Score + Ranking ───────────────────────────────────────────────────────

    public function computeScore(User $ambassador): int
    {
        $members    = $this->regionMembersCount($ambassador);
        $events     = $this->totalEventsCount($ambassador);
        $leads      = $this->leadsGeneratedCount($ambassador);
        $invAccepted = $this->invitationsAcceptedCount($ambassador);

        return ($members * 2) + ($events * 10) + ($leads * 5) + ($invAccepted * 2);
    }

    public function nationalRank(User $ambassador): int
    {
        $score = $this->computeScore($ambassador);

        $higherCount = User::where('ambassador_status', 'approved')
            ->where('id', '!=', $ambassador->id)
            ->get()
            ->filter(fn ($a) => $this->computeScore($a) > $score)
            ->count();

        return $higherCount + 1;
    }

    public function topAmbassadors(int $limit = 5): array
    {
        return User::with(['profile', 'ambassadorProfile', 'city'])
            ->where('ambassador_status', 'approved')
            ->get()
            ->map(fn ($a) => [
                'user'  => $a,
                'score' => $this->computeScore($a),
            ])
            ->sortByDesc('score')
            ->take($limit)
            ->values()
            ->toArray();
    }

    // ── Objectives ────────────────────────────────────────────────────────────

    public function currentObjective(User $ambassador): AmbassadorObjective
    {
        return AmbassadorObjective::firstOrCreate(
            [
                'ambassador_id' => $ambassador->id,
                'month'         => now()->month,
                'year'          => now()->year,
            ],
            [
                'target_members' => 30,
                'target_events'  => 5,
                'target_leads'   => 100,
            ]
        );
    }

    public function currentProgress(User $ambassador): array
    {
        $obj = $this->currentObjective($ambassador);

        $membersThisMonth = $this->newMembersThisMonth($ambassador);
        $eventsThisMonth  = $this->ambassadorEventsQuery($ambassador)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $leadsThisMonth   = Lead::where('sender_id', $ambassador->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            'members' => ['current' => $membersThisMonth, 'target' => $obj->target_members],
            'events'  => ['current' => $eventsThisMonth,  'target' => $obj->target_events],
            'leads'   => ['current' => $leadsThisMonth,   'target' => $obj->target_leads],
        ];
    }

    // ── Profile + Achievements ────────────────────────────────────────────────

    public function syncProfile(User $ambassador): AmbassadorProfile
    {
        $score   = $this->computeScore($ambassador);
        $natRank = $this->nationalRank($ambassador);

        $profile = AmbassadorProfile::updateOrCreate(
            ['user_id' => $ambassador->id],
            ['score' => $score, 'national_rank' => $natRank]
        );

        // Unlock achievements based on score
        $earned = [];
        foreach (AmbassadorProfile::ALL_ACHIEVEMENTS as $a) {
            if ($score >= $a['threshold']) {
                $earned[] = $a['key'];
            }
        }
        $profile->update(['achievements' => $earned]);

        return $profile->fresh();
    }

    // ── Dashboard KPIs ────────────────────────────────────────────────────────

    public function dashboardKpis(User $ambassador): array
    {
        return [
            'members_total'       => $this->regionMembersCount($ambassador),
            'members_this_month'  => $this->newMembersThisMonth($ambassador),
            'events_total'        => $this->totalEventsCount($ambassador),
            'events_upcoming'     => $this->upcomingEventsCount($ambassador),
            'leads_generated'     => $this->leadsGeneratedCount($ambassador),
            'leads_received'      => $this->leadsReceivedCount($ambassador),
            'invitations_sent'    => $this->invitationsSentCount($ambassador),
            'invitations_accepted'=> $this->invitationsAcceptedCount($ambassador),
            'score'               => $this->computeScore($ambassador),
            'national_rank'       => $this->nationalRank($ambassador),
        ];
    }
}
