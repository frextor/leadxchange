<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\LinkedInController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnterpriseController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home — Landing page (guests) or Dashboard (authenticated)
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('landing');
})->name('home');

// LinkedIn OAuth — web login redirect + shared callback (web & mobile)
Route::get('/auth/linkedin',          [LinkedInController::class, 'redirect'])->name('login.linkedin');
Route::get('/auth/linkedin/callback', [LinkedInController::class, 'callback'])->name('linkedin.callback');

Route::get('/.well-known/assetlinks.json', function () {
    return response()->json([
        [
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => [
                'namespace' => 'android_app',
                'package_name' => 'com.leadxchange.app',
                'sha256_cert_fingerprints' => [
                    '95:05:C4:C8:5D:09:97:80:4B:C6:68:35:9E:D4:98:18:E6:2C:04:72:A6:53:F1:12:74:D9:E6:10:5A:EC:95:AD',
                ],
            ],
        ],
    ])->header('Content-Type', 'application/json');
})->name('android.assetlinks');

// Enterprise join page (public — no auth required, creates account automatically if needed)
Route::get('/enterprise/join/{token}',  [EnterpriseController::class, 'join'])->name('enterprise.join');
Route::post('/enterprise/join/{token}', [EnterpriseController::class, 'processJoin'])->name('enterprise.join.process');

// Legacy deep-link redirect (kept for mobile app deep links)
Route::get('/enterprise/invitations/{token}', function (string $token) {
    $appUrl = 'x-tensia://enterprise/invitations/' . rawurlencode($token);
    $fallbackUrl = route('register', ['invitation_token' => $token]);
    $escapedAppUrl = e($appUrl);
    $escapedFallbackUrl = e($fallbackUrl);

    return response(<<<HTML
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitation LeadXchange</title>
    <meta http-equiv="refresh" content="2;url={$escapedFallbackUrl}">
    <script>
        window.location.replace('{$escapedAppUrl}');
        setTimeout(function () {
            window.location.href = '{$escapedFallbackUrl}';
        }, 1500);
    </script>
</head>
<body style="font-family: Arial, sans-serif; padding: 24px; color: #0D2B45;">
    <p>Ouverture de LeadXchange...</p>
    <p>Si l'application ne s'ouvre pas, <a href="{$escapedFallbackUrl}">continuer sur le web</a>.</p>
</body>
</html>
HTML);
})->name('enterprise.invitations.redirect');

// Legal pages (CGU, Privacy Policy) — public, no auth required
Route::get('/legal/{slug}', [PageController::class, 'show'])->name('legal.show');

// Firebase Messaging Service Worker (must be at root scope, no auth required)
Route::get('/firebase-messaging-sw.js', function () {
    return response()
        ->view('firebase-sw', ['config' => config('firebase')])
        ->header('Content-Type', 'application/javascript')
        ->header('Service-Worker-Allowed', '/');
})->name('firebase.sw');

// Stripe webhook — no auth, no CSRF (excluded in VerifyCsrfToken)
Route::post('/stripe/webhook', [\App\Http\Controllers\StripeCheckoutController::class, 'webhook'])->name('stripe.webhook');

// ==========================================
// Guest Routes (Not authenticated)
// ==========================================
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');

    // Registration
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Password Reset
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// ==========================================
// Email Verification Routes
// ==========================================
// verify: public + signed (no auth required — controller logs the user in)
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');

// resend: requires auth
Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// ==========================================
// Protected Routes (Authenticated)
// ==========================================
Route::middleware(['auth', 'user', 'email.verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home',      [DashboardController::class, 'index']);

    // Upgrade / Plans
    Route::get('/upgrade', function () {
        $plans = \App\Models\Plan::where('is_active', true)->orderBy('sort_order')->get();
        $currentPlan = auth()->user()->subscription?->plan;
        return view('upgrade', compact('plans', 'currentPlan'));
    })->name('upgrade');

    // Stripe Checkout (auth required)
    Route::post('/checkout/{plan}', [\App\Http\Controllers\StripeCheckoutController::class, 'checkout'])->name('checkout');
    Route::get('/checkout/success',  [\App\Http\Controllers\StripeCheckoutController::class, 'success'])->name('checkout.success');
    Route::post('/points/buy',     [\App\Http\Controllers\PointsPurchaseController::class, 'checkout'])->name('points.buy');
    Route::get('/points/success',  [\App\Http\Controllers\PointsPurchaseController::class, 'success'])->name('points.success');

    // Company Routes
    Route::get('/company/create', [CompanyController::class, 'create'])->name('company.create');
    Route::get('/company/search', [CompanyController::class, 'search'])->name('company.search');
    Route::get('/company/siret-lookup', [CompanyController::class, 'siretLookup'])->name('company.siret-lookup');
    Route::post('/company', [CompanyController::class, 'store'])->name('company.store');

    // Members/Network Page (Find Members)
    Route::get('/connections', [MemberController::class, 'index'])->name('connections.index');

    // Groups
    Route::get('/groups',                                       [GroupController::class, 'index'])->name('groups.index');
    Route::post('/groups',                                      [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{id}',                                  [GroupController::class, 'show'])->name('groups.show');
    Route::post('/groups/{id}/join',                            [GroupController::class, 'join'])->name('groups.join');
    Route::delete('/groups/{id}/leave',                         [GroupController::class, 'leave'])->name('groups.leave');
    Route::post('/groups/{id}/posts',                           [GroupController::class, 'storePost'])->name('groups.posts.store');
    Route::delete('/groups/{id}/posts/{postId}',                [GroupController::class, 'destroyPost'])->name('groups.posts.destroy');
    Route::post('/groups/{id}/posts/{postId}/comments',         [GroupController::class, 'storeComment'])->name('groups.comments.store');
    Route::post('/groups/{id}/activities',                      [GroupController::class, 'storeActivity'])->name('groups.activities.store');
    Route::delete('/groups/{id}',                               [GroupController::class, 'destroy'])->name('groups.destroy');
    // Region selector — stocke la ville active en session
    Route::post('/region/select', [\App\Http\Controllers\RegionSelectorController::class, 'select'])->name('region.select');

    Route::post('/groups/{id}/invite',               [GroupController::class, 'invite'])->name('groups.invite');
    Route::post('/groups/{id}/invite-region',        [GroupController::class, 'inviteRegion'])->name('groups.invite-region');
    Route::get('/groups/users/search',                          [GroupController::class, 'searchUsers'])->name('groups.users.search');
    Route::post('/groups/invitations/{invId}/accept',           [GroupController::class, 'acceptInvitation'])->name('groups.invitations.accept');
    Route::post('/groups/invitations/{invId}/decline',          [GroupController::class, 'declineInvitation'])->name('groups.invitations.decline');
    Route::delete('/groups/{id}/members/{userId}',              [GroupController::class, 'removeMember'])->name('groups.members.destroy');
    Route::post('/groups/{id}/members/{userId}/promote',        [GroupController::class, 'promoteAdmin'])->name('groups.members.promote');
    Route::post('/groups/{id}/members/{userId}/demote',         [GroupController::class, 'demoteAdmin'])->name('groups.members.demote');
    Route::post('/groups/{id}/members/{userId}/block',          [GroupController::class, 'blockMember'])->name('groups.members.block');
    Route::post('/groups/{id}/requests/{userId}/approve',       [GroupController::class, 'approveRequest'])->name('groups.requests.approve');
    Route::post('/groups/{id}/requests/{userId}/reject',        [GroupController::class, 'rejectRequest'])->name('groups.requests.reject');

    // Events
    Route::get('/events',                                       [EventController::class, 'index'])->name('events.index');
    Route::post('/events',                                      [EventController::class, 'store'])->name('events.store');
    Route::post('/events/invitations/{invId}/accept',           [EventController::class, 'acceptInvitation'])->name('events.invitations.accept');
    Route::post('/events/invitations/{invId}/decline',          [EventController::class, 'declineInvitation'])->name('events.invitations.decline');
    Route::get('/events/{id}',                                  [EventController::class, 'show'])->name('events.show');
    Route::post('/events/{id}/join',                            [EventController::class, 'join'])->name('events.join');
    Route::delete('/events/{id}/leave',                         [EventController::class, 'leave'])->name('events.leave');
    Route::post('/events/{id}/invite',                          [EventController::class, 'invite'])->name('events.invite');
    Route::post('/events/{id}/invite-group',                    [EventController::class, 'inviteGroup'])->name('events.invite-group');
    Route::delete('/events/{id}',                               [EventController::class, 'destroy'])->name('events.destroy');
    Route::delete('/events/{id}/attendees/{userId}',            [EventController::class, 'removeAttendee'])->name('events.attendees.destroy');

    // §12 CGU — Facturation & abonnement
    Route::get('/account/billing',            [\App\Http\Controllers\BillingController::class, 'index'])->name('billing.index');
    Route::post('/account/billing/cancel',    [\App\Http\Controllers\BillingController::class, 'cancel'])->name('billing.cancel');
    Route::post('/account/billing/reactivate',[\App\Http\Controllers\BillingController::class, 'reactivate'])->name('billing.reactivate');

    // Support (RGPD + Signalement + Mes demandes)
    Route::get('/support',            [SupportController::class, 'index'])->name('support.index');
    Route::post('/support/rgpd',      [SupportController::class, 'submitRgpd'])->name('support.rgpd');
    Route::post('/support/report',    [SupportController::class, 'submitReport'])->name('support.report');
    // Backward compat — old RGPD URL redirects to new Support page
    Route::get('/rgpd/request',  fn() => redirect()->route('support.index'))->name('rgpd.request');
    Route::post('/rgpd/request', fn() => redirect()->route('support.index'))->name('rgpd.submit');

    // §8.2 CGU — Signaler un comportement abusif (depuis un profil)
    Route::post('/users/{user}/report', function (\Illuminate\Http\Request $request, \App\Models\User $user) {
        $request->validate([
            'reason'  => ['required', 'in:' . implode(',', array_keys(\App\Models\UserReport::REASONS))],
            'details' => ['nullable', 'string', 'max:500'],
        ]);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas vous signaler vous-même.');
        }

        try {
            \App\Models\UserReport::create([
                'reporter_id' => auth()->id(),
                'reported_id' => $user->id,
                'reason'      => $request->reason,
                'details'     => $request->details,
            ]);
            return back()->with('success', 'Signalement envoyé. Notre équipe le traitera sous 10 jours ouvrés.');
        } catch (\Illuminate\Database\QueryException) {
            return back()->with('info', 'Vous avez déjà signalé ce membre pour cette raison.');
        }
    })->name('users.report');

    // §3.3 CGU — Accepter la nouvelle version
    Route::post('/legal/accept-cgu', function (\Illuminate\Http\Request $request) {
        $version = \App\Models\SystemSetting::get('cgu_current_version', '1.1');
        $request->user()->update(['cgu_version' => $version, 'cgu_accepted_at' => now()]);
        return back()->with('success', 'Merci d\'avoir accepté les nouvelles CGU v' . $version . '.');
    })->name('cgu.accept');

    // Consul/Ambassador requests (user-facing)
    Route::post('/consul/request',         [\App\Http\Controllers\ConsulRequestController::class, 'store'])->name('consul.request');
    Route::post('/consul/request-promote', [\App\Http\Controllers\ConsulRequestController::class, 'requestConsulPromotion'])->name('consul.request-promote');

    // ── Consul Space ─────────────────────────────────────────────────────────
    Route::middleware('consul')->prefix('consul')->name('consul.')->group(function () {
        Route::get('/',                                [\App\Http\Controllers\Consul\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/groupes/{group}/membres',         [\App\Http\Controllers\Consul\DashboardController::class, 'groupMembers'])->name('group.members');
        Route::get('/groupes/{group}/evenements',      [\App\Http\Controllers\Consul\DashboardController::class, 'groupEvents'])->name('group.events');
    });

    // ── Ambassador Space (ambassador-only, regional) ──────────────────────────
    Route::middleware('ambassador')->prefix('ambassador')->name('ambassador.')->group(function () {

        // Dashboard
        Route::get('/', [\App\Http\Controllers\Ambassador\DashboardController::class, 'index'])->name('dashboard');

        // Members
        Route::get('/members', [\App\Http\Controllers\Ambassador\MembersController::class, 'index'])->name('members.index');
        Route::post('/members/{user}/invite', [\App\Http\Controllers\Ambassador\MembersController::class, 'inviteToEvent'])->name('members.invite');

        // Events
        Route::get('/events',                  [\App\Http\Controllers\Ambassador\EventsController::class, 'index'])->name('events.index');
        Route::get('/events/create',           [\App\Http\Controllers\Ambassador\EventsController::class, 'create'])->name('events.create');
        Route::post('/events',                 [\App\Http\Controllers\Ambassador\EventsController::class, 'store'])->name('events.store');
        Route::get('/events/{event}',          [\App\Http\Controllers\Ambassador\EventsController::class, 'show'])->name('events.show');
        Route::get('/events/{event}/edit',     [\App\Http\Controllers\Ambassador\EventsController::class, 'edit'])->name('events.edit');
        Route::put('/events/{event}',          [\App\Http\Controllers\Ambassador\EventsController::class, 'update'])->name('events.update');
        Route::post('/events/{event}/cancel',  [\App\Http\Controllers\Ambassador\EventsController::class, 'cancel'])->name('events.cancel');
        Route::get('/events/{event}/export',     [\App\Http\Controllers\Ambassador\EventsController::class, 'exportParticipants'])->name('events.export');
        Route::post('/events/{event}/invite-group',  [\App\Http\Controllers\Ambassador\EventsController::class, 'inviteGroup'])->name('events.invite-group');
        Route::post('/events/{event}/invite-region', [\App\Http\Controllers\Ambassador\EventsController::class, 'inviteRegion'])->name('events.invite-region');

        // Consul management (existing — preserved)
        Route::prefix('consuls')->name('consuls.')->group(function () {
            Route::get('/',                [\App\Http\Controllers\Ambassador\ConsulManagementController::class, 'index'])->name('index');
            Route::post('/{user}/approve', [\App\Http\Controllers\Ambassador\ConsulManagementController::class, 'approveConsulRequest'])->name('approve');
            Route::post('/{user}/reject',  [\App\Http\Controllers\Ambassador\ConsulManagementController::class, 'rejectConsulRequest'])->name('reject');
            Route::post('/{user}/nominate',[\App\Http\Controllers\Ambassador\ConsulManagementController::class, 'nominateConsul'])->name('nominate');
        });

        // Leads analytics
        Route::get('/leads', [\App\Http\Controllers\Ambassador\LeadsController::class, 'index'])->name('leads.index');

        // Invitations
        Route::get('/invitations',              [\App\Http\Controllers\Ambassador\InvitationsController::class, 'index'])->name('invitations.index');
        Route::post('/invitations/{user}/send', [\App\Http\Controllers\Ambassador\InvitationsController::class, 'send'])->name('invitations.send');

        // Performance
        Route::get('/performance',  [\App\Http\Controllers\Ambassador\PerformanceController::class, 'index'])->name('performance.index');
        Route::post('/performance', [\App\Http\Controllers\Ambassador\PerformanceController::class, 'updateObjectives'])->name('performance.update');

        // Ranking
        Route::get('/ranking', [\App\Http\Controllers\Ambassador\RankingController::class, 'index'])->name('ranking.index');

        // Communication
        Route::get('/communication',  [\App\Http\Controllers\Ambassador\CommunicationController::class, 'index'])->name('communication.index');
        Route::post('/communication', [\App\Http\Controllers\Ambassador\CommunicationController::class, 'announce'])->name('communication.announce');


        // Reports
        Route::get('/reports',               [\App\Http\Controllers\Ambassador\ReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/{type}', [\App\Http\Controllers\Ambassador\ReportsController::class, 'export'])->name('reports.export');

        // Profile
        Route::get('/profile', [\App\Http\Controllers\Ambassador\AmbassadorProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [\App\Http\Controllers\Ambassador\AmbassadorProfileController::class, 'update'])->name('profile.update');

        // Notifications
        Route::get('/notifications', [\App\Http\Controllers\Ambassador\NotificationsController::class, 'index'])->name('notifications.index');
    });

    // Leads (Exchanges)
    Route::get('/leads',                  [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{id}',             [LeadController::class, 'show'])->name('leads.show');
    Route::post('/leads',                 [LeadController::class, 'store'])->name('leads.store');
    Route::post('/leads/{id}/accept',     [LeadController::class, 'accept'])->name('leads.accept');
    Route::post('/leads/{id}/reject',     [LeadController::class, 'reject'])->name('leads.reject');
    Route::post('/leads/{id}/convert',    [LeadController::class, 'convert'])->name('leads.convert');
    Route::post('/leads/{id}/rate',       [LeadController::class, 'rate'])->name('leads.rate');
    Route::post('/leads/{id}/report',     [LeadController::class, 'report'])->name('leads.report');

    // Chat
    Route::get('/chat',                              [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/inbox/check',                  [ChatController::class, 'checkInbox'])->name('chat.inbox.check');
    Route::post('/chat/{userId}',                    [ChatController::class, 'store'])->name('chat.store');
    Route::post('/chat/{userId}/media',              [ChatController::class, 'uploadMedia'])->name('chat.media');
    Route::get('/chat/{userId}/poll/{lastId}',       [ChatController::class, 'poll'])->name('chat.poll');
    Route::post('/chat/{userId}/typing',             [ChatController::class, 'typing'])->name('chat.typing');

    // Profile Routes
    Route::get('/profile', function () {
        return redirect()->route('profile.show', ['id' => auth()->id()]);
    })->name('profile.me');
    Route::post('/profile/video',   [ProfileController::class, 'uploadVideo'])->name('profile.video.upload');
    Route::delete('/profile/video', [ProfileController::class, 'deleteVideo'])->name('profile.video.delete');
    Route::get('/profile/{id}',     [ProfileController::class, 'show'])->name('profile.show');

    // Enterprise team management (holder only)
    Route::get('/enterprise/expired',                 [EnterpriseController::class, 'expired'])->name('enterprise.expired');
    // Proposition reçue (client)
    Route::get('/enterprise/proposal/{token}',        [\App\Http\Controllers\EnterpriseProposalController::class, 'view'])->name('enterprise.proposal.view');
    Route::get('/enterprise/proposal/{token}/paid',   [\App\Http\Controllers\EnterpriseProposalController::class, 'paid'])->name('enterprise.proposal.paid');
    Route::get('/enterprise/team',                    [EnterpriseController::class, 'team'])->name('enterprise.team');
    Route::post('/enterprise/team/invite',            [EnterpriseController::class, 'invite'])->name('enterprise.invite');
    Route::post('/enterprise/team/{inv}/revoke',      [EnterpriseController::class, 'revoke'])->name('enterprise.revoke');

    // Enterprise quote request
    Route::post('/enterprise/request-quote',          [EnterpriseController::class, 'requestQuote'])->name('enterprise.request-quote');

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});


// Referral — lien d'invitation : stocke le token en session et redirige vers l'inscription web
Route::get('/referral/{token}', function (string $token) {
    $referral = \App\Models\Referral::where('token', $token)->where('status', 'pending')->first();
    if ($referral) {
        session(['referral_token' => $token]);
    }
    return redirect()->route('register');
})->name('referral.register');

// Parrainage (espace connecté)
Route::middleware(['auth'])->group(function () {
    Route::get('/parrainage',        [\App\Http\Controllers\ReferralWebController::class, 'index'])->name('referral.index');
    Route::post('/parrainage/send',  [\App\Http\Controllers\ReferralWebController::class, 'send'])->name('referral.send');
});
