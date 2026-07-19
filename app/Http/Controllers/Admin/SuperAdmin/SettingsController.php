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
            'welcome_popup_criteria'  => ['required', 'in:none,same_city,same_interest,both'],
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
