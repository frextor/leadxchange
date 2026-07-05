<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\NotationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\SuperAdmin\AdminManagerController;
use App\Http\Controllers\Admin\SuperAdmin\AmbassadorController;
use App\Http\Controllers\Admin\SuperAdmin\ConsulController;
use App\Http\Controllers\Admin\SuperAdmin\UserReportController;
use App\Http\Controllers\Admin\SuperAdmin\CityController;
use App\Http\Controllers\Admin\SuperAdmin\CountryController;
use App\Http\Controllers\Admin\SuperAdmin\DashboardController as SuperDashboardController;
use App\Http\Controllers\Admin\SuperAdmin\InterestController;
use App\Http\Controllers\Admin\SuperAdmin\PlanController;
use App\Http\Controllers\Admin\SuperAdmin\SectorController;
use App\Http\Controllers\Admin\SuperAdmin\EmailTemplateController;
use App\Http\Controllers\Admin\SuperAdmin\SettingsController;
use App\Http\Controllers\Admin\SuperAdmin\SmtpController;
use App\Http\Controllers\Admin\SuperAdmin\ActivityLogController;
use App\Http\Controllers\Admin\SuperAdmin\PaymentsController;
use App\Http\Controllers\Admin\SuperAdmin\SubscriberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes  —  domain: admin.leadxchange.test / admin.x-tensia.com
|--------------------------------------------------------------------------
*/

// ── Guest ────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [\App\Http\Controllers\Admin\Auth\LoginController::class, 'show'])->name('admin.login');
    Route::post('/login', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'store'])
         ->middleware('throttle:5,1')
         ->name('admin.login.post');
});

// ── Admin + Super Admin ───────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->group(function () {

    // Dashboard — super_admin voit le super dashboard, admin voit le dashboard admin
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.alt');

    // Users
    Route::get('/users',                        [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/{user}',                 [UserController::class, 'show'])->name('admin.users.show');
    Route::get('/users/{user}/edit',            [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}',                 [UserController::class, 'update'])->name('admin.users.update');
    Route::post('/users/{user}/change-plan',       [UserController::class, 'changePlan'])->name('admin.users.change-plan');
    Route::post('/users/{user}/profile-reminder',  [UserController::class, 'sendProfileReminder'])->name('admin.users.profile-reminder');
    Route::delete('/users/{user}',                 [UserController::class, 'destroy'])->name('admin.users.destroy');

    // Leads
    Route::get('/leads',         [LeadController::class, 'index'])->name('admin.leads.index');
    Route::get('/leads/{lead}',  [LeadController::class, 'show'])->name('admin.leads.show');

    // Notation
    Route::get('/notation',                         [NotationController::class, 'index'])->name('admin.notation.index');
    Route::get('/notation/icons',                   [NotationController::class, 'icons'])->name('admin.notation.icons');
    Route::post('/notation/icons/plan/{key}',       [NotationController::class, 'uploadPlanIcon'])->name('admin.notation.icons.plan');
    Route::post('/notation/icons/badge/{key}',      [NotationController::class, 'uploadBadgeIcon'])->name('admin.notation.icons.badge');
    Route::put('/notation/{user}',                  [NotationController::class, 'update'])->name('admin.notation.update');
    Route::post('/notation/{user}/recalculate',     [NotationController::class, 'recalculate'])->name('admin.notation.recalculate');
    Route::post('/notation/recalculate-all',        [NotationController::class, 'recalculateAll'])->name('admin.notation.recalculate-all');
    Route::post('/notation/thresholds',             [NotationController::class, 'updateThresholds'])->name('admin.notation.thresholds');

    // Events
    Route::get('/events',              [EventController::class, 'index'])->name('admin.events.index');
    Route::delete('/events/{event}',   [EventController::class, 'destroy'])->name('admin.events.destroy');

    // Groups
    Route::get('/groups',              [GroupController::class, 'index'])->name('admin.groups.index');
    Route::get('/groups/{group}',      [GroupController::class, 'show'])->name('admin.groups.show');
    Route::delete('/groups/{group}',   [GroupController::class, 'destroy'])->name('admin.groups.destroy');

    // Video moderation
    Route::get('/videos',                        [VideoController::class, 'index'])->name('admin.videos.index');
    Route::post('/videos/{profile}/approve',     [VideoController::class, 'approve'])->name('admin.videos.approve');
    Route::post('/videos/{profile}/reject',      [VideoController::class, 'reject'])->name('admin.videos.reject');
});

// ── Super Admin only ─────────────────────────────────────────────────────
Route::middleware(['auth', 'super_admin'])->prefix('super')->name('admin.super.')->group(function () {

    // Dashboard
    Route::get('/',          [SuperDashboardController::class, 'index'])->name('dashboard');

    // Admin management
    Route::get('admins',                     [AdminManagerController::class, 'index'])->name('admins.index');
    Route::get('admins/create',              [AdminManagerController::class, 'create'])->name('admins.create');
    Route::post('admins',                    [AdminManagerController::class, 'store'])->name('admins.store');
    Route::post('admins/promote',            [AdminManagerController::class, 'promote'])->name('admins.promote');
    Route::get('admins/{user}/edit',         [AdminManagerController::class, 'edit'])->name('admins.edit');
    Route::put('admins/{user}',              [AdminManagerController::class, 'update'])->name('admins.update');
    Route::post('admins/{user}/demote',      [AdminManagerController::class, 'demote'])->name('admins.demote');

    // Plans
    Route::get('plans',                          [PlanController::class, 'index'])->name('plans.index');
    Route::get('plans/create',                   [PlanController::class, 'create'])->name('plans.create');
    Route::post('plans',                         [PlanController::class, 'store'])->name('plans.store');
    Route::get('plans/permissions',              [PlanController::class, 'permissions'])->name('plans.permissions');
    Route::post('plans/permissions',             [PlanController::class, 'updatePermissions'])->name('plans.permissions.update');
    Route::get('plans/permission-labels',        [PlanController::class, 'permissionLabels'])->name('plans.permission-labels');
    Route::put('plans/permission-labels',        [PlanController::class, 'updatePermissionLabels'])->name('plans.permission-labels.update');
    Route::get('plans/stripe',                   [PlanController::class, 'stripeIndex'])->name('plans.stripe');
    Route::get('payments',                       [PaymentsController::class, 'index'])->name('payments.index');
    Route::post('plans/stripe/sync-all',         [PlanController::class, 'stripeSyncAll'])->name('plans.stripe.sync-all');
    Route::post('plans/stripe/{plan}/sync',      [PlanController::class, 'stripeSyncPlan'])->name('plans.stripe.sync');
    Route::get('plans/{plan}/edit',              [PlanController::class, 'edit'])->name('plans.edit');
    Route::put('plans/{plan}',                   [PlanController::class, 'update'])->name('plans.update');
    Route::post('plans/{plan}/toggle',           [PlanController::class, 'toggleStatus'])->name('plans.toggle');

    // Activity log
    Route::get('activity-log',               [ActivityLogController::class, 'index'])->name('activity-log.index');

    // Subscribers
    Route::get('subscribers',                [SubscriberController::class, 'index'])->name('subscribers.index');

    // Ambassadors
    // Ancien système ambassadeurs — remplacé par manage-ambassadors (ConsulController)
    // Route::get('ambassadors', ...)->name('ambassadors.index'); // retiré

    // Consul requests (admin + ambassador)
    // Ambassador requests (from Consuls — index is the request queue)
    Route::get('consul',                          [ConsulController::class, 'index'])->name('consul.index');
    Route::post('consul/{consulRequest}/approve', [ConsulController::class, 'approve'])->name('consul.approve');
    Route::post('consul/{consulRequest}/reject',  [ConsulController::class, 'reject'])->name('consul.reject');

    // Consul management — admin nominates Premium users as Consul
    Route::get('manage-consuls',                       [ConsulController::class, 'consuls'])->name('consuls.manage');
    Route::post('manage-consuls/{user}/nominate',              [ConsulController::class, 'nominateConsul'])->name('consuls.nominate');
    Route::delete('manage-consuls/{user}/revoke',              [ConsulController::class, 'revokeConsul'])->name('consuls.revoke');
    Route::delete('manage-consuls/{user}/reject-request',      [ConsulController::class, 'rejectConsulRequest'])->name('consuls.reject-request');

    // Ambassador management (nominate directly or revoke)
    Route::post('manage-ambassadors/{user}/nominate',  [ConsulController::class, 'nominateAmbassador'])->name('ambassadors.nominate');
    Route::delete('manage-ambassadors/{user}/revoke',  [ConsulController::class, 'revokeAmbassador'])->name('ambassadors.revoke');

    // Unified Ambassadeurs page
    Route::get('manage-ambassadors',                   [AmbassadorController::class, 'index'])->name('ambassadors.manage');
    Route::post('manage-ambassadors/{user}/promote',   [ConsulController::class, 'nominateConsul'])->name('ambassadors.promote');

    // Sectors
    Route::get('sectors',                    [SectorController::class, 'index'])->name('sectors.index');
    Route::post('sectors',                   [SectorController::class, 'store'])->name('sectors.store');
    Route::put('sectors/{sector}',           [SectorController::class, 'update'])->name('sectors.update');
    Route::delete('sectors/{sector}',        [SectorController::class, 'destroy'])->name('sectors.destroy');

    // Cities
    Route::get('cities',                     [CityController::class, 'index'])->name('cities.index');
    Route::post('cities',                    [CityController::class, 'store'])->name('cities.store');
    Route::put('cities/{city}',              [CityController::class, 'update'])->name('cities.update');
    Route::patch('cities/{city}/toggle',     [CityController::class, 'toggle'])->name('cities.toggle');
    Route::delete('cities/{city}',           [CityController::class, 'destroy'])->name('cities.destroy');

    // Countries
    Route::get('countries',                  [CountryController::class, 'index'])->name('countries.index');
    Route::post('countries',                 [CountryController::class, 'store'])->name('countries.store');
    Route::put('countries/{country}',        [CountryController::class, 'update'])->name('countries.update');
    Route::delete('countries/{country}',     [CountryController::class, 'destroy'])->name('countries.destroy');

    // Interests
    Route::get('interests',                  [InterestController::class, 'index'])->name('interests.index');
    Route::post('interests',                 [InterestController::class, 'store'])->name('interests.store');
    Route::put('interests/{interest}',       [InterestController::class, 'update'])->name('interests.update');
    Route::delete('interests/{interest}',    [InterestController::class, 'destroy'])->name('interests.destroy');

    // Platform settings
    Route::get('settings/currency',     [SettingsController::class, 'currency'])->name('settings.currency');
    Route::put('settings/currency',     [SettingsController::class, 'updateCurrency'])->name('settings.currency.update');
    // Enterprise licenses — attribution des packs aux comptes holders
    Route::get('enterprise',               [\App\Http\Controllers\Admin\SuperAdmin\EnterpriseLicenseController::class, 'index'])->name('enterprise.index');
    Route::get('enterprise/create',        [\App\Http\Controllers\Admin\SuperAdmin\EnterpriseLicenseController::class, 'create'])->name('enterprise.create');
    Route::post('enterprise',              [\App\Http\Controllers\Admin\SuperAdmin\EnterpriseLicenseController::class, 'store'])->name('enterprise.store');
    Route::get('enterprise/{license}/edit',[\App\Http\Controllers\Admin\SuperAdmin\EnterpriseLicenseController::class, 'edit'])->name('enterprise.edit');
    Route::put('enterprise/{license}',     [\App\Http\Controllers\Admin\SuperAdmin\EnterpriseLicenseController::class, 'update'])->name('enterprise.update');
    Route::delete('enterprise/{license}',  [\App\Http\Controllers\Admin\SuperAdmin\EnterpriseLicenseController::class, 'destroy'])->name('enterprise.destroy');

    // Pages légales (CGU, Confidentialité)
    Route::get('pages',              [\App\Http\Controllers\Admin\SuperAdmin\PageController::class, 'index'])->name('pages.index');
    Route::get('pages/{page}/edit',  [\App\Http\Controllers\Admin\SuperAdmin\PageController::class, 'edit'])->name('pages.edit');
    Route::put('pages/{page}',       [\App\Http\Controllers\Admin\SuperAdmin\PageController::class, 'update'])->name('pages.update');

    // §8.2 CGU — Signalements comportements abusifs
    Route::get('reports',                      [UserReportController::class, 'index'])->name('reports.index');
    Route::post('reports/{report}/action',     [UserReportController::class, 'action'])->name('reports.action');

    // §10.8 RGPD — Demandes d'exercice des droits
    Route::get('rgpd',                         [\App\Http\Controllers\Admin\SuperAdmin\RgpdRequestController::class, 'index'])->name('rgpd.index');
    Route::put('rgpd/{rgpdRequest}',           [\App\Http\Controllers\Admin\SuperAdmin\RgpdRequestController::class, 'update'])->name('rgpd.update');

    Route::get('settings/maintenance',         [SettingsController::class, 'maintenance'])->name('settings.maintenance');
    Route::put('settings/maintenance',         [SettingsController::class, 'updateMaintenance'])->name('settings.maintenance.update');
    Route::get('settings/maintenance/preview', fn() => response()->view('errors.503', ['message' => 'Quelques minutes (démonstration)']))->name('settings.maintenance.preview');
    Route::post('settings/maintenance/down',   fn() => redirect()->back()->with('success', 'Mode maintenance activé. Exécutez : php artisan down'))->name('settings.maintenance.down');
    Route::post('settings/maintenance/up',     fn() => redirect()->back()->with('success', 'Application remise en ligne. Exécutez : php artisan up'))->name('settings.maintenance.up');

    // Email templates
    Route::get('email-templates',                         [EmailTemplateController::class, 'index'])->name('email-templates.index');
    Route::get('email-templates/{key}/edit',              [EmailTemplateController::class, 'edit'])->name('email-templates.edit');
    Route::put('email-templates/{key}',                   [EmailTemplateController::class, 'update'])->name('email-templates.update');
    Route::post('email-templates/{key}/reset',            [EmailTemplateController::class, 'reset'])->name('email-templates.reset');
    Route::post('email-templates/{key}/preview',    [EmailTemplateController::class, 'preview'])->name('email-templates.preview');
    Route::post('email-templates/{key}/send-test', [EmailTemplateController::class, 'sendTest'])->name('email-templates.send-test');

    // SMTP / Email settings
    Route::get('smtp',                       [SmtpController::class, 'index'])->name('smtp.index');
    Route::put('smtp',                       [SmtpController::class, 'update'])->name('smtp.update');
    Route::post('smtp/test-connection',      [SmtpController::class, 'testConnection'])->name('smtp.test-connection');
    Route::post('smtp/send-test',            [SmtpController::class, 'sendTest'])->name('smtp.send-test');
    Route::delete('smtp/logs',               [SmtpController::class, 'clearLogs'])->name('smtp.clear-logs');
});

// ── Logout ────────────────────────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('admin.logout');
