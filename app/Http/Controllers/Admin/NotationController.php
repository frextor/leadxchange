<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\LeadScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NotationController extends Controller
{
    public function __construct(private LeadScoreService $scorer) {}

    public function index(Request $request): View
    {
        $query = User::where('role', 'user')->with('city');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2
                ->where('first_name', 'like', "%{$q}%")
                ->orWhere('last_name',  'like', "%{$q}%")
                ->orWhere('email',      'like', "%{$q}%")
            );
        }
        if ($request->filled('badge')) {
            $query->where('badge_level', $request->badge);
        }

        $users = $query->orderByDesc('points_balance')->paginate(30)->withQueryString();

        $since = now()->subDays(LeadScoreService::WINDOW_DAYS);

        $details = DB::table('leads')
            ->whereBetween('leads.created_at', [$since, now()])
            ->select(
                DB::raw('sender_id as user_id'),
                DB::raw('count(*) as sent_count'),
                DB::raw('SUM(CASE WHEN lead_type="MQL" THEN 1 ELSE 0 END) as mql'),
                DB::raw('SUM(CASE WHEN lead_type="SQL" THEN 1 ELSE 0 END) as sql_count'),
                DB::raw('SUM(CASE WHEN lead_type="SP"  THEN 1 ELSE 0 END) as sp')
            )
            ->groupBy('sender_id')
            ->get()
            ->keyBy('user_id');

        $received = DB::table('leads')
            ->whereBetween('created_at', [$since, now()])
            ->select('receiver_id', DB::raw('count(*) as cnt'))
            ->groupBy('receiver_id')
            ->pluck('cnt', 'receiver_id');

        $badges = [
            'neutre'    => ['label' => 'Neutre',    'color' => 'bg-gray-100 text-gray-500',     'min' => 0,  'max' => 4],
            'bronze'    => ['label' => 'Bronze',    'color' => 'bg-amber-100 text-amber-700',   'min' => 5,  'max' => 9],
            'argent'    => ['label' => 'Argent',    'color' => 'bg-slate-100 text-slate-600',   'min' => 10, 'max' => 14],
            'or'        => ['label' => 'Or',        'color' => 'bg-yellow-100 text-yellow-700', 'min' => 15, 'max' => 19],
            'platinium' => ['label' => 'Platinium', 'color' => 'bg-indigo-100 text-indigo-700', 'min' => 20, 'max' => null],
        ];

        $thresholds = LeadScoreService::thresholds();
        $ranges     = LeadScoreService::ranges();

        $blockNegativeSender = (bool) SystemSetting::get('leads.block_negative_sender', true);
        $negativeGraceDays   = (int)  SystemSetting::get('leads.negative_sender_grace_days', 0);

        return view('admin.notation.index', compact('users', 'details', 'received', 'badges', 'thresholds', 'ranges', 'blockNegativeSender', 'negativeGraceDays'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'points_balance' => ['required', 'integer', 'min:-999', 'max:9999'],
            'badge_level'    => ['required', 'in:neutre,bronze,argent,or,platinium'],
        ]);

        $user->update([
            'points_balance' => $request->points_balance,
            'badge_level'    => $request->badge_level,
        ]);

        ActivityLogger::log('admin.notation.updated', "Notation de {$user->first_name} {$user->last_name} mise à jour", null, $user, ['points' => $request->points_balance, 'badge' => $request->badge_level]);
        return back()->with('success', "{$user->first_name} {$user->last_name} — notation mise à jour.");
    }

    public function recalculate(User $user): RedirectResponse
    {
        $this->scorer->updateUser($user);
        $fresh = $user->fresh();
        ActivityLogger::log('admin.notation.recalculated', "Score de {$user->first_name} {$user->last_name} recalculé : {$fresh->points_balance} pts ({$fresh->badge_level})", null, $user);
        return back()->with('success', "Score de {$user->first_name} recalculé : {$fresh->points_balance} pts ({$fresh->badge_level}).");
    }

    public function recalculateAll(): RedirectResponse
    {
        $count = $this->scorer->updateAll();
        ActivityLogger::log('admin.notation.recalculated_all', "{$count} utilisateur(s) recalculés");
        return back()->with('success', "{$count} utilisateur(s) recalculés.");
    }

    public function updateThresholds(Request $request): RedirectResponse
    {
        $request->validate([
            'badge_bronze_min'    => ['required', 'integer', 'min:1'],
            'badge_argent_min'    => ['required', 'integer', 'min:1'],
            'badge_or_min'        => ['required', 'integer', 'min:1'],
            'badge_platinium_min' => ['required', 'integer', 'min:1'],
            'badge_neutre_max'    => ['nullable', 'integer', 'min:0'],
            'badge_bronze_max'    => ['nullable', 'integer', 'min:1'],
            'badge_argent_max'    => ['nullable', 'integer', 'min:1'],
            'badge_or_max'        => ['nullable', 'integer', 'min:1'],
        ]);

        SystemSetting::set('badge_bronze_min',    $request->badge_bronze_min);
        SystemSetting::set('badge_argent_min',    $request->badge_argent_min);
        SystemSetting::set('badge_or_min',        $request->badge_or_min);
        SystemSetting::set('badge_platinium_min', $request->badge_platinium_min);

        if ($request->filled('badge_neutre_max')) {
            SystemSetting::set('badge_neutre_max', $request->badge_neutre_max);
        }
        if ($request->filled('badge_bronze_max')) {
            SystemSetting::set('badge_bronze_max', $request->badge_bronze_max);
        }
        if ($request->filled('badge_argent_max')) {
            SystemSetting::set('badge_argent_max', $request->badge_argent_max);
        }
        if ($request->filled('badge_or_max')) {
            SystemSetting::set('badge_or_max', $request->badge_or_max);
        }

        $count = $this->scorer->updateAll();
        ActivityLogger::log('admin.notation.thresholds_updated', "Seuils de badges mis à jour. {$count} utilisateur(s) recalculés");

        return back()->with('success', "Seuils mis à jour. {$count} utilisateur(s) recalculés.");
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'negative_grace_days' => ['nullable', 'integer', 'min:0', 'max:365'],
        ]);

        SystemSetting::updateOrCreate(
            ['key' => 'leads.block_negative_sender'],
            ['value' => $request->boolean('block_negative_sender') ? '1' : '0', 'type' => 'bool']
        );
        SystemSetting::updateOrCreate(
            ['key' => 'leads.negative_sender_grace_days'],
            ['value' => (int) $request->input('negative_grace_days', 0), 'type' => 'int']
        );
        Cache::forget('system_settings');

        ActivityLogger::log('admin.notation.settings_updated', 'Paramètres blocage solde négatif mis à jour', null, null, [
            'block'       => $request->boolean('block_negative_sender'),
            'grace_days'  => (int) $request->input('negative_grace_days', 0),
        ]);
        return back()->with('success', 'Paramètres mis à jour.');
    }

    public function icons(): View
    {
        $plans  = ['basic', 'premium', 'consul', 'ambassadeur'];
        $badges = ['neutre', 'bronze', 'argent', 'or', 'platinium'];
        return view('admin.notation.icons', compact('plans', 'badges'));
    }

    public function uploadPlanIcon(Request $request, string $key): RedirectResponse
    {
        abort_unless(in_array($key, ['basic', 'premium', 'consul', 'ambassadeur']), 404);
        $request->validate(['icon' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096']]);
        $request->file('icon')->move(public_path('images/plans'), $key . '.jpg');
        return back()->with('success_plan_' . $key, 'Image mise à jour.');
    }

    public function uploadBadgeIcon(Request $request, string $key): RedirectResponse
    {
        abort_unless(in_array($key, ['neutre', 'bronze', 'argent', 'or', 'platinium']), 404);
        $request->validate(['icon' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096']]);
        $request->file('icon')->move(public_path('images/badges'), $key . '.jpg');
        return back()->with('success_badge_' . $key, 'Image mise à jour.');
    }
}
