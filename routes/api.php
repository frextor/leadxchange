<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ConnectionController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\ProfileController;
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
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// ==========================================
// Protected Routes (Web sessions + API tokens)
// ==========================================
Route::middleware(['web', 'auth:sanctum'])->group(function () {

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
    Route::get('/users', [UserController::class, 'index']);       // Get paginated users list
    Route::get('/users/{id}', [UserController::class, 'show']);   // Get user details

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
    Route::get('/interests',                [ProfileController::class, 'interests']);
});
