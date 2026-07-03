<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\UserReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserReportController extends Controller
{
    public function index(Request $request): View
    {
        $status   = $request->get('status', 'pending');
        $reports  = UserReport::with(['reporter','reported','reviewer'])
            ->where('status', $status)
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $counts = [
            'pending'   => UserReport::where('status', 'pending')->count(),
            'reviewed'  => UserReport::where('status', 'reviewed')->count(),
            'actioned'  => UserReport::where('status', 'actioned')->count(),
            'dismissed' => UserReport::where('status', 'dismissed')->count(),
        ];

        return view('admin.super_admin.reports.index', compact('reports', 'status', 'counts'));
    }

    public function action(Request $request, UserReport $report): RedirectResponse
    {
        $request->validate([
            'action'     => ['required', 'in:reviewed,actioned,dismissed'],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $report->update([
            'status'      => $request->action,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_note'  => $request->admin_note,
        ]);

        $labels = ['reviewed'=>'examiné','actioned'=>'traité','dismissed'=>'clôturé'];
        return back()->with('success', "Signalement {$labels[$request->action]}.");
    }
}
