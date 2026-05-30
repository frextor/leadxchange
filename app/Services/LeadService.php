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
            throw new \Exception('Vous ne pouvez envoyer des leads qu\'à vos connexions.');
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

            $sender->adjustPoints(+1, 'lead_sent');

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
            $lead->update(['status' => Lead::STATUS_ACCEPTED]);
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

        if ($lead->sender_id !== $user->id) {
            throw new \Exception('Seul l\'expéditeur peut marquer un lead comme converti.');
        }

        if (!$lead->isAccepted()) {
            throw new \Exception('Seuls les leads acceptés peuvent être convertis.');
        }

        $lead->update(['status' => Lead::STATUS_CONVERTED]);

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

            // Additional -1 point penalty for the sender (CDC: "Lead déclaré frauduleux: −1 supplémentaire")
            $lead->sender?->adjustPoints(-1, 'lead_fraud_penalty');

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
    // Read helpers
    // ─────────────────────────────────────────────────────────────────────────

    public function getUserLeads(User $user): array
    {
        $with = ['sender:id,first_name,last_name', 'receiver:id,first_name,last_name', 'ratings', 'sector:id,name'];

        $received = Lead::with($with)->where('receiver_id', $user->id)->latest()->get();
        $sent     = Lead::with($with)->where('sender_id',   $user->id)->latest()->get();

        return compact('received', 'sent');
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
