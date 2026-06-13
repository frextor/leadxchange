<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
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

// LinkedIn OAuth callback fallback for mobile app links.
Route::get('/auth/linkedin/callback', function (Request $request) {
    $query = http_build_query($request->only([
        'code',
        'state',
        'error',
        'error_description',
    ]));

    return redirect()->away('x-tensia://auth/linkedin/callback' . ($query !== '' ? "?{$query}" : ''));
})->name('linkedin.mobile.callback');

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

// Enterprise invitation fallback: app handles the deep link when installed;
// otherwise the browser lands on the regular registration page with the token.
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

// Firebase Messaging Service Worker (must be at root scope, no auth required)
Route::get('/firebase-messaging-sw.js', function () {
    return response()
        ->view('firebase-sw', ['config' => config('firebase')])
        ->header('Content-Type', 'application/javascript')
        ->header('Service-Worker-Allowed', '/');
})->name('firebase.sw');

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
Route::middleware('auth')->group(function () {
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');
});

// ==========================================
// Protected Routes (Authenticated)
// ==========================================
Route::middleware(['auth', 'user'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home',      [DashboardController::class, 'index']);

    // Upgrade / Plans
    Route::get('/upgrade', function () {
        $plans = \App\Models\Plan::where('is_active', true)->orderBy('sort_order')->get();
        $currentPlan = auth()->user()->subscription?->plan;
        return view('upgrade', compact('plans', 'currentPlan'));
    })->name('upgrade');

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
    Route::post('/groups/{id}/invite',                          [GroupController::class, 'invite'])->name('groups.invite');
    Route::post('/groups/invitations/{invId}/accept',           [GroupController::class, 'acceptInvitation'])->name('groups.invitations.accept');
    Route::post('/groups/invitations/{invId}/decline',          [GroupController::class, 'declineInvitation'])->name('groups.invitations.decline');
    Route::delete('/groups/{id}/members/{userId}',              [GroupController::class, 'removeMember'])->name('groups.members.destroy');
    Route::post('/groups/{id}/members/{userId}/promote',        [GroupController::class, 'promoteAdmin'])->name('groups.members.promote');
    Route::post('/groups/{id}/members/{userId}/demote',         [GroupController::class, 'demoteAdmin'])->name('groups.members.demote');

    // Events
    Route::get('/events',                                       [EventController::class, 'index'])->name('events.index');
    Route::post('/events',                                      [EventController::class, 'store'])->name('events.store');
    Route::post('/events/invitations/{invId}/accept',           [EventController::class, 'acceptInvitation'])->name('events.invitations.accept');
    Route::post('/events/invitations/{invId}/decline',          [EventController::class, 'declineInvitation'])->name('events.invitations.decline');
    Route::get('/events/{id}',                                  [EventController::class, 'show'])->name('events.show');
    Route::post('/events/{id}/join',                            [EventController::class, 'join'])->name('events.join');
    Route::delete('/events/{id}/leave',                         [EventController::class, 'leave'])->name('events.leave');
    Route::post('/events/{id}/invite',                          [EventController::class, 'invite'])->name('events.invite');
    Route::delete('/events/{id}',                               [EventController::class, 'destroy'])->name('events.destroy');
    Route::delete('/events/{id}/attendees/{userId}',            [EventController::class, 'removeAttendee'])->name('events.attendees.destroy');

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

    // Enterprise invitations
    Route::get('/enterprise',                           [EnterpriseController::class, 'index'])->name('enterprise.index');
    Route::post('/enterprise/invitations',              [EnterpriseController::class, 'store'])->name('enterprise.invitations.store');
    Route::post('/enterprise/invitations/{token}/accept', [EnterpriseController::class, 'accept'])->name('enterprise.invitations.accept');
    Route::delete('/enterprise/invitations/{invitation}/revoke', [EnterpriseController::class, 'revoke'])->name('enterprise.invitations.revoke');

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
