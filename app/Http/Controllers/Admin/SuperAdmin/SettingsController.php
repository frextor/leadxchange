<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        return back()->with('success', 'Paramètres de devise enregistrés.');
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
        return back()->with('success', "Bandeau de maintenance {$status}.");
    }
}
