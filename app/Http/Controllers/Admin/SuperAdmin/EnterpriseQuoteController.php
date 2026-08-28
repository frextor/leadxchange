<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Jobs\SendQueuedEmailJob;
use App\Mail\SystemNotificationMail;
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
        $plans        = Plan::orderBy('price')->get();

        return view('admin.enterprise.quotes', compact('quotes', 'pendingCount', 'plans'));
    }

    /** Formulaire de proposition (modal pré-rempli) */
    public function proposalForm(EnterpriseQuoteRequest $quote)
    {
        $quote->load(['user', 'plan']);
        $plans = Plan::orderBy('price')->get();

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

        // ── Email ─────────────────────────────────────────────────────────────
        $proposalUrl   = route('enterprise.proposal.view', $token);
        $priceFormatted = number_format((float) $data['proposed_price'], 2, ',', ' ') . ' €';
        $planLabel      = $plan->label;
        $months         = $data['proposed_duration_months'];

        $body = "<p>Bonjour {$user->first_name},</p>
<p>Suite à votre demande, nous avons le plaisir de vous adresser notre proposition
de <strong>Pack Entreprise</strong> pour <strong>« {$quote->company_name} »</strong>.</p>
<table style='border-collapse:collapse;width:100%;max-width:480px;margin:20px 0;'>
  <tr style='background:#F8FAFC;'><td style='padding:10px 14px;font-size:13px;color:#374151;border:1px solid #E5E7EB;'><strong>Plan</strong></td><td style='padding:10px 14px;font-size:13px;color:#111827;border:1px solid #E5E7EB;'>{$planLabel}</td></tr>
  <tr><td style='padding:10px 14px;font-size:13px;color:#374151;border:1px solid #E5E7EB;'><strong>Licences</strong></td><td style='padding:10px 14px;font-size:13px;color:#111827;border:1px solid #E5E7EB;'>{$data['proposed_seats']} utilisateurs</td></tr>
  <tr style='background:#F8FAFC;'><td style='padding:10px 14px;font-size:13px;color:#374151;border:1px solid #E5E7EB;'><strong>Durée</strong></td><td style='padding:10px 14px;font-size:13px;color:#111827;border:1px solid #E5E7EB;'>{$months} mois</td></tr>
  <tr><td style='padding:10px 14px;font-size:13px;color:#374151;border:1px solid #E5E7EB;'><strong>Prix total</strong></td><td style='padding:10px 14px;font-size:14px;font-weight:700;color:#6366F1;border:1px solid #E5E7EB;'>{$priceFormatted}</td></tr>
</table>"
. ($data['proposal_message'] ? "<p><strong>Message de notre équipe :</strong><br>" . nl2br(htmlspecialchars($data['proposal_message'])) . "</p>" : '')
. "<p>Consultez votre proposition détaillée et procédez au paiement en toute sécurité via le lien ci-dessous.</p>";

        try {
            $recipientName = trim("{$user->first_name} {$user->last_name}");
            SendQueuedEmailJob::dispatch(
                to:       $user->email,
                subject:  "Votre proposition Pack Entreprise — {$quote->company_name}",
                type:     'enterprise_proposal',
                mailable: new SystemNotificationMail(
                    recipientName: $recipientName,
                    title:         'Votre proposition Pack Entreprise',
                    body:          $body,
                    actionLabel:   'Voir la proposition et payer',
                    actionUrl:     $proposalUrl,
                ),
                toName:   $recipientName,
            );
        } catch (\Exception $e) {
            Log::warning('Enterprise proposal email failed', ['quote_id' => $quote->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('admin.super.enterprise.quotes')
            ->with('success', "Proposition envoyée à {$user->email}.");
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
                $body = "<p>Bonjour {$user->first_name},</p>
<p>Votre demande de <strong>Pack Entreprise</strong> pour <strong>« {$company} »</strong>
a été <strong>acceptée</strong>. Notre équipe va vous contacter très prochainement.</p>";
                $this->sendEmail($user->email, $recipientName, 'Votre demande de pack Entreprise a été acceptée', $body);

            } elseif ($newStatus === 'closed') {
                Notification::storeForUser(
                    $user,
                    'enterprise_quote_rejected',
                    'Demande Pack Entreprise non retenue',
                    "Votre demande de pack Entreprise pour « {$company} » n'a pas pu être retenue. Contactez-nous pour plus d'informations.",
                    ['url' => route('dashboard')],
                );
                $body = "<p>Bonjour {$user->first_name},</p>
<p>Nous n'avons pas pu retenir votre demande de <strong>Pack Entreprise</strong>
pour <strong>« {$company} »</strong>.</p>"
. ($request->admin_notes ? "<p><strong>Remarque :</strong> " . nl2br(htmlspecialchars($request->admin_notes)) . "</p>" : '')
. "<p>N'hésitez pas à nous contacter pour plus d'informations.</p>";
                $this->sendEmail($user->email, $recipientName, 'Votre demande de pack Entreprise', $body);
            }
        }

        return back()->with('success', 'Demande mise à jour.');
    }

    private function sendEmail(string $to, string $name, string $title, string $body): void
    {
        try {
            SendQueuedEmailJob::dispatch(
                to:       $to,
                subject:  $title . ' — LeadXchange',
                type:     'enterprise_quote_update',
                mailable: new SystemNotificationMail(
                    recipientName: $name,
                    title:         $title,
                    body:          $body,
                    actionLabel:   'Accéder à mon espace',
                    actionUrl:     route('dashboard'),
                ),
                toName: $name,
            );
        } catch (\Exception $e) {
            Log::warning('EnterpriseQuote email failed', ['to' => $to, 'error' => $e->getMessage()]);
        }
    }
}
