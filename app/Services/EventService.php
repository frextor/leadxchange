<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Logique partagée des événements (design lx2) — utilisée par EventController et DashboardController.
 */
class EventService
{
    /**
     * Attache à chaque événement une relation `previewPeople` : jusqu'à $limit participants
     * (avec profil) pour la pile d'avatars des cartes. 2 requêtes au total, quel que soit
     * le nombre d'événements (pas de requête par carte).
     */
    public function attachPreviewAttendees(Collection $events, int $limit = 3): Collection
    {
        if ($events->isEmpty()) {
            return $events;
        }

        $rows = DB::table('event_user')
            ->whereIn('event_id', $events->pluck('id'))
            ->orderByRaw("CASE WHEN role = 'organizer' THEN 0 ELSE 1 END")
            ->orderBy('user_id')
            ->get(['event_id', 'user_id'])
            ->groupBy('event_id')
            ->map(fn($g) => $g->take($limit)->pluck('user_id'));

        $users = User::with('profile')
            ->whereIn('id', $rows->flatten()->unique())
            ->get(['id', 'first_name', 'last_name'])
            ->keyBy('id');

        return $events->each(fn($e) => $e->setRelation(
            'previewPeople',
            ($rows[$e->id] ?? collect())->map(fn($id) => $users[$id] ?? null)->filter()->values()
        ));
    }

    /** Même principe pour les groupes (table group_user). */
    public function attachPreviewMembers(Collection $groups, int $limit = 3): Collection
    {
        if ($groups->isEmpty()) {
            return $groups;
        }

        $rows = DB::table('group_user')
            ->whereIn('group_id', $groups->pluck('id'))
            ->whereNull('blocked_at')
            ->orderBy('user_id')
            ->get(['group_id', 'user_id'])
            ->groupBy('group_id')
            ->map(fn($g) => $g->take($limit)->pluck('user_id'));

        $users = User::with('profile')
            ->whereIn('id', $rows->flatten()->unique())
            ->get(['id', 'first_name', 'last_name'])
            ->keyBy('id');

        return $groups->each(fn($g) => $g->setRelation(
            'previewPeople',
            ($rows[$g->id] ?? collect())->map(fn($id) => $users[$id] ?? null)->filter()->values()
        ));
    }
}
