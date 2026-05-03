<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\MemberController;
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
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/home', function () {
        return view('dashboard');
    })->name('home');

    // Company Routes
    Route::get('/company/create', [CompanyController::class, 'create'])->name('company.create');
    Route::get('/company/search', [CompanyController::class, 'search'])->name('company.search');
    Route::post('/company', [CompanyController::class, 'store'])->name('company.store');

    // Members/Network Page (Find Members)
    Route::get('/connections', [MemberController::class, 'index'])->name('connections.index');

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
