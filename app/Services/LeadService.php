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
            throw new \Exception('You cannot send a lead to yourself.');
        }

        $receiver = User::findOrFail($receiverId);

        // Receiver must be a connection
        if (!$sender->isConnectedWith($receiverId)) {
            throw new \Exception('You can only send leads to your connections.');
        }

        // Receiver must have a positive balance (ratio enforcement)
        if (($receiver->points_balance ?? 0) < 1) {
            throw new \Exception('This member cannot receive leads right now. They need to send a lead first to build their balance.');
        }

        DB::beginTransaction();
        try {
            $lead = Lead::create([
                'sender_id'        => $sender->id,
                'receiver_id'      => $receiverId,
                'company_name'     => $data['company_name'],
                'contact_name'     => $data['contact_name'],
                'contact_email'    => $data['contact_email'] ?? null,
                'contact_phone'    => $data['contact_phone'] ?? null,
                'contact_position' => $data['contact_position'] ?? null,
                'deadline'         => $data['deadline'],
                'qualification'    => $data['qualification'],
                'description'      => $data['description'] ?? null,
                'status'           => Lead::STATUS_NEW,
            ]);

            // +1 point for the sender immediately
            $sender->adjustPoints(+1);

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

        return $lead->load(['sender', 'receiver']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Accept a lead
    // ─────────────────────────────────────────────────────────────────────────

    public function acceptLead(User $user, int $leadId): Lead
    {
        $lead = Lead::findOrFail($leadId);

        if ($lead->receiver_id !== $user->id) {
            throw new \Exception('Only the recipient can accept this lead.');
        }

        if (!$lead->isNew()) {
            throw new \Exception('This lead has already been actioned.');
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
            throw new \Exception('Only the recipient can reject this lead.');
        }

        if (!$lead->isNew()) {
            throw new \Exception('This lead has already been actioned.');
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
            throw new \Exception('Only the sender can mark a lead as converted.');
        }

        if (!$lead->isAccepted()) {
            throw new \Exception('Only accepted leads can be converted.');
        }

        $lead->update(['status' => Lead::STATUS_CONVERTED]);

        Log::info('Lead converted', ['lead_id' => $lead->id, 'user' => $user->id]);

        return $lead->fresh(['sender', 'receiver']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Rate a lead (receiver rates the quality of the lead they received)
    // ─────────────────────────────────────────────────────────────────────────

    public function rateLead(User $rater, int $leadId, int $quality, int $relevance, int $reactivity): LeadRating
    {
        $lead = Lead::with('sender')->findOrFail($leadId);

        if ($lead->receiver_id !== $rater->id) {
            throw new \Exception('Only the recipient can rate this lead.');
        }

        if (!$lead->isAccepted() && !$lead->isConverted()) {
            throw new \Exception('You can only rate accepted leads.');
        }

        if ($lead->hasRatingBy($rater->id)) {
            throw new \Exception('You have already rated this lead.');
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

            // +1 bonus for sender if avg >= 4
            $avg = ($quality + $relevance + $reactivity) / 3.0;
            if ($avg >= 4.0 && $lead->rated_bonus_at === null) {
                $lead->update(['rated_bonus_at' => now()]);
                $lead->sender?->adjustPoints(+1);
            }

            // If the deduction hadn't fired yet (rated within 15 days), mark to skip cron
            if (!$lead->points_deducted) {
                $lead->update(['points_deducted' => true]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        Log::info('Lead rated', ['lead_id' => $lead->id, 'rater' => $rater->id, 'avg' => round($avg, 2)]);

        return $rating;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Read helpers
    // ─────────────────────────────────────────────────────────────────────────

    public function getUserLeads(User $user): array
    {
        $with = ['sender:id,first_name,last_name', 'receiver:id,first_name,last_name', 'ratings'];

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
