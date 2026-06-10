<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $query = Lead::with(['sender', 'receiver', 'sector']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2
                ->where('company_name',   'like', "%{$q}%")
                ->orWhere('contact_name', 'like', "%{$q}%")
                ->orWhere('contact_email','like', "%{$q}%")
            );
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('qualification')) {
            $query->where('qualification', $request->qualification);
        }
        if ($request->filled('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }
        if ($request->boolean('fraud')) {
            $query->where('fraud_reported', true);
        }

        $leads   = $query->orderByDesc('created_at')->paginate(25)->withQueryString();
        $sectors = Sector::orderBy('name')->get(['id', 'name']);

        $counts = [
            'total'     => Lead::count(),
            'pending'   => Lead::where('status', 'new')->count(),
            'converted' => Lead::where('status', 'converted')->count(),
            'fraud'     => Lead::where('fraud_reported', true)->count(),
        ];

        return view('admin.leads.index', compact('leads', 'sectors', 'counts'));
    }

    public function show(Lead $lead): View
    {
        $lead->load(['sender.profile', 'receiver.profile', 'sector', 'ratings.rater']);
        return view('admin.leads.show', compact('lead'));
    }
}
