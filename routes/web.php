<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConnectionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', function () {
    return redirect()->route('login');
});

// Guest Routes (Not authenticated)
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');

    // Registration
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Email Verification Routes (Authenticated)
Route::middleware('auth')->group(function () {
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');
});

// Protected Routes (Authenticated)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Dashboard
    Route::get('/home', function () {
        return view('dashboard');
    })->name('dashboard');

    // Company Routes
    Route::get('/company/create', [CompanyController::class, 'create'])->name('company.create');
    Route::get('/company/search', [CompanyController::class, 'search'])->name('company.search');
    Route::post('/company', [CompanyController::class, 'store'])->name('company.store');

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});


Route::middleware('auth')->group(function () {
    Route::prefix('connections')->name('connections.')->group(function () {
        Route::get('/', [ConnectionController::class, 'index'])->name('index');
        Route::post('/', [ConnectionController::class, 'store'])->name('store');
        Route::post('/{id}/accept', [ConnectionController::class, 'accept'])->name('accept');
        Route::post('/{id}/reject', [ConnectionController::class, 'reject'])->name('reject');
        Route::delete('/{id}', [ConnectionController::class, 'destroy'])->name('destroy');
    });
});
