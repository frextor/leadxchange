<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
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

// Home - Redirect to login
Route::get('/', function () {
    return redirect()->route('login');
});

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
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home',      [DashboardController::class, 'index'])->name('home');

    // Company Routes
    Route::get('/company/create', [CompanyController::class, 'create'])->name('company.create');
    Route::get('/company/search', [CompanyController::class, 'search'])->name('company.search');
    Route::get('/company/siret-lookup', [CompanyController::class, 'siretLookup'])->name('company.siret-lookup');
    Route::post('/company', [CompanyController::class, 'store'])->name('company.store');

    // Members/Network Page (Find Members)
    Route::get('/connections', [MemberController::class, 'index'])->name('connections.index');

    // Groups
    Route::get('/groups',               [GroupController::class, 'index'])->name('groups.index');
    Route::post('/groups',              [GroupController::class, 'store'])->name('groups.store');
    Route::post('/groups/{id}/join',    [GroupController::class, 'join'])->name('groups.join');
    Route::delete('/groups/{id}/leave', [GroupController::class, 'leave'])->name('groups.leave');

    // Profile Routes
    Route::get('/profile', function () {
        return redirect()->route('profile.show', ['id' => auth()->id()]);
    })->name('profile.me');
    Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.show');

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
