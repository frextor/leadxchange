<?php

namespace App\Http\Controllers;

use App\Mail\SystemNotificationMail;
use App\Models\EnterpriseInvitation;
use App\Models\EnterpriseLicense;
use App\Models\EnterpriseQuoteRequest;
use App\Models\Lead;
use App\Models\Notification;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnterpriseController extends Controller
{
    // ── Team management (holder only) ─────────────────────────────────────────

    public function expired(Request $request)
    {
        $user    = $request->user();
        $license = $user->enterpriseLicense()->with('plan')->first();

        abort_unless($license, 403, 'Vous n\'avez pas de licence entreprise.');

        // If licence is still active, redirect to team page
        if (! $license->isExpired()) {
            return redirect()->route('enterprise.team');
        }

        return view('enterprise.expired', compact('license'));
    }

    /**
     * Tableau de bord — vue d'ensemble de l'espace Entreprise.
     */
    public function dashboard(Request $request)
    {
        $user    = $request->user();
        $license = $user->enterpriseLicense()->with('invitations.user')->first();

        abort_unless($license, 403, 'Vous n\'avez pas de licence entreprise.');

        if ($license->isExpired()) {
            return redirect()->route('enterprise.expired');
        }

        ['invitations' => $invitations, 'leaderboard' => $leaderboard, 'analytics' => $analytics]
            = $this->buildTeamAnalytics($license, $user);

        return view('enterprise.dashboard', compact('license', 'invitations', 'leaderboard', 'analytics'));
    }

    public function team(Request $request)
    {
        $user    = $request->user();
        $license = $user->enterpriseLicense()->with('invitations.user')->first();

        abort_unless($license, 403, 'Vous n\'avez pas de licence entreprise.');

        // Redirect to dedicated expired page instead of aborting
        if ($license->isExpired()) {
            return redirect()->route('enterprise.expired');
        }

        ['invitations' => $invitations, 'leaderboard' => $leaderboard, 'analytics' => $analytics]
            = $this->buildTeamAnalytics($license, $user);

        return view('enterprise.team', compact('license', 'invitations', 'leaderboard', 'analytics'));
    }

    /**
     * Calcule l'historique des invitations et les analytics agrégées de l'équipe
     * (titulaire + membres actifs) pour une licence donnée.
     */
    private function buildTeamAnalytics(EnterpriseLicense $license, User $user): array
    {
        $invitations = $license->invitations()
            ->with('user')
            ->orderByRaw("FIELD(status,'active','pending','available','revoked')")
            ->latest()
            ->get();

        $memberIds = $invitations->where('status', 'active')->pluck('user_id')->filter()->values();
        $teamIds   = $memberIds->push($user->id)->unique();

        $teamUsers = User::whereIn('id', $teamIds)
            ->select(['id', 'first_name', 'last_name', 'email', 'points_balance'])
            ->get()
            ->keyBy('id');

        $leadsSentByUser = Lead::whereIn('sender_id', $teamIds)
            ->selectRaw('sender_id, COUNT(*) as total, SUM(status = "converted") as converted')
            ->groupBy('sender_id')
            ->get()
            ->keyBy('sender_id');

        $leadsReceivedByUser = Lead::whereIn('receiver_id', $teamIds)
            ->selectRaw('receiver_id, COUNT(*) as total')
            ->groupBy('receiver_id')
            ->get()
            ->keyBy('receiver_id');

        $connectionsByUser = \App\Models\Connection::where('status', 'accepted')
            ->where(function ($q) use ($teamIds) {
                $q->whereIn('sender_id', $teamIds)->orWhereIn('receiver_id', $teamIds);
            })
            ->get()
            ->flatMap(fn($c) => [$c->sender_id, $c->receiver_id])
            ->filter(fn($id) => $teamIds->contains($id))
            ->countBy();

        $leaderboard = $teamIds->map(function ($id) use ($teamUsers, $leadsSentByUser, $leadsReceivedByUser, $connectionsByUser) {
            $u = $teamUsers->get($id);
            if (!$u) return null;
            return [
                'user'        => $u,
                'leads_sent'  => (int) ($leadsSentByUser->get($id)->total ?? 0),
                'converted'   => (int) ($leadsSentByUser->get($id)->converted ?? 0),
                'leads_recv'  => (int) ($leadsReceivedByUser->get($id)->total ?? 0),
                'connections' => (int) ($connectionsByUser->get($id) ?? 0),
                'points'      => (int) ($u->points_balance ?? 0),
            ];
        })->filter()->sortByDesc('leads_sent')->values();

        $analytics = [
            'leads_sent_total'  => $leaderboard->sum('leads_sent'),
            'leads_converted'   => $leaderboard->sum('converted'),
            'leads_recv_total'  => $leaderboard->sum('leads_recv'),
            'connections_total' => $leaderboard->sum('connections'),
            'points_total'      => $leaderboard->sum('points'),
            'days_left'         => $license->expires_at ? max(0, now()->diffInDays($license->expires_at, false)) : null,
        ];

        return compact('invitations', 'leaderboard', 'analytics');
    }

    /**
     * Envoie un message (notification in-app + email) du titulaire à un membre de son pack.
     */
    public function messageMember(Request $request, int $userId): RedirectResponse
    {
        $holder  = $request->user();
        $license = $holder->enterpriseLicense()->first();
        abort_unless($license, 403);

        // Le destinataire doit être un membre actif de CE pack
        $isMember = $license->invitations()->where('user_id', $userId)->where('status', 'active')->exists();
        abort_unless($isMember, 403, 'Ce membre ne fait pas partie de votre pack.');

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $member = User::findOrFail($userId);
        $holderName = trim("{$holder->first_name} {$holder->last_name}");

        Notification::storeForUser(
            $member,
            'enterprise_holder_message',
            $data['subject'],
            $data['message'],
            ['url' => route('enterprise.team'), 'from_user_id' => $holder->id]
        );

        try {
            Mail::to($member->email)->send(new SystemNotificationMail(
                recipientName: $member->first_name,
                title:         $data['subject'],
                body:          nl2br(htmlspecialchars($data['message'])) . "<p style=\"color:#94a3b8;font-size:12px;margin-top:16px;\">Message envoyé par {$holderName}, titulaire de votre Pack Entreprise « {$license->company_name} ».</p>",
                actionLabel:   'Répondre depuis mon dashboard',
                actionUrl:     route('dashboard'),
            ));
        } catch (\Exception $e) {
            Log::warning('Enterprise member message email failed', ['error' => $e->getMessage()]);
        }

        return back()->with('success', "Message envoyé à {$member->first_name} {$member->last_name}.");
    }

    public function invite(Request $request)
    {
        $user    = $request->user();
        $license = $user->enterpriseLicense()->first();

        abort_unless($license, 403);

        if ($license->isExpired()) {
            return redirect()->route('enterprise.expired');
        }

        $request->validate(['email' => ['required', 'email', 'max:255']]);
        $email = strtolower(trim($request->email));

        if ($email === strtolower($user->email)) {
            return back()->with('error', 'Vous ne pouvez pas vous inviter vous-même.');
        }

        // Already has active/pending invitation on this license
        $existingActive = $license->invitations()
            ->whereNotNull('email')
            ->where('email', $email)
            ->whereIn('status', ['pending', 'active'])
            ->first();

        if ($existingActive) {
            return back()->with('error', $email . ' possède déjà une licence active ou en attente.');
        }

        // Claim an available slot
        $slot = $license->invitations()->where('status', EnterpriseInvitation::STATUS_AVAILABLE)->first();

        if (! $slot) {
            return back()->with('error', 'Aucune licence disponible. Augmentez votre quota ou révoquez un membre inactif.');
        }

        $targetUser = User::where('email', $email)->first();

        $slot->update([
            'email'      => $email,
            'user_id'    => $targetUser?->id,
            'status'     => EnterpriseInvitation::STATUS_PENDING,
            'invited_by' => $user->id,
            'token'      => EnterpriseInvitation::generateToken(),
        ]);

        $license->increment('seats_used');
        $this->sendInvitationEmail($slot, $license);

        return back()->with('success', "Invitation envoyée à {$email}.");
    }

    /**
     * Renvoie l'email d'invitation à un membre qui n'a pas encore accepté (statut pending).
     */
    public function resendInvitation(Request $request, int $invId)
    {
        $user    = $request->user();
        $license = $user->enterpriseLicense()->first();
        abort_unless($license, 403);

        $invitation = $license->invitations()->findOrFail($invId);

        if ($invitation->status !== EnterpriseInvitation::STATUS_PENDING) {
            return back()->with('error', 'Cette invitation ne peut pas être renvoyée (statut : ' . $invitation->status . ').');
        }

        // Nouveau token pour invalider l'ancien lien
        $invitation->update(['token' => EnterpriseInvitation::generateToken()]);

        $this->sendInvitationEmail($invitation, $license);

        return back()->with('success', "Invitation renvoyée à {$invitation->email}.");
    }

    public function revoke(Request $request, int $invId)
    {
        $user    = $request->user();
        $license = $user->enterpriseLicense()->first();
        abort_unless($license, 403);

        $invitation = $license->invitations()->findOrFail($invId);

        if ($invitation->status === EnterpriseInvitation::STATUS_AVAILABLE) {
            return back()->with('info', 'Cette licence est déjà disponible.');
        }

        // Downgrade the member if they had been granted a plan
        if ($invitation->user_id && in_array($invitation->status, ['active', 'pending'])) {
            $this->downgradeToBasic($invitation->user_id);
            $license->decrement('seats_used');
        }

        // Reset slot back to available for re-use
        $invitation->update([
            'status'      => EnterpriseInvitation::STATUS_AVAILABLE,
            'email'       => null,
            'user_id'     => null,
            'invited_by'  => null,
            'accepted_at' => null,
            'token'       => EnterpriseInvitation::generateToken(),
        ]);

        return back()->with('success', 'Licence libérée. Le membre a été rétrogradé en Basic. La licence est à nouveau disponible.');
    }

    // ── Enterprise quote request ──────────────────────────────────────────────

    public function requestQuote(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:100'],
            'seats_needed' => ['required', 'integer', 'min:2', 'max:500'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'message'      => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        // Always persist in DB so the admin can see it regardless of email delivery
        EnterpriseQuoteRequest::create([
            'user_id'      => $user->id,
            'company_name' => $data['company_name'],
            'seats_needed' => $data['seats_needed'],
            'phone'        => $data['phone'] ?? null,
            'message'      => $data['message'] ?? null,
            'status'       => 'pending',
        ]);

        // Also send email notification (best-effort)
        $body = "<p>Nouvelle demande de devis Pack Entreprise :</p>
<ul>
<li><strong>Entreprise :</strong> {$data['company_name']}</li>
<li><strong>Utilisateurs souhaités :</strong> {$data['seats_needed']} licences</li>
<li><strong>Demandeur :</strong> {$user->first_name} {$user->last_name} ({$user->email})</li>
<li><strong>Téléphone :</strong> " . ($data['phone'] ?: '—') . "</li>
<li><strong>Message :</strong> " . nl2br(htmlspecialchars($data['message'] ?? '')) . "</li>
</ul>
<p><a href=\"" . route('admin.super.enterprise.quotes') . "\">Voir les demandes dans l'administration →</a></p>";

        try {
            $adminEmail = env('ADMIN_EMAIL', config('mail.from.address'));
            Mail::to($adminEmail)->send(new SystemNotificationMail(
                recipientName: 'Équipe LeadXchange',
                title:         'Demande de devis Pack Entreprise — ' . $data['company_name'],
                body:          $body,
                actionLabel:   'Voir les demandes',
                actionUrl:     route('admin.super.enterprise.quotes'),
            ));
        } catch (\Exception $e) {
            Log::warning('Enterprise quote request email failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        return back()->with('enterprise_quote_sent', true);
    }

    // ── Join flow (public, tokenised) ─────────────────────────────────────────

    public function join(string $token)
    {
        $invitation = EnterpriseInvitation::with('license.holder')
            ->where('token', $token)
            ->whereIn('status', [EnterpriseInvitation::STATUS_PENDING, EnterpriseInvitation::STATUS_AVAILABLE])
            ->firstOrFail();

        $existingUser = $invitation->user ?? ($invitation->email ? User::where('email', $invitation->email)->first() : null);

        // Existing user with a pending invite → login required (no registration needed)
        $confirmOnly = ($existingUser && $invitation->status === EnterpriseInvitation::STATUS_PENDING && $invitation->user_id);

        // Already logged in as the invited account? No need to re-enter the password.
        $alreadyAuthenticated = $confirmOnly && auth()->check() && auth()->id() === $existingUser->id;

        return view('enterprise.join', compact('invitation', 'existingUser', 'confirmOnly', 'alreadyAuthenticated'));
    }

    public function processJoin(Request $request, string $token)
    {
        $invitation = EnterpriseInvitation::with('license')
            ->where('token', $token)
            ->whereIn('status', [EnterpriseInvitation::STATUS_PENDING, EnterpriseInvitation::STATUS_AVAILABLE])
            ->firstOrFail();

        $wasAvailable = $invitation->status === EnterpriseInvitation::STATUS_AVAILABLE;

        // ── Case 1: available slot (no email pre-set) — full registration form ──
        if ($wasAvailable) {
            $request->validate([
                'email'      => ['required', 'email', 'max:255'],
                'password'   => ['required', 'string'],
            ]);

            $email      = strtolower(trim($request->email));
            $targetUser = User::where('email', $email)->first();

            if (! $targetUser) {
                // New account — first/last name required, password becomes the account password.
                $request->validate([
                    'first_name' => ['required', 'string', 'max:80'],
                    'last_name'  => ['required', 'string', 'max:80'],
                    'password'   => ['min:8', 'confirmed'],
                ]);

                $targetUser = User::create([
                    'first_name'        => $request->first_name,
                    'last_name'         => $request->last_name,
                    'email'             => $email,
                    'password'          => Hash::make($request->password),
                    'role'              => 'user',
                    'points_balance'    => 0,
                    'badge_level'       => 'neutre',
                    'email_verified_at' => now(),
                ]);
            } else {
                // Existing account with this email — must prove ownership before attaching
                // the licence. Never overwrite name/password of an account we don't own.
                $alreadyAuthenticated = auth()->check() && auth()->id() === $targetUser->id;

                if (! $alreadyAuthenticated) {
                    try {
                        $valid = Auth::guard()->validate(['email' => $targetUser->email, 'password' => $request->password]);
                    } catch (\RuntimeException $e) {
                        Log::error('Enterprise join hash format error', ['user_id' => $targetUser->id, 'error' => $e->getMessage()]);
                        $valid = false;
                    }

                    if (! $valid) {
                        return back()->withErrors([
                            'password' => 'Un compte existe déjà avec cet email. Saisissez le mot de passe de ce compte pour continuer.',
                        ])->withInput();
                    }
                }
            }

            $invitation->update([
                'email'       => $email,
                'user_id'     => $targetUser->id,
                'status'      => EnterpriseInvitation::STATUS_ACTIVE,
                'accepted_at' => now(),
            ]);

            $invitation->license->increment('seats_used');

        // ── Case 2: pending invitation — existing user must authenticate ──
        } elseif ($invitation->user_id && $invitation->user) {
            $targetUser = $invitation->user;

            // Already logged in as this exact account? No need to re-enter the password.
            $alreadyAuthenticated = auth()->check() && auth()->id() === $targetUser->id;

            if (! $alreadyAuthenticated) {
                $request->validate(['password' => ['required', 'string']]);

                try {
                    $valid = Auth::guard()->validate(['email' => $targetUser->email, 'password' => $request->password]);
                } catch (\RuntimeException $e) {
                    Log::error('Enterprise join hash format error', ['user_id' => $targetUser->id, 'error' => $e->getMessage()]);
                    $valid = false;
                }

                if (! $valid) {
                    return back()->withErrors(['password' => 'Mot de passe incorrect.'])->withInput();
                }
            }

            $invitation->update([
                'status'      => EnterpriseInvitation::STATUS_ACTIVE,
                'accepted_at' => now(),
            ]);

        // ── Case 3: pending invitation — new user, registration form ──
        } else {
            $request->validate([
                'first_name' => ['required', 'string', 'max:80'],
                'last_name'  => ['required', 'string', 'max:80'],
                'password'   => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $targetUser = User::create([
                'first_name'        => $request->first_name,
                'last_name'         => $request->last_name,
                'email'             => $invitation->email,
                'password'          => Hash::make($request->password),
                'role'              => 'user',
                'points_balance'    => 0,
                'badge_level'       => 'neutre',
                'email_verified_at' => now(),
            ]);

            $invitation->update([
                'user_id'     => $targetUser->id,
                'status'      => EnterpriseInvitation::STATUS_ACTIVE,
                'accepted_at' => now(),
            ]);
        }

        $this->grantEnterprisePlan($targetUser, $invitation->license);
        auth()->login($targetUser);

        return redirect()->route('dashboard')
            ->with('success', 'Bienvenue ! Votre licence Premium « ' . $invitation->license->company_name . ' » est maintenant active.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function grantEnterprisePlan(User $user, EnterpriseLicense $license): void
    {
        Subscription::where('user_id', $user->id)->update(['status' => 'canceled']);

        Subscription::create([
            'user_id'            => $user->id,
            'plan_id'            => $license->plan_id,
            'status'             => 'active',
            'current_period_end' => $license->expires_at,
        ]);
    }

    private function downgradeToBasic(int $userId): void
    {
        $basicPlan = Plan::where('name', 'basic')->first();
        Subscription::where('user_id', $userId)->update(['status' => 'canceled']);

        if ($basicPlan) {
            Subscription::create([
                'user_id' => $userId,
                'plan_id' => $basicPlan->id,
                'status'  => 'active',
            ]);
        }
    }

    private function sendInvitationEmail(EnterpriseInvitation $invitation, EnterpriseLicense $license): void
    {
        try {
            $license->loadMissing('holder');
            $holderName = trim(($license->holder?->first_name ?? '') . ' ' . ($license->holder?->last_name ?? '')) ?: 'LeadXchange';
            $company    = $license->company_name ?: $holderName;

            \App\Jobs\SendQueuedEmailJob::dispatch(
                to:       $invitation->email,
                subject:  $company . ' vous invite à rejoindre son équipe LeadXchange',
                type:     'enterprise_invitation',
                mailable: new \App\Mail\EnterpriseInvitationMail($invitation, $holderName),
                toName:   $invitation->email,
                metadata: ['invitation_id' => $invitation->id],
            );
        } catch (\Exception $e) {
            Log::warning('Enterprise invitation email failed', [
                'invitation_id' => $invitation->id,
                'error'         => $e->getMessage(),
            ]);
        }
    }
}
