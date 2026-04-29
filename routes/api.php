<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
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

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Onboarding (original routes - keep for backward compatibility)
    Route::post('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/company', [AuthController::class, 'createCompany']);

    // Company routes (new - for search and advanced features)
    Route::prefix('companies')->group(function () {
        Route::get('/search', [CompanyController::class, 'search']);      // Search companies
        Route::get('/me', [CompanyController::class, 'getUserCompany']);  // Get user's company
        Route::post('/', [CompanyController::class, 'store']);            // Create/Join company (alternative to /company)
        Route::get('/', [CompanyController::class, 'index']);             // List all companies (optional)
    });
});
