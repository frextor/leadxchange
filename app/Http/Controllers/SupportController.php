<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\RgpdRequest;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tab  = $request->get('tab', 'rgpd');

        $rgpdRequests = RgpdRequest::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $reports = UserReport::where('reporter_id', $user->id)
            ->with('reported:id,first_name,last_name')
            ->orderByDesc('created_at')
            ->get();

        $feedbacks = Feedback::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $historyCount = $rgpdRequests->count() + $reports->count() + $feedbacks->count();

        return view('support.index', compact('tab', 'rgpdRequests', 'reports', 'feedbacks', 'historyCount'));
    }

    public function submitRgpd(Request $request): RedirectResponse
    {
        $request->validate([
            'right_type' => ['required', 'in:' . implode(',', array_keys(RgpdRequest::RIGHTS))],
            'details'    => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        RgpdRequest::create([
            'user_id'    => $user->id,
            'right_type' => $request->right_type,
            'details'    => $request->details,
            'status'     => 'pending',
        ]);

        $type    = RgpdRequest::RIGHTS[$request->right_type];
        $details = $request->details ?? 'Aucun détail fourni.';

        try {
            Mail::raw(
                "Demande RGPD\n\nUtilisateur : {$user->first_name} {$user->last_name} ({$user->email})\nDroit demandé : {$type}\nDétails : {$details}\nDate : " . now()->format('d/m/Y H:i'),
                fn($m) => $m->to('contact@leadxchange.com')->subject("[RGPD] Demande de {$user->first_name} {$user->last_name} — {$type}")
            );
        } catch (\Throwable) {}

        return redirect()->route('support.index', ['tab' => 'history'])
            ->with('support_success', 'Votre demande RGPD a été envoyée. Nous vous répondrons sous 1 mois (Art. 12 RGPD).');
    }

    public function submitFeedback(Request $request): RedirectResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        Feedback::create([
            'user_id' => $request->user()->id,
            'message' => $request->message,
            'status'  => 'pending',
        ]);

        return redirect()->route('support.index', ['tab' => 'history'])
            ->with('support_success', 'Merci pour votre retour ! Notre équipe le prendra en compte.');
    }

    public function submitReport(Request $request): RedirectResponse
    {
        $request->validate([
            'reported_email' => ['nullable', 'email', 'max:255'],
            'reason'         => ['required', 'in:' . implode(',', array_keys(UserReport::REASONS))],
            'details'        => ['nullable', 'string', 'max:500'],
        ]);

        $user       = $request->user();
        $reportedId = null;

        if ($request->filled('reported_email')) {
            $reported = User::where('email', $request->reported_email)->first();
            if ($reported) {
                if ($reported->id === $user->id) {
                    return back()->with('error', 'Vous ne pouvez pas vous signaler vous-même.');
                }
                $reportedId = $reported->id;
            }
        }

        UserReport::create([
            'reporter_id' => $user->id,
            'reported_id' => $reportedId,
            'reason'      => $request->reason,
            'details'     => $request->details,
            'status'      => 'pending',
        ]);

        return redirect()->route('support.index', ['tab' => 'history'])
            ->with('support_success', 'Votre signalement a été envoyé. Notre équipe le traitera sous 10 jours ouvrés.');
    }
}
