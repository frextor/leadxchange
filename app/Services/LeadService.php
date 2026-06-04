<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadRating;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadService
{
    public function __construct(private FirebaseService $firebase) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Create & send a lead
    // ─────────────────────────────────────────────────────────────────────────

    public function createLead(User $sender, array $data): Lead
    {
        $receiverId = (int) $data['receiver_id'];

        if ($sender->id === $receiverId) {
            throw new \Exception('Vous ne pouvez pas vous envoyer un lead à vous-même.');
        }

        $receiver = User::findOrFail($receiverId);

        if (!$sender->isConnectedWith($receiverId)) {
            throw new \Exception("Vous ne pouvez envoyer des leads qu'à vos connexions.");
        }

        if (($sender->points_balance ?? 0) < 1) {
            throw new \Exception('Votre solde est insuffisant pour envoyer un lead (minimum 1 point requis).');
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
            $lead->load('sender');
            $lead->sender?->adjustPoints(+1, 'lead_accepted');
            $lead->receiver->adjustPoints(-1, 'lead_received');
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

    public function rateLead(User $rater, int $leadId, int $quality, int $relevance, int $reactivity): LeadRating
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

        if ($lead->created_at->lt(now()->subDays(30))) {
            throw new \Exception('La fenêtre de notation de 30 jours est expirée pour ce lead.');
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

            $avg = ($quality + $relevance + $reactivity) / 3.0;

            if ($avg >= 4.0 && $lead->rated_bonus_at === null) {
                $lead->update(['rated_bonus_at' => now()]);
                $lead->sender?->adjustPoints(+1, 'lead_bonus_note');
            }

            if (!$lead->points_deducted) {
                $lead->update(['points_deducted' => true]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        Log::info('Lead rated', ['lead_id' => $lead->id, 'rater' => $rater->id, 'avg' => round($avg, 2)]);

        // Warn sender after every 5 bad notes (avg ≤ 2)
        if ($avg <= 2.0 && $lead->sender) {
            $badCount = LeadRating::whereHas('lead', fn($q) => $q->where('sender_id', $lead->sender_id))
                ->where('average_note', '<=', 2)
                ->count();

            if ($badCount > 0 && $badCount % 5 === 0) {
                try {
                    $this->firebase->sendBadNoteWarning($lead->sender, $badCount);
                } catch (\Exception $e) {
                    Log::warning('Bad note warning notification failed', ['error' => $e->getMessage()]);
                }
            }
        }

        return $rating;
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

        $allowedReasons = ['faux_profil', 'lead_frauduleux', 'spam', 'comportement_inapproprie', 'autre'];
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

        $newReceiver = User::findOrFail($newReceiverId);

        if (!$sender->isConnectedWith($newReceiverId)) {
            throw new \Exception("Vous ne pouvez transférer des leads qu'à vos connexions.");
        }

        if (($newReceiver->points_balance ?? 0) < 1) {
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
