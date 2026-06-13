<?php

namespace App\Http\Controllers;

use App\Models\EnterpriseInvitation;
use App\Models\Subscription;
use App\Services\EnterpriseInvitationService;
use Illuminate\Http\Request;

class EnterpriseController extends Controller
{
    public function __construct(private EnterpriseInvitationService $service) {}

    public function index(Request $request)
    {
        $user = $request->user();

        // Invitations sent by this owner
        $sentInvitations = EnterpriseInvitation::with(['acceptedUser:id,first_name,last_name,email'])
            ->where('owner_id', $user->id)
            ->latest()
            ->get();

        // Invitations received (by email)
        $receivedInvitations = EnterpriseInvitation::with(['owner:id,first_name,last_name,email'])
            ->where('email', $user->email)
            ->latest()
            ->get();

        // Active enterprise subscription
        $subscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('plan')
            ->first();

        $isEnterpriseOwner = $subscription && ($subscription->plan->max_users ?? 1) > 1;
        $seatUsed = $sentInvitations->where('status', 'accepted')->count() + 1; // +1 for owner
        $seatTotal = $subscription?->plan?->max_users ?? 1;

        return view('enterprise.index', compact(
            'sentInvitations', 'receivedInvitations',
            'isEnterpriseOwner', 'subscription', 'seatUsed', 'seatTotal'
        ));
    }

    public function store(Request $request)
    {
        $request->validate(['email' => ['required', 'email', 'max:200']]);

        try {
            $this->service->sendInvitation($request->user(), $request->email);
            return back()->with('success', "Invitation envoyée à {$request->email}.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function accept(Request $request, string $token)
    {
        try {
            $this->service->acceptInvitation($request->user(), $token);
            return redirect()->route('dashboard')->with('success', 'Vous avez rejoint l\'équipe !');
        } catch (\Exception $e) {
            return redirect()->route('enterprise.index')->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function revoke(Request $request, EnterpriseInvitation $invitation)
    {
        abort_if($invitation->owner_id !== $request->user()->id, 403);
        $invitation->update(['status' => 'revoked']);
        return back()->with('success', 'Invitation révoquée.');
    }
}
