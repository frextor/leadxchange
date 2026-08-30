<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $query = Feedback::with('user')
            ->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $feedbacks = $query->paginate(30)->withQueryString();
        $total     = Feedback::count();
        $pending   = Feedback::where('status', 'pending')->count();

        return view('admin.feedbacks.index', compact('feedbacks', 'total', 'pending'));
    }

    public function updateStatus(Feedback $feedback, Request $request)
    {
        $request->validate(['status' => ['required', 'in:pending,reviewed,closed']]);
        $feedback->update(['status' => $request->status]);
        return back()->with('success', 'Statut mis à jour.');
    }
}
