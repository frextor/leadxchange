<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeadController as ApiLeadController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ConnectionController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\EnterpriseInvitationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProfileVisitorController;
use App\Http\Controllers\Api\ProfileVideoModerationController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\ChatController as ApiChatController;
use App\Http\Controllers\Api\ChatFirebaseController;
use App\Http\Controllers\Api\EventController as ApiEventController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\PollController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ==========================================
// Public Routes (No authentication)
// ==========================================
Route::prefix('auth')->group(function () {
    Route::post('/register',        [AuthController::class, 'register']);
    Route::post('/login',           [AuthController::class, 'login']);
    Route::post('/linkedin',        [AuthController::class, 'linkedin']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password',  [AuthController::class, 'resetPassword']);
});

// Reference data — public, no auth needed
Route::get('/ping',     fn() => response()->json(['status' => 'ok']));
Route::get('/settings', [SettingsController::class, 'index']);
Route::get('/enterprise/invitations/{token}', [EnterpriseInvitationController::class, 'show']);
Route::post('/stripe/webhook', StripeWebhookController::class);


// ==========================================
// Protected Routes (Web sessions + API tokens)
// ==========================================
Route::middleware(['auth:sanctum'])->group(function () {

    // Auth Routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/company', [AuthController::class, 'createCompany']);

    // Company Routes
    Route::prefix('companies')->group(function () {
        Route::get('/search', [CompanyController::class, 'search']);      // Search companies
        Route::get('/me', [CompanyController::class, 'getUserCompany']);  // Get user's company
        Route::post('/', [CompanyController::class, 'store']);            // Create/Join company
        Route::get('/', [CompanyController::class, 'index']);             // List all companies
    });

    // Connection Routes
    Route::prefix('connections')->group(function () {
        Route::get('/', [ConnectionController::class, 'index']);              // Get all connections
        Route::post('/', [ConnectionController::class, 'store']);             // Send connection request
        Route::post('/{id}/accept', [ConnectionController::class, 'accept']); // Accept request
        Route::post('/{id}/reject', [ConnectionController::class, 'reject']); // Reject request
        Route::delete('/{id}', [ConnectionController::class, 'destroy']);     // Cancel pending request
        Route::post('/{id}/remove', [ConnectionController::class, 'remove']); // Remove accepted connection
    });

    // User Routes
    Route::get('/users',                  [UserController::class, 'index']);           // Paginated list + search
    Route::get('/users/recommendations',  [UserController::class, 'recommendations']); // Location + interest scoring
    Route::get('/users/{id}',             [UserController::class, 'show']);            // User details
    Route::post('/users/{id}/visit',        [UserController::class, 'recordVisit']);    // Record profile visit

    // FCM Device Token Routes
    Route::post('/device-token', [DeviceTokenController::class, 'store']);
    Route::delete('/device-token', [DeviceTokenController::class, 'destroy']);

    // Profile Routes
    Route::get('/profile',                  [ProfileController::class, 'show']);
    Route::put('/profile/location',         [ProfileController::class, 'updateLocation']);
    Route::put('/profile/basic',            [ProfileController::class, 'updateBasic']);
    Route::put('/profile/professional',     [ProfileController::class, 'updateProfessional']);
    Route::put('/profile/bio',              [ProfileController::class, 'updateBio']);
    Route::post('/profile/avatar',          [ProfileController::class, 'updateAvatar']);
    Route::post('/profile/presentation-video', [ProfileController::class, 'updatePresentationVideo']);
    Route::post('/profile/interests',       [ProfileController::class, 'syncInterests']);
    Route::post('/profile/complete',        [ProfileController::class, 'complete']);
    Route::post('/profile/ambassador-request', [ProfileController::class, 'requestAmbassador']);
    Route::post('/profile/consul-request',    [ProfileController::class, 'requestConsul']);
    Route::get('/interests',                [ProfileController::class, 'interests']);
    Route::get('/languages',                [LanguageController::class, 'index']);
    Route::get('/countries',                [CountryController::class, 'index']);
    Route::get('/profile/visitors',         [ProfileVisitorController::class, 'index']);

    Route::prefix('admin/profile-videos')->group(function () {
        Route::get('/', [ProfileVideoModerationController::class, 'index']);
        Route::patch('/{userId}/approve', [ProfileVideoModerationController::class, 'approve']);
        Route::patch('/{userId}/reject', [ProfileVideoModerationController::class, 'reject']);
    });

    // Group Routes
    Route::get('/groups',                                       [GroupController::class, 'index']);
    Route::post('/groups',                                      [GroupController::class, 'store']);
    Route::get('/groups/mine',                                  [GroupController::class, 'mine']);
    Route::get('/groups/invitations',                           [GroupController::class, 'invitations']);
    Route::post('/groups/invitations/{invId}/accept',           [GroupController::class, 'acceptInvitation']);
    Route::post('/groups/invitations/{invId}/decline',          [GroupController::class, 'declineInvitation']);
    Route::get('/groups/{id}',                                  [GroupController::class, 'show']);
    Route::put('/groups/{id}',                                  [GroupController::class, 'update']);
    Route::delete('/groups/{id}',                               [GroupController::class, 'destroy']);
    Route::post('/groups/{id}/join',                            [GroupController::class, 'join']);
    Route::delete('/groups/{id}/leave',                         [GroupController::class, 'leave']);
    Route::post('/groups/{id}/invite',                          [GroupController::class, 'invite']);
    Route::get('/groups/{id}/members',                          [GroupController::class, 'members']);
    Route::delete('/groups/{id}/members/{userId}',              [GroupController::class, 'removeMember']);
    Route::post('/groups/{id}/members/{userId}/promote',        [GroupController::class, 'promote']);
    Route::post('/groups/{id}/members/{userId}/demote',         [GroupController::class, 'demote']);
    Route::post('/groups/{id}/members/{userId}/block',          [GroupController::class, 'block']);
    Route::get('/groups/{id}/posts',                            [GroupController::class, 'posts']);
    Route::post('/groups/{id}/posts',                           [GroupController::class, 'storePost']);
    Route::delete('/groups/{id}/posts/{postId}',                [GroupController::class, 'destroyPost']);
    Route::post('/groups/{id}/posts/{postId}/comments',         [GroupController::class, 'storeComment']);
    Route::post('/groups/{id}/activities',                      [GroupController::class, 'storeActivity']);
    Route::get('/groups/{group}/polls',              [PollController::class, 'index']);
    Route::post('/groups/{group}/polls',             [PollController::class, 'store']);
    Route::post('/groups/{group}/polls/{poll}/vote', [PollController::class, 'vote']);
    Route::patch('/groups/{group}/polls/{poll}',     [PollController::class, 'update']);
    Route::delete('/groups/{group}/polls/{poll}',    [PollController::class, 'destroy']);

    // Lead Routes
    Route::get('/leads',                   [ApiLeadController::class, 'index']);
    Route::post('/leads',                  [ApiLeadController::class, 'store']);
    Route::get('/leads/stats',             [ApiLeadController::class, 'stats']);
    Route::get('/leads/{id}',              [ApiLeadController::class, 'show']);
    Route::delete('/leads/{id}',           [ApiLeadController::class, 'destroy']);
    Route::post('/leads/{id}/accept',      [ApiLeadController::class, 'accept']);
    Route::post('/leads/{id}/reject',      [ApiLeadController::class, 'reject']);
    Route::post('/leads/{id}/convert',     [ApiLeadController::class, 'convert']);
    Route::post('/leads/{id}/rate',        [ApiLeadController::class, 'rate']);
    Route::post('/leads/{id}/report',      [ApiLeadController::class, 'report']);
    Route::patch('/leads/{id}/reschedule', [ApiLeadController::class, 'reschedule']);
    Route::post('/leads/{id}/transfer',    [ApiLeadController::class, 'transfer']);

    // Points history (CDC: GET /api/users/me/points/history)
    Route::get('/users/me/points/history', [ApiLeadController::class, 'pointsHistory']);

    // Payment Routes
    Route::get('/payments/config',                         [PaymentController::class, 'config']);
    Route::post('/payments/events/{event}/intent',         [PaymentController::class, 'eventIntent']);
    Route::post('/payments/plans/{plan}/subscription',     [PaymentController::class, 'planSubscription']);
    Route::get('/payments/status',                         [PaymentController::class, 'status']);

    // Enterprise invitation Routes
    Route::get('/enterprise/invitations',                  [EnterpriseInvitationController::class, 'index']);
    Route::post('/enterprise/invitations',                 [EnterpriseInvitationController::class, 'store']);
    Route::post('/enterprise/invitations/{token}/accept',  [EnterpriseInvitationController::class, 'accept']);

    // Event Routes
    Route::get('/events',                                       [ApiEventController::class, 'index']);
    Route::post('/events',                                      [ApiEventController::class, 'store']);
    Route::get('/events/mine',                                  [ApiEventController::class, 'mine']);
    Route::get('/events/invitations',                           [ApiEventController::class, 'invitations']);
    Route::post('/events/invitations/{invId}/accept',           [ApiEventController::class, 'acceptInvitation']);
    Route::post('/events/invitations/{invId}/decline',          [ApiEventController::class, 'declineInvitation']);
    Route::get('/events/{id}',                                  [ApiEventController::class, 'show']);
    Route::post('/events/{id}/join',                            [ApiEventController::class, 'join']);
    Route::delete('/events/{id}/leave',                         [ApiEventController::class, 'leave']);
    Route::post('/events/{id}/invite',                          [ApiEventController::class, 'invite']);
    Route::post('/events/{id}/invite/bulk',                     [ApiEventController::class, 'inviteBulk']);
    Route::delete('/events/{id}',                               [ApiEventController::class, 'destroy']);
    Route::get('/events/{id}/attendees',                        [ApiEventController::class, 'attendees']);
    Route::delete('/events/{id}/attendees/{userId}',            [ApiEventController::class, 'removeAttendee']);

    // Notification Routes
    Route::get('/notifications',                 [NotificationController::class, 'index']);
    Route::post('/notifications/read-all',       [NotificationController::class, 'readAll']);
    Route::delete('/notifications/{id}',         [NotificationController::class, 'destroy']);

    // Chat Routes — static paths must come before wildcard /{userId}
    Route::get('/chat/firebase/token',           [ChatFirebaseController::class, 'token']);
    Route::get('/chat',                          [ApiChatController::class, 'index']);
    Route::post('/chat/{userId}/messages',       [ApiChatController::class, 'store']);
    Route::get('/chat/{userId}',                 [ApiChatController::class, 'show']);
    Route::post('/chat/{userId}',                [ApiChatController::class, 'store']);
    Route::get('/chat/{userId}/poll/{lastId}',   [ApiChatController::class, 'poll']);
    Route::post('/chat/{conversationId}/read',   [ApiChatController::class, 'markRead']);
    Route::post('/chat/{conversationId}/typing', [ApiChatController::class, 'typing']);
});
