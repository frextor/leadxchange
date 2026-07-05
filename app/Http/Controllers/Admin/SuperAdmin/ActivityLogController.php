<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::with('causer')->orderByDesc('created_at');

        if ($request->filled('category')) {
            $category = $request->category;
            $query->when($category === 'security', fn($q) => $q->where('event', 'like', '%login_failed%'));
            $query->when($category === 'auth',    fn($q) => $q->where('event', 'like', 'auth.%')->where('event', 'not like', '%login_failed%'));
            $query->when($category === 'admin',   fn($q) => $q->where('event', 'like', 'admin.%'));
            $query->when($category === 'payment', fn($q) => $q->where('event', 'like', 'payment.%'));
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('description', 'like', "%{$s}%")
                ->orWhere('ip_address', 'like', "%{$s}%")
                ->orWhereHas('causer', fn($q2) => $q2
                    ->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name',  'like', "%{$s}%")
                    ->orWhere('email',      'like', "%{$s}%")
                )
            );
        }

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs  = $query->paginate(50)->withQueryString();
        $total = ActivityLog::count();

        $events = ActivityLog::selectRaw('event, count(*) as cnt')
            ->groupBy('event')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'event');

        return view('admin.super_admin.activity_log.index', compact('logs', 'total', 'events'));
    }
}
