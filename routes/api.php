<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeadController as ApiLeadController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ConnectionController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProfileVisitorController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\ChatController as ApiChatController;
use App\Http\Controllers\Api\EventController as ApiEventController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\SettingsController;
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
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password',  [AuthController::class, 'resetPassword']);
});

// Reference data — public, no auth needed
Route::get('/ping',     fn() => response()->json(['status' => 'ok']));
Route::get('/settings', [SettingsController::class, 'index']);


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
        Route::delete('/{id}', [ConnectionController::class, 'destroy']);     // Cancel request
    });

    // User Routes
    Route::get('/users',                  [UserController::class, 'index']);           // Paginated list + search
    Route::get('/users/recommendations',  [UserController::class, 'recommendations']); // Location + interest scoring
    Route::get('/users/{id}',             [UserController::class, 'show']);            // User details

    // FCM Device Token Routes
    Route::post('/device-token', [DeviceTokenController::class, 'store']);
    Route::delete('/device-token', [DeviceTokenController::class, 'destroy']);

    // Profile Routes
    Route::get('/profile',                  [ProfileController::class, 'show']);
    Route::put('/profile/basic',            [ProfileController::class, 'updateBasic']);
    Route::put('/profile/professional',     [ProfileController::class, 'updateProfessional']);
    Route::put('/profile/bio',              [ProfileController::class, 'updateBio']);
    Route::post('/profile/avatar',          [ProfileController::class, 'updateAvatar']);
    Route::post('/profile/interests',       [ProfileController::class, 'syncInterests']);
    Route::post('/profile/complete',        [ProfileController::class, 'complete']);
    Route::get('/interests',                [ProfileController::class, 'interests']);
    Route::get('/languages',                [LanguageController::class, 'index']);
    Route::get('/countries',                [CountryController::class, 'index']);
    Route::get('/profile/visitors',         [ProfileVisitorController::class, 'index']);

    // Group Routes
    Route::get('/groups',                   [GroupController::class, 'index']);
    Route::post('/groups',                  [GroupController::class, 'store']);
    Route::get('/groups/{id}',              [GroupController::class, 'show']);
    Route::put('/groups/{id}',              [GroupController::class, 'update']);
    Route::post('/groups/{id}/join',        [GroupController::class, 'join']);
    Route::delete('/groups/{id}/leave',     [GroupController::class, 'leave']);
    Route::post('/groups/{id}/invite',                    [GroupController::class, 'invite']);
    Route::get('/groups/{id}/members',                    [GroupController::class, 'members']);
    Route::post('/groups/{id}/members/{userId}/promote',  [GroupController::class, 'promote']);

    // Lead Routes
    Route::get('/leads',                   [ApiLeadController::class, 'index']);
    Route::post('/leads',                  [ApiLeadController::class, 'store']);
    Route::get('/leads/{id}',              [ApiLeadController::class, 'show']);
    Route::post('/leads/{id}/accept',      [ApiLeadController::class, 'accept']);
    Route::post('/leads/{id}/reject',      [ApiLeadController::class, 'reject']);
    Route::post('/leads/{id}/convert',     [ApiLeadController::class, 'convert']);
    Route::post('/leads/{id}/rate',        [ApiLeadController::class, 'rate']);
    Route::post('/leads/{id}/report',      [ApiLeadController::class, 'report']);

    // Points history (CDC: GET /api/users/me/points/history)
    Route::get('/users/me/points/history', [ApiLeadController::class, 'pointsHistory']);

    // Event Routes
    Route::get('/events',               [ApiEventController::class, 'index']);
    Route::post('/events',              [ApiEventController::class, 'store']);
    Route::get('/events/{id}',          [ApiEventController::class, 'show']);
    Route::post('/events/{id}/join',    [ApiEventController::class, 'join']);
    Route::delete('/events/{id}/leave', [ApiEventController::class, 'leave']);

    // Chat Routes
    Route::get('/chat',                          [ApiChatController::class, 'index']);
    Route::get('/chat/{userId}',                 [ApiChatController::class, 'show']);
    Route::post('/chat/{userId}',                [ApiChatController::class, 'store']);
    Route::get('/chat/{userId}/poll/{lastId}',   [ApiChatController::class, 'poll']);
});
