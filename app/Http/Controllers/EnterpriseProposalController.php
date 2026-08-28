<?php

namespace App\Http\Controllers;

use App\Models\EnterpriseLicense;
use App\Models\EnterpriseInvitation;
use App\Models\EnterpriseQuoteRequest;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class EnterpriseProposalController extends Controller
{
    /** Page de proposition visible par le client */
    public function view(string $token)
    {
        $quote = EnterpriseQuoteRequest::with(['user', 'plan'])
            ->where('proposal_token', $token)
            ->where('status', 'proposed')
            ->firstOrFail();

        // Seul le propriétaire peut la voir
        if (auth()->id() !== $quote->user_id) {
            abort(403, 'Cette proposition ne vous appartient pas.');
        }

        return view('enterprise.proposal', compact('quote'));
    }

    /** Retour Stripe après paiement réussi */
    public function paid(string $token)
    {
        $quote = EnterpriseQuoteRequest::with(['user', 'plan'])
            ->where('proposal_token', $token)
            ->firstOrFail();

        if ($quote->proposal_accepted_at) {
            return redirect()->route('enterprise.team')
                ->with('success', 'Votre licence Enterprise est déjà activée.');
        }

        // Marquer comme accepté
        $quote->update([
            'status'                => 'converted',
            'proposal_accepted_at'  => now(),
        ]);

        // Créer la licence Enterprise
        $this->activateLicense($quote);

        return redirect()->route('enterprise.team')
            ->with('success', "Félicitations ! Votre Pack Entreprise « {$quote->company_name} » est maintenant actif.");
    }

    public function activateLicensePublic(EnterpriseQuoteRequest $quote): void
    {
        $this->activateLicense($quote);
    }

    private function activateLicense(EnterpriseQuoteRequest $quote): void
    {
        try {
            $user    = $quote->user;
            $expiresAt = now()->addMonths($quote->proposed_duration_months);

            // Créer ou remplacer la licence
            $license = EnterpriseLicense::updateOrCreate(
                ['holder_user_id' => $user->id],
                [
                    'company_name' => $quote->company_name,
                    'plan_id'      => $quote->plan_id,
                    'seats_total'  => $quote->proposed_seats,
                    'seats_used'   => 1,
                    'expires_at'   => $expiresAt,
                ]
            );

            // Créer les slots d'invitations disponibles
            $existing = $license->invitations()->count();
            $toCreate = max(0, $quote->proposed_seats - $existing - 1); // -1 pour le holder

            for ($i = 0; $i < $toCreate; $i++) {
                EnterpriseInvitation::create([
                    'license_id' => $license->id,
                    'status'     => EnterpriseInvitation::STATUS_AVAILABLE,
                    'token'      => EnterpriseInvitation::generateToken(),
                ]);
            }

            // Activer l'abonnement du holder
            Subscription::where('user_id', $user->id)->update(['status' => 'canceled']);
            Subscription::create([
                'user_id'            => $user->id,
                'plan_id'            => $quote->plan_id,
                'status'             => 'active',
                'current_period_end' => $expiresAt,
            ]);

        } catch (\Exception $e) {
            Log::error('Enterprise license activation failed', [
                'quote_id' => $quote->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
