<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SystemNotificationMail;
use App\Models\EnterpriseQuoteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnterpriseController extends Controller
{
    /**
     * POST /api/enterprise/request-quote
     *
     * Submit an enterprise quote request from the mobile app.
     */
    public function requestQuote(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_name' => ['nullable', 'string', 'max:100'],
            'seats_needed' => ['required', 'integer', 'min:2', 'max:500'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'message'      => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $companyName = $data['company_name'] ?? $user->company?->name ?? "{$user->first_name} {$user->last_name}";

        EnterpriseQuoteRequest::create([
            'user_id'      => $user->id,
            'company_name' => $companyName,
            'seats_needed' => $data['seats_needed'],
            'phone'        => $data['phone'] ?? null,
            'message'      => $data['message'] ?? null,
            'status'       => 'pending',
        ]);

        $body = "<p>Nouvelle demande de devis Pack Entreprise :</p>
<ul>
<li><strong>Entreprise :</strong> {$companyName}</li>
<li><strong>Utilisateurs souhaités :</strong> {$data['seats_needed']} licences</li>
<li><strong>Demandeur :</strong> {$user->first_name} {$user->last_name} ({$user->email})</li>
<li><strong>Téléphone :</strong> " . ($data['phone'] ?: '—') . "</li>
<li><strong>Message :</strong> " . nl2br(htmlspecialchars($data['message'] ?? '')) . "</li>
</ul>
<p><a href=\"" . route('admin.super.enterprise.quotes') . "\">Voir les demandes dans l'administration →</a></p>";

        try {
            $adminEmail = env('ADMIN_EMAIL', config('mail.from.address'));
            Mail::to($adminEmail)->send(new SystemNotificationMail(
                recipientName: 'Équipe LeadXchange',
                title:         'Demande de devis Pack Entreprise — ' . $companyName,
                body:          $body,
                actionLabel:   'Voir les demandes',
                actionUrl:     route('admin.super.enterprise.quotes'),
            ));
        } catch (\Exception $e) {
            Log::warning('Enterprise quote request email failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Quote request submitted successfully.',
        ], 201);
    }
}
