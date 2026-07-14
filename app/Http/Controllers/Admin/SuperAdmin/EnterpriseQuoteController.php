<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\EnterpriseQuoteRequest;
use Illuminate\Http\Request;

class EnterpriseQuoteController extends Controller
{
    public function index()
    {
        $quotes = EnterpriseQuoteRequest::with('user')
            ->orderByRaw("FIELD(status,'pending','contacted','converted','closed')")
            ->orderByDesc('created_at')
            ->get();

        $pendingCount = $quotes->where('status', 'pending')->count();

        return view('admin.enterprise.quotes', compact('quotes', 'pendingCount'));
    }

    public function update(Request $request, EnterpriseQuoteRequest $quote)
    {
        $request->validate([
            'status'      => ['required', 'in:pending,contacted,converted,closed'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $quote->update([
            'status'      => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with('success', 'Demande mise à jour.');
    }
}
