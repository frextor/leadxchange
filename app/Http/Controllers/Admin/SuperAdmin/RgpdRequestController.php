<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\RgpdRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;
use Illuminate\View\View;

class RgpdRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status   = $request->get('status', 'pending');
        $requests = RgpdRequest::with(['user', 'processedBy'])
            ->where('status', $status)
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $counts = [];
        foreach (array_keys(RgpdRequest::STATUSES) as $s) {
            $counts[$s] = RgpdRequest::where('status', $s)->count();
        }

        return view('admin.super_admin.rgpd.index', compact('requests', 'status', 'counts'));
    }

    public function update(Request $request, RgpdRequest $rgpdRequest): RedirectResponse
    {
        $request->validate([
            'status'      => ['required', 'in:pending,processing,completed,rejected'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $rgpdRequest->update([
            'status'       => $request->status,
            'admin_notes'  => $request->admin_notes,
            'processed_by' => auth()->id(),
            'processed_at' => in_array($request->status, ['completed', 'rejected']) ? now() : null,
        ]);

        ActivityLogger::log('admin.rgpd.processed', "Demande RGPD #{$rgpdRequest->id} : {$request->status}", null, $rgpdRequest, ['status' => $request->status]);
        return back()->with('success', 'Demande RGPD mise à jour.');
    }
}
