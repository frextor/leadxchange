<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Jobs\SendQueuedEmailJob;
use App\Mail\SystemNotificationMail;
use App\Models\EnterpriseLicense;
use App\Models\EnterpriseQuoteRequest;
use App\Models\Notification;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class EnterpriseQuoteController extends Controller
{
    public function index()
    {
        $quotes = EnterpriseQuoteRequest::with(['user', 'plan'])
            ->orderByRaw("FIELD(status,'pending','contacted','proposed','converted','closed')")
            ->orderByDesc('created_at')
            ->get();

        $pendingCount = $quotes->whereIn('status', ['pending', 'contacted'])->count();
        $plans        = Plan::where('name', 'enterprise')->orderBy('price')->get();

        // ── Packs Entreprise actifs / expirés — pour l'onglet "Packs" ───────────
        $licenses = EnterpriseLicense::with(['holder', 'plan'])
            ->withCount(['invitations', 'activeInvitations'])
            ->with(['invitations' => function ($q) {
                $q->where('status', 'pending')->whereNotNull('email');
            }])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($license) {
                $license->pack_status       = $license->isExpired() ? 'expired' : 'active';
                $license->pending_members   = $license->invitations; // filtered to pending in the with() above
                return $license;
            });

        $activeLicensesCount  = $licenses->where('pack_status', 'active')->count();
        $expiredLicensesCount = $licenses->where('pack_status', 'expired')->count();

        return view('admin.enterprise.quotes', compact(
            'quotes', 'pendingCount', 'plans',
            'licenses', 'activeLicensesCount', 'expiredLicensesCount'
        ));
    }

    /** Formulaire de proposition (modal pré-rempli) */
    public function proposalForm(EnterpriseQuoteRequest $quote)
    {
        $quote->load(['user', 'plan']);
        $plans = Plan::where('name', 'enterprise')->orderBy('price')->get();

        return view('admin.enterprise.proposal_form', compact('quote', 'plans'));
    }

    /** Envoyer la proposition au client */
    public function sendProposal(Request $request, EnterpriseQuoteRequest $quote)
    {
        $data = $request->validate([
            'proposed_seats'           => ['required', 'integer', 'min:1', 'max:500'],
            'plan_id'                  => ['required', 'exists:plans,id'],
            'proposed_price'           => ['required', 'numeric', 'min:0'],
            'proposed_duration_months' => ['required', 'integer', 'min:1', 'max:60'],
            'proposal_message'         => ['nullable', 'string', 'max:2000'],
        ]);

        $quote->load('user');
        $plan  = Plan::findOrFail($data['plan_id']);
        $user  = $quote->user;
        $token = EnterpriseQuoteRequest::generateToken();

        // ── Créer un Stripe Price one-off ────────────────────────────────────
        $stripePaymentLink = null;
        $stripePriceId     = null;

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));

            // Créer un Price one-time pour ce montant exact
            $price = $stripe->prices->create([
                'currency'    => 'eur',
                'unit_amount' => (int) round((float) $data['proposed_price'] * 100),
                'product_data'=> [
                    'name' => "Pack Entreprise — {$quote->company_name} ({$data['proposed_seats']} licences, {$data['proposed_duration_months']} mois)",
                ],
            ]);

            // Créer un Payment Link Stripe
            $link = $stripe->paymentLinks->create([
                'line_items' => [['price' => $price->id, 'quantity' => 1]],
                'metadata'   => [
                    'quote_id' => (string) $quote->id,
                    'user_id'  => (string) $user->id,
                    'token'    => $token,
                ],
                'after_completion' => [
                    'type'     => 'redirect',
                    'redirect' => ['url' => route('enterprise.proposal.paid', $token)],
                ],
            ]);

            $stripePaymentLink = $link->url;
            $stripePriceId     = $price->id;

        } catch (\Exception $e) {
            Log::warning('Stripe payment link creation failed', ['quote_id' => $quote->id, 'error' => $e->getMessage()]);
        }

        // ── Sauvegarder la proposition ────────────────────────────────────────
        $quote->update([
            'status'                   => 'proposed',
            'proposed_seats'           => $data['proposed_seats'],
            'plan_id'                  => $data['plan_id'],
            'proposed_price'           => $data['proposed_price'],
            'proposed_duration_months' => $data['proposed_duration_months'],
            'proposal_message'         => $data['proposal_message'],
            'stripe_payment_link'      => $stripePaymentLink,
            'stripe_price_id'          => $stripePriceId,
            'proposal_token'           => $token,
            'proposal_sent_at'         => now(),
        ]);

        // ── Notification in-app ───────────────────────────────────────────────
        $proposalUrl = route('enterprise.proposal.view', $token);
        Notification::storeForUser(
            $user,
            'enterprise_proposal_received',
            'Proposition Pack Entreprise reçue',
            "Vous avez reçu une proposition pour « {$quote->company_name} ». Consultez-la et acceptez-la en ligne.",
            ['url' => $proposalUrl],
        );

        // ── Email via template ────────────────────────────────────────────────
        $priceFormatted = number_format((float) $data['proposed_price'], 2, ',', ' ') . ' €';
        $proposalMsgBlock = $data['proposal_message']
            ? '<div class="info-card"><p style="font-size:14px;color:#0B6B5A;margin:0;"><strong>Message de notre équipe :</strong><br>' . nl2br(htmlspecialchars($data['proposal_message'])) . '</p></div>'
            : '';

        try {
            $recipientName = trim("{$user->first_name} {$user->last_name}");
            SendQueuedEmailJob::dispatch(
                to:       $user->email,
                subject:  "Votre proposition Pack Entreprise — {$quote->company_name}",
                type:     'enterprise_proposal',
                mailable: new SystemNotificationMail(
                    recipientName: $recipientName,
                    title:         'Votre proposition Pack Entreprise',
                    body:          '',
                    actionLabel:   'Voir la proposition et payer',
                    actionUrl:     $proposalUrl,
                    templateKey:   'enterprise_proposal',
                    extraVars:     [
                        'company_name'          => $quote->company_name,
                        'plan_label'            => $plan->label,
                        'seats'                 => (string) $data['proposed_seats'],
                        'duration_months'       => (string) $data['proposed_duration_months'],
                        'price'                 => $priceFormatted,
                        'proposal_message_block'=> $proposalMsgBlock,
                        'proposal_url'          => $proposalUrl,
                    ],
                ),
                toName:   $recipientName,
            );
        } catch (\Exception $e) {
            Log::warning('Enterprise proposal email failed', ['quote_id' => $quote->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('admin.super.enterprise.quotes')
            ->with('success', "Proposition envoyée à {$user->email}.");
    }

    /** Régénère le Payment Link Stripe pour une proposition déjà envoyée sans lien */
    public function regeneratePaymentLink(EnterpriseQuoteRequest $quote)
    {
        abort_unless($quote->status === 'proposed' && !$quote->proposal_accepted_at, 403);

        $stripePaymentLink = null;
        $stripePriceId     = null;

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));

            $price = $stripe->prices->create([
                'currency'     => 'eur',
                'unit_amount'  => (int) round((float) $quote->proposed_price * 100),
                'product_data' => [
                    'name' => "Pack Entreprise — {$quote->company_name} ({$quote->proposed_seats} licences, {$quote->proposed_duration_months} mois)",
                ],
            ]);

            $link = $stripe->paymentLinks->create([
                'line_items' => [['price' => $price->id, 'quantity' => 1]],
                'metadata'   => [
                    'quote_id' => (string) $quote->id,
                    'user_id'  => (string) $quote->user_id,
                    'token'    => $quote->proposal_token,
                ],
                'after_completion' => [
                    'type'     => 'redirect',
                    'redirect' => ['url' => route('enterprise.proposal.paid', $quote->proposal_token)],
                ],
            ]);

            $stripePaymentLink = $link->url;
            $stripePriceId     = $price->id;

        } catch (\Exception $e) {
            Log::error('Stripe link regeneration failed', ['quote_id' => $quote->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Erreur Stripe : ' . $e->getMessage());
        }

        $quote->update([
            'stripe_payment_link' => $stripePaymentLink,
            'stripe_price_id'     => $stripePriceId,
        ]);

        return back()->with('success', 'Lien de paiement Stripe régénéré. Le client peut maintenant payer.');
    }

    public function update(Request $request, EnterpriseQuoteRequest $quote)
    {
        $request->validate([
            'status'      => ['required', 'in:pending,contacted,proposed,converted,closed'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $quote->status;
        $newStatus = $request->status;

        $quote->update([
            'status'      => $newStatus,
            'admin_notes' => $request->admin_notes,
        ]);

        if ($oldStatus !== $newStatus && $quote->user) {
            $user        = $quote->user;
            $company     = $quote->company_name;
            $recipientName = trim("{$user->first_name} {$user->last_name}") ?: $user->email;

            if ($newStatus === 'converted') {
                Notification::storeForUser(
                    $user,
                    'enterprise_quote_accepted',
                    'Demande Pack Entreprise acceptée',
                    "Bonne nouvelle ! Votre demande pour « {$company} » a été acceptée. Notre équipe va vous contacter prochainement.",
                    ['url' => route('dashboard')],
                );
                $this->sendEmail(
                    to:          $user->email,
                    name:        $recipientName,
                    subject:     'Votre demande Pack Entreprise a été acceptée — LeadXchange',
                    templateKey: 'enterprise_quote_accepted',
                    extraVars:   [
                        'company_name'  => $company,
                        'dashboard_url' => route('dashboard'),
                    ],
                );

            } elseif ($newStatus === 'closed') {
                Notification::storeForUser(
                    $user,
                    'enterprise_quote_rejected',
                    'Demande Pack Entreprise non retenue',
                    "Votre demande de pack Entreprise pour « {$company} » n'a pas pu être retenue. Contactez-nous pour plus d'informations.",
                    ['url' => route('dashboard')],
                );
                $notesBlock = $request->admin_notes
                    ? '<div class="info-card" style="background:#FEF3C7;border-color:#FDE68A;"><p style="font-size:14px;color:#92400E;margin:0;"><strong>Remarque :</strong> ' . nl2br(htmlspecialchars($request->admin_notes)) . '</p></div>'
                    : '';
                $this->sendEmail(
                    to:          $user->email,
                    name:        $recipientName,
                    subject:     'Votre demande Pack Entreprise — LeadXchange',
                    templateKey: 'enterprise_quote_rejected',
                    extraVars:   [
                        'company_name'      => $company,
                        'admin_notes_block' => $notesBlock,
                        'dashboard_url'     => route('dashboard'),
                    ],
                );
            }
        }

        return back()->with('success', 'Demande mise à jour.');
    }

    private function sendEmail(
        string $to,
        string $name,
        string $subject,
        string $templateKey,
        array  $extraVars = [],
    ): void {
        try {
            SendQueuedEmailJob::dispatch(
                to:       $to,
                subject:  $subject,
                type:     'enterprise_quote_update',
                mailable: new SystemNotificationMail(
                    recipientName: $name,
                    title:         $subject,
                    body:          '',
                    actionLabel:   'Accéder à mon espace',
                    actionUrl:     route('dashboard'),
                    templateKey:   $templateKey,
                    extraVars:     $extraVars,
                ),
                toName: $name,
            );
        } catch (\Exception $e) {
            Log::warning('EnterpriseQuote email failed', ['to' => $to, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Admin approuve le virement bancaire et active le Pack Entreprise.
     */
    public function approveWireTransfer(EnterpriseQuoteRequest $quote)
    {
        abort_unless($quote->status === 'contacted' && !$quote->proposal_accepted_at, 403, 'Ce pack ne peut pas être approuvé par virement.');

        $quote->load('user');

        // Marquer comme converti
        $quote->update([
            'status'               => 'converted',
            'proposal_accepted_at' => now(),
        ]);

        // Activer la licence via EnterpriseProposalController
        app(\App\Http\Controllers\EnterpriseProposalController::class)->activateLicensePublic($quote);

        // Notifier le client par email
        try {
            $user = $quote->user;
            $name = $user->first_name . ' ' . $user->last_name;
            SendQueuedEmailJob::dispatch(
                to:       $user->email,
                subject:  "Votre Pack Entreprise « {$quote->company_name} » est activé — LeadXchange",
                type:     'enterprise_quote_accepted',
                mailable: new SystemNotificationMail(
                    recipientName: $name,
                    title:         "Votre Pack Entreprise est activé !",
                    body:          '',
                    actionLabel:   'Accéder à mon espace entreprise',
                    actionUrl:     route('enterprise.team'),
                    templateKey:   'enterprise_quote_accepted',
                    extraVars:     ['name' => $name, 'company_name' => $quote->company_name, 'dashboard_url' => route('enterprise.team')],
                ),
                toName: $name,
            );
        } catch (\Exception $e) {
            Log::warning('EnterpriseQuote approve-wire email failed', ['error' => $e->getMessage()]);
        }

        return redirect()->route('admin.super.enterprise.quotes')
            ->with('success', "Pack Entreprise « {$quote->company_name} » activé après virement bancaire.");
    }
}
