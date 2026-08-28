<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\ActivityLogger;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function currency(): View
    {
        $settings = SystemSetting::where('group', 'currency')->get()->keyBy('key');

        return view('admin.super_admin.settings.currency', compact('settings'));
    }

    public function updateCurrency(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'currency_symbol'       => ['required', 'string', 'max:10'],
            'currency_position'     => ['required', 'in:before,after'],
            'currency_decimals'     => ['required', 'integer', 'in:0,2'],
            'currency_thousands_sep'=> ['required', 'string', 'max:3'],
            'currency_decimal_sep'  => ['required', 'string', 'max:3'],
        ]);

        foreach ($data as $key => $value) {
            SystemSetting::set($key, $value);
        }

        Cache::forget('system_settings');
        ActivityLogger::log('admin.settings.updated', "Paramètres de devise mis à jour (symbole : {$data['currency_symbol']})");

        return back()->with('success', 'Paramètres de devise enregistrés.');
    }

    // ── Prix du point ────────────────────────────────────────────────────

    public function pointsSettings(): View
    {
        $pricePerUnit    = (float) SystemSetting::get('points.price_per_unit', 5.00);
        $annualEnabled   = (bool)  SystemSetting::get('billing.annual_enabled', true);
        $annualDiscount  = (int)   SystemSetting::get('billing.annual_discount_pct', 0);

        return view('admin.super_admin.settings.points', compact('pricePerUnit', 'annualEnabled', 'annualDiscount'));
    }

    public function updateBillingAnnual(Request $request): RedirectResponse
    {
        $request->validate([
            'billing_annual_discount_pct' => ['required', 'integer', 'min:0', 'max:80'],
        ]);

        $enabled = $request->boolean('billing_annual_enabled');

        SystemSetting::updateOrCreate(
            ['key' => 'billing.annual_enabled'],
            ['value' => $enabled ? '1' : '0', 'type' => 'bool', 'group' => 'billing']
        );
        SystemSetting::updateOrCreate(
            ['key' => 'billing.annual_discount_pct'],
            ['value' => $request->billing_annual_discount_pct, 'type' => 'int', 'group' => 'billing']
        );
        Cache::forget('system_settings');

        ActivityLogger::log('admin.settings.updated', "Facturation annuelle : " . ($enabled ? 'activée' : 'désactivée') . ", remise {$request->billing_annual_discount_pct}%");

        return back()->with('success', 'Paramètres de facturation annuelle enregistrés.');
    }

    public function updatePointsPrice(Request $request): RedirectResponse
    {
        $request->validate([
            'points_price_per_unit' => ['required', 'numeric', 'min:0.01', 'max:9999'],
        ]);

        SystemSetting::updateOrCreate(
            ['key' => 'points.price_per_unit'],
            ['value' => $request->points_price_per_unit, 'type' => 'float', 'group' => 'points']
        );
        Cache::forget('system_settings');

        ActivityLogger::log('admin.settings.updated', "Prix du point mis à jour : {$request->points_price_per_unit}");

        return back()->with('success', 'Prix du point enregistré.');
    }

    // ── §5.2 Welcome popup ───────────────────────────────────────────────

    public function welcomePopup(): View
    {
        $settings  = SystemSetting::where('group', 'welcome_popup')->get()->keyBy('key');
        $interests = \App\Models\Interest::orderBy('name')->get();

        return view('admin.super_admin.settings.welcome-popup', compact('settings', 'interests'));
    }

    public function updateWelcomePopup(Request $request): RedirectResponse
    {
        $request->validate([
            'welcome_popup_enabled'   => ['boolean'],
            'welcome_popup_criteria'  => ['required', 'in:none,same_city,same_region,same_interest,both'],
            'welcome_popup_count'     => ['required', 'integer', 'in:2,3,4'],
            'welcome_popup_frequency' => ['required', 'in:once,session,always'],
            'welcome_popup_title'     => ['nullable', 'string', 'max:120'],
            'welcome_popup_subtitle'  => ['nullable', 'string', 'max:200'],
            'welcome_popup_btn_later' => ['nullable', 'string', 'max:60'],
            'welcome_popup_btn_cta'   => ['nullable', 'string', 'max:60'],
        ]);

        $fields = [
            'welcome_popup_enabled'   => $request->boolean('welcome_popup_enabled') ? '1' : '0',
            'welcome_popup_criteria'  => $request->welcome_popup_criteria,
            'welcome_popup_count'     => $request->welcome_popup_count,
            'welcome_popup_frequency' => $request->welcome_popup_frequency,
            'welcome_popup_title'     => $request->welcome_popup_title     ?? '',
            'welcome_popup_subtitle'  => $request->welcome_popup_subtitle  ?? '',
            'welcome_popup_btn_later' => $request->welcome_popup_btn_later ?? '',
            'welcome_popup_btn_cta'   => $request->welcome_popup_btn_cta   ?? '',
        ];

        foreach ($fields as $key => $value) {
            SystemSetting::updateOrCreate(['key' => $key], [
                'value' => $value,
                'group' => 'welcome_popup',
                'type'  => $key === 'welcome_popup_enabled' ? 'bool' : ($key === 'welcome_popup_count' ? 'int' : 'string'),
            ]);
        }

        Cache::forget('system_settings');
        ActivityLogger::log('admin.settings.updated', "Paramètres popup de bienvenue mis à jour (critère : {$request->welcome_popup_criteria})");

        return back()->with('success', 'Paramètres du popup enregistrés.');
    }

    // ── §5.3 Page À propos ───────────────────────────────────────────────

    public function aboutPage(): View
    {
        $settings = SystemSetting::where('group', 'about_page')->get()->keyBy('key');
        return view('admin.super_admin.settings.about-page', compact('settings'));
    }

    public function updateAboutPage(Request $request): RedirectResponse
    {
        $request->validate([
            'about_enabled'       => ['boolean'],
            'about_title'         => ['required', 'string', 'max:100'],
            'about_tagline'       => ['nullable', 'string', 'max:200'],
            'about_mission'       => ['nullable', 'string', 'max:600'],
            'about_content'       => ['nullable', 'string', 'max:10000'],
            'about_contact_email' => ['nullable', 'email', 'max:150'],
            'about_founded_year'  => ['nullable', 'integer', 'min:2000', 'max:2030'],
            'about_cta_label'     => ['nullable', 'string', 'max:60'],
            'about_cta_url'       => ['nullable', 'string', 'max:255'],
        ]);

        $fields = [
            'about_enabled'       => $request->boolean('about_enabled') ? '1' : '0',
            'about_title'         => $request->about_title,
            'about_tagline'       => $request->about_tagline       ?? '',
            'about_mission'       => $request->about_mission       ?? '',
            'about_content'       => $request->about_content       ?? '',
            'about_contact_email' => $request->about_contact_email ?? '',
            'about_founded_year'  => $request->about_founded_year  ?? '',
            'about_cta_label'     => $request->about_cta_label     ?? '',
            'about_cta_url'       => $request->about_cta_url       ?? '',
        ];

        foreach ($fields as $key => $value) {
            SystemSetting::updateOrCreate(['key' => $key], [
                'value' => $value,
                'group' => 'about_page',
                'type'  => $key === 'about_enabled' ? 'bool' : 'string',
            ]);
        }

        Cache::forget('system_settings');
        ActivityLogger::log('admin.settings.updated', 'Page À propos mise à jour');

        return back()->with('success', 'Page À propos enregistrée.');
    }

    // ── §5.x Payments ─────────────────────────────────────────────────────

    public function payments(): View
    {
        $settings = SystemSetting::where('group', 'payments')->get()->keyBy('key');

        return view('admin.super_admin.settings.payments', compact('settings'));
    }

    public function updatePayments(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'point_price_cents'                  => ['required', 'integer', 'min:1', 'max:100000'],
            'subscription_test_mode'             => ['nullable', 'boolean'],
            'subscription_test_monthly_minutes'  => ['nullable', 'integer', 'min:1', 'max:10080'],
            'subscription_test_annual_minutes'   => ['nullable', 'integer', 'min:1', 'max:10080'],
        ]);

        SystemSetting::updateOrCreate(
            ['key' => 'payments.point_price_cents'],
            ['value' => (string) $data['point_price_cents'], 'group' => 'payments', 'type' => 'int']
        );

        $testMode = !empty($data['subscription_test_mode']);
        SystemSetting::updateOrCreate(
            ['key' => 'payments.subscription_test_mode'],
            ['value' => $testMode ? '1' : '0', 'group' => 'payments', 'type' => 'bool']
        );
        SystemSetting::updateOrCreate(
            ['key' => 'payments.subscription_test_monthly_minutes'],
            ['value' => (string) ($data['subscription_test_monthly_minutes'] ?? 5), 'group' => 'payments', 'type' => 'int']
        );
        SystemSetting::updateOrCreate(
            ['key' => 'payments.subscription_test_annual_minutes'],
            ['value' => (string) ($data['subscription_test_annual_minutes'] ?? 10), 'group' => 'payments', 'type' => 'int']
        );

        Cache::forget('system_settings');
        ActivityLogger::log('admin.settings.updated', "Paramètres de paiement mis à jour (test_mode=" . ($testMode ? 'on' : 'off') . ")");

        return back()->with('success', 'Paramètres enregistrés.');
    }

    // ── §5.3 Maintenance ──────────────────────────────────────────────────

    public function maintenance(): View
    {
        $settings = SystemSetting::where('group', 'maintenance')->get()->keyBy('key');
        return view('admin.super_admin.settings.maintenance', compact('settings'));
    }

    public function updateMaintenance(Request $request): RedirectResponse
    {
        $request->validate([
            'maintenance_banner_enabled' => ['boolean'],
            'maintenance_banner_message' => ['required', 'string', 'max:300'],
        ]);

        SystemSetting::set('maintenance_banner_enabled', $request->boolean('maintenance_banner_enabled') ? '1' : '0');
        SystemSetting::set('maintenance_banner_message', $request->maintenance_banner_message);
        Cache::forget('system_settings');

        $status = $request->boolean('maintenance_banner_enabled') ? 'activé' : 'désactivé';
        ActivityLogger::log('admin.settings.updated', "Bandeau de maintenance {$status}");
        return back()->with('success', "Bandeau de maintenance {$status}.");
    }
}
