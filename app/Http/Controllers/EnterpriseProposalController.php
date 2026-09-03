<?php

namespace App\Http\Controllers;

use App\Models\EnterpriseLicense;
use App\Models\EnterpriseInvitation;
use App\Models\EnterpriseQuoteRequest;
use App\Models\Subscription;
use App\Models\SystemSetting;
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
            ->whereIn('status', ['proposed', 'contacted', 'converted'])
            ->firstOrFail();

        // Seul le propriétaire peut la voir
        if (auth()->id() !== $quote->user_id) {
            abort(403, 'Cette proposition ne vous appartient pas.');
        }

        // Si déjà activée, rediriger vers l'espace entreprise
        if ($quote->proposal_accepted_at && $quote->status === 'converted') {
            return redirect()->route('enterprise.team')
                ->with('success', 'Votre Pack Entreprise est déjà actif.');
        }

        $bankTransferDetails = SystemSetting::where('key', 'bank_transfer.details')->first()?->value ?? '';

        return view('enterprise.proposal', compact('quote', 'bankTransferDetails'));
    }

    /** Retour Stripe après paiement réussi */
    public function paid(string $token)
    {
        $quote = EnterpriseQuoteRequest::with(['user', 'plan'])
            ->where('proposal_token', $token)
            ->firstOrFail();

        // Vérifier que l'utilisateur connecté est bien le propriétaire de la proposition
        // (au cas où un admin clique sur le lien de paiement en étant connecté)
        $currentUser = auth()->user();
        if ($currentUser && $currentUser->id !== $quote->user_id) {
            // Rediriger vers le dashboard de l'utilisateur courant, pas entreprise.team
            return redirect()->route('dashboard')
                ->with('error', 'Ce lien de paiement ne correspond pas à votre compte.');
        }

        // Déjà activé (webhook a battu le redirect) → vérifier que la licence existe
        if ($quote->proposal_accepted_at) {
            $license = $quote->user->enterpriseLicense()->first();
            if ($license && !$license->isExpired()) {
                return redirect()->route('enterprise.team')
                    ->with('success', 'Votre licence Entreprise est déjà activée. Bienvenue !');
            }
            // Licence pas encore créée (webhook en cours) → réessayer l'activation
            $this->activateLicense($quote);
        } else {
            // Marquer comme accepté
            $quote->update([
                'status'               => 'converted',
                'proposal_accepted_at' => now(),
            ]);

            // Créer la licence Enterprise
            $this->activateLicense($quote);
        }

        // Vérifier que la licence a bien été créée avant de rediriger
        $quote->user->refresh();
        $license = $quote->user->enterpriseLicense()->first();

        if ($license && !$license->isExpired()) {
            return redirect()->route('enterprise.team')
                ->with('success', "Félicitations ! Votre Pack Entreprise « {$quote->company_name} » est maintenant actif.");
        }

        // Fallback : activation échouée, rediriger vers dashboard avec message
        return redirect()->route('dashboard')
            ->with('success', "Paiement reçu ! Votre licence Entreprise « {$quote->company_name} » est en cours d'activation. Vous serez notifié dans quelques instants.");
    }

    /**
     * Accepter la proposition par virement bancaire.
     * Le pack est marqué "en attente de virement" — l'admin valide manuellement après réception.
     */
    public function acceptWireTransfer(Request $request, string $token)
    {
        $quote = EnterpriseQuoteRequest::with(['user', 'plan'])
            ->where('proposal_token', $token)
            ->whereIn('status', ['proposed'])
            ->firstOrFail();

        if (auth()->id() !== $quote->user_id) {
            abort(403, 'Cette proposition ne vous appartient pas.');
        }

        // Marquer comme "contacté / en attente de virement"
        $quote->update([
            'status'                => 'contacted',
            'proposal_accepted_at'  => null, // pas encore payé
        ]);

        return redirect()->route('enterprise.proposal.view', $token)
            ->with('wire_transfer_requested', true);
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
