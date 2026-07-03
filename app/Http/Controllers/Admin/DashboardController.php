<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        // Super admin → toujours redirigé vers le super admin dashboard
        if (auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.super.dashboard');
        }

        $stats = [
            'total_users'       => User::where('role', 'user')->count(),
            'total_admins'      => User::whereIn('role', ['admin', 'super_admin'])->count(),
            'total_events'      => DB::table('events')->count(),
            'total_leads'       => DB::table('leads')->count(),
            'total_connections' => DB::table('connections')->where('status', 'accepted')->count(),
            'total_groups'      => DB::table('groups')->count(),
            'pending_videos'    => DB::table('profiles')->whereNotNull('presentation_video')->where('presentation_video_status', 'pending')->count(),
            'fraud_leads'       => DB::table('leads')->where('fraud_reported', true)->count(),
            'unverified_users'  => User::where('role', 'user')->whereNull('email_verified_at')->count(),
            'active_subs'       => DB::table('subscriptions')->where('status', 'active')->count(),
        ];

        $recentUsers = User::with('subscription.plan')
            ->where('role', 'user')
            ->latest()
            ->limit(8)
            ->get(['id', 'first_name', 'last_name', 'email', 'role', 'created_at', 'email_verified_at']);

        return view('admin.dashboard.index', compact('stats', 'recentUsers'));
    }
}
