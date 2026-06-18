<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmbassadorController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status', 'pending');

        $query = User::with(['region', 'profile'])
            ->where('ambassador_status', $status);

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }

        $applicants = $query->orderBy('ambassador_requested_at', 'desc')->paginate(20)->withQueryString();
        $regions    = City::active()->orderBy('name')->get(['id', 'name']);
        $counts     = [
            'pending'  => User::where('ambassador_status', 'pending')->count(),
            'approved' => User::where('ambassador_status', 'approved')->count(),
            'rejected' => User::where('ambassador_status', 'rejected')->count(),
        ];

        return view('admin.super_admin.ambassadors.index', compact('applicants', 'regions', 'status', 'counts'));
    }

    public function show(User $user): View
    {
        $user->load(['region', 'profile', 'company', 'ambassadorReviewer']);
        return view('admin.super_admin.ambassadors.show', compact('user'));
    }

    public function approve(User $user): RedirectResponse
    {
        if ($user->ambassador_status !== 'pending') {
            return back()->with('error', 'Ce dossier n\'est pas en attente.');
        }

        $user->update([
            'ambassador_status'       => 'approved',
            'ambassador_reviewed_at'  => now(),
            'ambassador_reviewed_by'  => auth()->id(),
            'ambassador_rejection_reason' => null,
        ]);

        return back()->with('success', "{$user->first_name} {$user->last_name} est maintenant Ambassadeur.");
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if ($user->ambassador_status !== 'pending') {
            return back()->with('error', 'Ce dossier n\'est pas en attente.');
        }

        $user->update([
            'ambassador_status'           => 'rejected',
            'ambassador_reviewed_at'      => now(),
            'ambassador_reviewed_by'      => auth()->id(),
            'ambassador_rejection_reason' => $request->reason,
        ]);

        return redirect()->route('admin.super.ambassadors.index')
            ->with('success', "Demande de {$user->first_name} {$user->last_name} refusée.");
    }
}
