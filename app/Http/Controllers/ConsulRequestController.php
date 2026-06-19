<?php

namespace App\Http\Controllers;

use App\Models\ConsulRequest;
use App\Services\ConsulService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConsulRequestController extends Controller
{
    public function __construct(private ConsulService $service) {}

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ConsulRequest::class);

        try {
            $this->service->request($request->user());
            return back()->with('success', 'Votre demande de rôle Consul a été envoyée. Vous serez notifié(e) de la décision.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
