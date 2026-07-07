<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadRating;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadService
{
    public function __construct(
        private FirebaseService $firebase,
        private PointsService   $points,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Create & send a lead
    // ─────────────────────────────────────────────────────────────────────────

    public function createLead(User $sender, array $data): Lead
    {
        $receiverId = (int) $data['receiver_id'];

        if ($sender->id === $receiverId) {
            throw new \Exception('Vous ne pouvez pas vous envoyer un lead à vous-même.');
        }

        $receiver = User::with('subscription.plan')->findOrFail($receiverId);

        if (!$sender->isConnectedWith($receiverId)) {
            throw new \Exception("Vous ne pouvez envoyer des leads qu'à vos connexions.");
        }

        $receiverIsPremium = ($receiver->subscription?->status === 'active')
            && (float) ($receiver->subscription?->plan?->price ?? 0) > 0;

        if (!$receiverIsPremium && ($receiver->points_balance ?? 0) < 1) {
            throw new \Exception('Ce membre ne peut pas recevoir de leads pour le moment. Son solde est insuffisant.');
        }

        DB::beginTransaction();
        try {
            $lead = Lead::create([
                'sender_id'        => $sender->id,
                'receiver_id'      => $receiverId,
                'company_name'     => $data['company_name'],
                'contact_name'     => $data['contact_name'],
                'contact_email'    => $data['contact_email'],
                'contact_phone'    => $data['contact_phone'],
                'contact_position' => $data['contact_position'] ?? null,
                'deadline'         => $data['deadline'],
                'qualification'    => $data['qualification'],
                'sector_id'        => $data['sector_id'] ?? null,
                'description'      => $data['description'] ?? null,
                'status'           => Lead::STATUS_NEW,
            ]);

            // Points are granted when the receiver accepts, not on send

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create lead', ['error' => $e->getMessage()]);
            throw $e;
        }

        try {
            $this->firebase->sendLeadNotification($lead, $sender, 'sent');
        } catch (\Exception $e) {
            Log::warning('Firebase lead notification failed', ['error' => $e->getMessage()]);
        }

        Log::info('Lead created', ['lead_id' => $lead->id, 'sender' => $sender->id, 'receiver' => $receiverId]);

        return $lead->load(['sender', 'receiver', 'sector']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Accept a lead
    // ─────────────────────────────────────────────────────────────────────────

    public function acceptLead(User $user, int $leadId): Lead
    {
        $lead = Lead::findOrFail($leadId);

        if ($lead->receiver_id !== $user->id) {
            throw new \Exception('Seul le destinataire peut accepter ce lead.');
        }

        if (!$lead->isNew()) {
            throw new \Exception('Ce lead a déjà été traité.');
        }

        DB::beginTransaction();
        try {
            $lead->update(['status' => Lead::STATUS_ACCEPTED, 'points_deducted' => true]);
            $lead->load('sender', 'receiver');
            // CGU §6.2.1 + §6.3.1 : sender +2, receiver -1
            $this->points->onLeadAccepted($lead);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        try {
            $this->firebase->sendLeadNotification($lead->fresh(), $user, 'accepted');
        } catch (\Exception $e) {
            Log::warning('Firebase lead notification failed', ['error' => $e->getMessage()]);
        }

        Log::info('Lead accepted', ['lead_id' => $lead->id, 'user' => $user->id]);

        return $lead->fresh(['sender', 'receiver']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Reject a lead
    // ─────────────────────────────────────────────────────────────────────────

    public function rejectLead(User $user, int $leadId): Lead
    {
        $lead = Lead::findOrFail($leadId);

        if ($lead->receiver_id !== $user->id) {
            throw new \Exception('Seul le destinataire peut refuser ce lead.');
        }

        if (!$lead->isNew()) {
            throw new \Exception('Ce lead a déjà été traité.');
        }

        DB::beginTransaction();
        try {
            $lead->update(['status' => Lead::STATUS_REJECTED]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        try {
            $this->firebase->sendLeadNotification($lead->fresh(), $user, 'rejected');
        } catch (\Exception $e) {
            Log::warning('Firebase lead notification failed', ['error' => $e->getMessage()]);
        }

        Log::info('Lead rejected', ['lead_id' => $lead->id, 'user' => $user->id]);

        return $lead->fresh(['sender', 'receiver']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Mark as converted
    // ─────────────────────────────────────────────────────────────────────────

    public function convertLead(User $user, int $leadId): Lead
    {
        $lead = Lead::findOrFail($leadId);

        if ($lead->receiver_id !== $user->id) {
            throw new \Exception("Seul le destinataire peut marquer un lead comme converti.");
        }

        if (!$lead->isAccepted()) {
            throw new \Exception('Seuls les leads acceptés peuvent être convertis.');
        }

        DB::beginTransaction();
        try {
            $lead->update(['status' => Lead::STATUS_CONVERTED]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        Log::info('Lead converted', ['lead_id' => $lead->id, 'user' => $user->id]);

        return $lead->fresh(['sender', 'receiver']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Rate a lead
    // ─────────────────────────────────────────────────────────────────────────

    public function rateLead(User $rater, int $leadId, int $quality, int $relevance, int $reactivity, string $leadType): LeadRating
    {
        $lead = Lead::with('sender')->findOrFail($leadId);

        if ($lead->receiver_id !== $rater->id) {
            throw new \Exception('Seul le destinataire peut noter ce lead.');
        }

        if (!$lead->isAccepted() && !$lead->isConverted()) {
            throw new \Exception('Vous ne pouvez noter que les leads acceptés.');
        }

        if ($lead->hasRatingBy($rater->id)) {
            throw new \Exception('Vous avez déjà noté ce lead.');
        }

        $ratingDeadline = $lead->deadline
            ? \Carbon\Carbon::parse($lead->deadline)->addDays(30)
            : $lead->created_at->addDays(60);
        if (now()->gt($ratingDeadline)) {
            throw new \Exception('Le délai de notation de ce lead est dépassé.');
        }

        DB::beginTransaction();
        try {
            $rating = LeadRating::create([
                'lead_id'    => $lead->id,
                'rater_id'   => $rater->id,
                'quality'    => $quality,
                'relevance'  => $relevance,
                'reactivity' => $reactivity,
            ]);

            $lead->update(['lead_type' => $leadType, 'points_deducted' => true]);
            $lead->load('sender', 'receiver');

            // CGU §6.2.2 + §6.3.2 : bonus points based on lead_type
            $this->points->onLeadRated($lead, $leadType ?? '');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $avg = ($quality + $relevance + $reactivity) / 3.0;
        Log::info('Lead rated', [
            'lead_id'   => $lead->id,
            'rater'     => $rater->id,
            'lead_type' => $leadType,
            'bonus_pts' => \App\Services\PointsService::bonusFor($leadType),
            'avg'       => round($avg, 2),
        ]);

        return $rating;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Compute 60-day rolling rating score for a user
    // ─────────────────────────────────────────────────────────────────────────

    public function computeRatingScore(int $userId): array
    {
        $windowDays    = SystemSetting::get('scoring.window_days', 60);
        $givenMult     = SystemSetting::get('scoring.given_multiplier', 2);
        $receivedMult  = SystemSetting::get('scoring.received_multiplier', -1);
        $mqlWeight     = SystemSetting::get('scoring.mql_weight', 1);
        $sqlWeight     = SystemSetting::get('scoring.sql_weight', 3);
        $spWeight      = SystemSetting::get('scoring.sp_weight', 5);

        $since = now()->subDays($windowDays);

        $given = Lead::where('sender_id', $userId)
            ->whereIn('status', [Lead::STATUS_ACCEPTED, Lead::STATUS_CONVERTED])
            ->where('updated_at', '>=', $since)
            ->get(['lead_type']);

        $receivedCount = Lead::where('receiver_id', $userId)
            ->whereIn('status', [Lead::STATUS_ACCEPTED, Lead::STATUS_CONVERTED])
            ->where('updated_at', '>=', $since)
            ->count();

        $givenCount = $given->count();
        $mql = $given->where('lead_type', Lead::TYPE_MQL)->count();
        $sql = $given->where('lead_type', Lead::TYPE_SQL)->count();
        $sp  = $given->where('lead_type', Lead::TYPE_SP)->count();

        $score = ($givenCount * $givenMult) + ($receivedCount * $receivedMult) + ($mql * $mqlWeight) + ($sql * $sqlWeight) + ($sp * $spWeight);
        $score = max(0, $score);
        $stars = min(5, (int) floor($score / 5));

        return ['score' => $score, 'stars' => $stars];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Report a lead as fraudulent
    // ─────────────────────────────────────────────────────────────────────────

    public function reportFraud(User $reporter, int $leadId, string $reason): Lead
    {
        $lead = Lead::with('sender')->findOrFail($leadId);

        if ($lead->receiver_id !== $reporter->id) {
            throw new \Exception('Seul le destinataire peut signaler un lead comme frauduleux.');
        }

        if ($lead->isFraudReported()) {
            throw new \Exception('Ce lead a déjà été signalé.');
        }

        $reasonAliases = [
            'false_info' => 'fausses_coordonnees',
            'no_need'    => 'besoin_inexistant',
            'duplicate'  => 'doublon',
        ];
        $reason = $reasonAliases[$reason] ?? $reason;

        $allowedReasons = ['fausses_coordonnees', 'besoin_inexistant', 'doublon'];
        if (!in_array($reason, $allowedReasons)) {
            throw new \Exception('Motif invalide.');
        }

        DB::beginTransaction();
        try {
            $lead->update([
                'fraud_reported'    => true,
                'fraud_reason'      => $reason,
                'fraud_reported_at' => now(),
            ]);

            $lead->sender?->adjustPoints(-1, 'lead_fraud');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        Log::info('Lead reported as fraud', [
            'lead_id'  => $lead->id,
            'reporter' => $reporter->id,
            'reason'   => $reason,
        ]);

        return $lead->fresh(['sender', 'receiver']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Reschedule deadline (receiver only, after accept)
    // ─────────────────────────────────────────────────────────────────────────

    public function rescheduleDeadline(User $user, int $leadId, string $newDeadline): Lead
    {
        $lead = Lead::findOrFail($leadId);

        if ($lead->receiver_id !== $user->id) {
            throw new \Exception('Seul le destinataire peut modifier la date échéance.');
        }

        if (!$lead->isAccepted()) {
            throw new \Exception('La date échéance ne peut être modifiée que sur un lead accepté.');
        }

        $lead->update(['deadline' => $newDeadline]);

        Log::info('Lead deadline rescheduled', ['lead_id' => $lead->id, 'new_deadline' => $newDeadline]);

        return $lead->fresh(['sender', 'receiver', 'sector']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Transfer lead to another connection (sender only, when pending/rejected)
    // ─────────────────────────────────────────────────────────────────────────

    public function transferLead(User $sender, int $leadId, int $newReceiverId): Lead
    {
        $lead = Lead::findOrFail($leadId);

        if ($lead->sender_id !== $sender->id) {
            throw new \Exception("Seul l'expéditeur peut transférer ce lead.");
        }

        if (!$lead->isNew() && !$lead->isRejected()) {
            throw new \Exception('Seuls les leads en attente ou refusés peuvent être transférés.');
        }

        if ($lead->receiver_id === $newReceiverId) {
            throw new \Exception('Le nouveau destinataire est identique au destinataire actuel.');
        }

        $newReceiver = User::with('subscription.plan')->findOrFail($newReceiverId);

        if (!$sender->isConnectedWith($newReceiverId)) {
            throw new \Exception("Vous ne pouvez transférer des leads qu'à vos connexions.");
        }

        $newReceiverIsPremium = ($newReceiver->subscription?->status === 'active')
            && (float) ($newReceiver->subscription?->plan?->price ?? 0) > 0;

        if (!$newReceiverIsPremium && ($newReceiver->points_balance ?? 0) < 1) {
            throw new \Exception('Ce membre ne peut pas recevoir de leads pour le moment. Son solde est insuffisant.');
        }

        $lead->update([
            'receiver_id' => $newReceiverId,
            'status'      => Lead::STATUS_NEW,
        ]);

        try {
            $this->firebase->sendLeadNotification($lead->fresh(), $sender, 'sent');
        } catch (\Exception $e) {
            Log::warning('Firebase lead transfer notification failed', ['error' => $e->getMessage()]);
        }

        Log::info('Lead transferred', ['lead_id' => $lead->id, 'new_receiver' => $newReceiverId]);

        return $lead->fresh(['sender', 'receiver', 'sector']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Read helpers
    // ─────────────────────────────────────────────────────────────────────────

    public function getUserLeads(User $user): array
    {
        $with = ['sender:id,first_name,last_name', 'sender.profile:user_id,avatar', 'receiver:id,first_name,last_name', 'receiver.profile:user_id,avatar', 'ratings', 'sector:id,name'];

        return [
            'received' => Lead::with($with)->where('receiver_id', $user->id)->latest()->get(),
            'sent'     => Lead::with($with)->where('sender_id',   $user->id)->latest()->get(),
        ];
    }

    public function getUserLeadsPaginated(User $user, string $tab, ?string $status, ?string $qualification, ?int $sectorId, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $with  = ['sender:id,first_name,last_name', 'sender.profile:user_id,avatar', 'receiver:id,first_name,last_name', 'receiver.profile:user_id,avatar', 'ratings', 'sector:id,name'];
        $query = Lead::with($with);

        if ($tab === 'sent') {
            $query->where('sender_id', $user->id);
        } else {
            $query->where('receiver_id', $user->id);
        }

        if ($status)        $query->where('status', $status);
        if ($qualification) $query->where('qualification', $qualification);
        if ($sectorId)      $query->where('sector_id', $sectorId);

        return $query->latest()->paginate($perPage);
    }

    public function cancelLead(User $user, int $leadId): void
    {
        $lead = Lead::findOrFail($leadId);

        if ($lead->sender_id !== $user->id) {
            throw new \Exception("Seul l'expéditeur peut annuler ce lead.");
        }

        if (!$lead->isNew()) {
            throw new \Exception('Seuls les leads en attente peuvent être annulés.');
        }

        $lead->delete();

        Log::info('Lead cancelled', ['lead_id' => $leadId, 'user' => $user->id]);
    }

    public function getDashboardStats(User $user): array
    {
        return [
            'sent'      => Lead::where('sender_id',   $user->id)->count(),
            'received'  => Lead::where('receiver_id', $user->id)->count(),
            'pending'   => Lead::where('receiver_id', $user->id)->where('status', Lead::STATUS_NEW)->count(),
            'converted' => Lead::where('sender_id',   $user->id)->where('status', Lead::STATUS_CONVERTED)->count(),
        ];
    }
}
