<?php

namespace App\Http\Controllers;

use App\Models\ConsulRequest;
use App\Models\Notification;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\ConsulService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConsulRequestController extends Controller
{
    public function __construct(private ConsulService $service) {}

    /** Consul → Ambassador request (user must already be Consul). */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ConsulRequest::class);

        try {
            $this->service->request($request->user());
            return back()->with('success', 'Votre demande de rôle Ambassadeur a été envoyée. Vous serez notifié(e) de la décision.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** Premium → Consul promotion request (sets consul_status = pending for admin review). */
    public function requestConsulPromotion(Request $request): RedirectResponse
    {
        $user = $request->user()->loadMissing('subscription.plan');

        if (! $user->hasPaidPlan()) {
            return back()->with('error', 'Un abonnement payant est requis pour demander le rôle Consul.');
        }

        if ($user->isConsul()) {
            return back()->with('error', 'Vous êtes déjà Consul.');
        }

        if ($user->hasPendingConsulPromotion()) {
            return back()->with('error', 'Vous avez déjà une demande Consul en attente.');
        }

        if ($user->consul_status === 'rejected' && $user->consul_rejected_at) {
            $cooldown = (int) SystemSetting::get('consul_rejection_cooldown_days', 30);
            $retryDate = $user->consul_rejected_at->addDays($cooldown);
            if (now()->lt($retryDate)) {
                return back()->with('error', 'Votre demande a été refusée. Vous pourrez re-soumettre à partir du ' . $retryDate->format('d/m/Y') . '.');
            }
        }

        $user->update(['consul_status' => 'pending']);

        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        foreach ($admins as $admin) {
            try {
                Notification::storeForUser(
                    $admin,
                    'consul_request_submitted',
                    'Nouvelle demande Consul',
                    "{$user->first_name} {$user->last_name} demande le rôle Consul.",
                    ['url' => route('admin.super.consuls.manage')]
                );
            } catch (\Throwable) {}
        }

        return back()->with('success', 'Votre demande de rôle Consul a été envoyée. L\'administration vous contactera.');
    }
}
