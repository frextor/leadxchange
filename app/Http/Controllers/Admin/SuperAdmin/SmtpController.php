<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendTestEmailRequest;
use App\Http\Requests\Admin\UpdateSmtpSettingsRequest;
use App\Mail\SystemNotificationMail;
use App\Models\EmailLog;
use App\Models\EmailSetting;
use App\Services\SmtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmtpController extends Controller
{
    public function __construct(private readonly SmtpService $smtp) {}

    // ── Settings + diagnostics page ───────────────────────────────────────────

    public function index(Request $request): View
    {
        $setting     = EmailSetting::getActive() ?? new EmailSetting();
        $diagnostics = $this->smtp->getDiagnostics();
        $logs        = EmailLog::latest()->paginate(20);
        $tab         = $request->get('tab', 'settings');

        return view('admin.super_admin.smtp.index', compact('setting', 'diagnostics', 'logs', 'tab'));
    }

    // ── Save SMTP settings ────────────────────────────────────────────────────

    public function update(UpdateSmtpSettingsRequest $request): RedirectResponse
    {
        $setting = EmailSetting::firstOrNew(['name' => 'default']);

        $data = $request->validated();

        // Only update password if a new one was provided
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $setting->fill($data)->save();

        // Force reload of mail config for this request
        $this->smtp->applyToMailConfig($setting);

        return redirect()->route('admin.super.smtp.index', ['tab' => 'settings'])
            ->with('success', 'Configuration SMTP enregistrée avec succès.');
    }

    // ── Test SMTP connection ──────────────────────────────────────────────────

    public function testConnection(): RedirectResponse
    {
        $result = $this->smtp->testConnection();

        if ($result['success']) {
            return redirect()->route('admin.super.smtp.index', ['tab' => 'diagnostics'])
                ->with('success', '✅ ' . $result['message']);
        }

        return redirect()->route('admin.super.smtp.index', ['tab' => 'diagnostics'])
            ->with('error', '❌ Connexion SMTP échouée : ' . $result['message']);
    }

    // ── Send test email ───────────────────────────────────────────────────────

    public function sendTest(SendTestEmailRequest $request): RedirectResponse
    {
        $to      = $request->validated('test_email');
        $setting = EmailSetting::getActive();

        if (! $setting) {
            return back()->with('error', 'Aucune configuration SMTP active. Configurez le SMTP d\'abord.');
        }

        try {
            $this->smtp->applyToMailConfig($setting);

            $mailable = new SystemNotificationMail(
                recipientName: 'Administrateur',
                title: 'Test SMTP — LeadXchange',
                body: 'Cet email confirme que votre configuration SMTP IONOS fonctionne correctement.',
                actionLabel: 'Accéder à l\'administration',
                actionUrl: route('admin.super.smtp.index'),
            );

            $this->smtp->send(
                to: $to,
                subject: 'Test SMTP — LeadXchange',
                type: 'test',
                mailable: $mailable,
                toName: 'Administrateur',
                metadata: ['sent_by' => auth()->id(), 'ip' => request()->ip()],
            );

            return redirect()->route('admin.super.smtp.index', ['tab' => 'diagnostics'])
                ->with('success', "✅ Email de test envoyé à {$to} avec succès.");
        } catch (\Exception $e) {
            return redirect()->route('admin.super.smtp.index', ['tab' => 'diagnostics'])
                ->with('error', '❌ Échec d\'envoi : ' . $e->getMessage());
        }
    }

    // ── Clear email logs ──────────────────────────────────────────────────────

    public function clearLogs(Request $request): RedirectResponse
    {
        $days = (int) $request->input('days', 30);
        $count = EmailLog::where('created_at', '<', now()->subDays($days))->delete();

        return redirect()->route('admin.super.smtp.index', ['tab' => 'diagnostics'])
            ->with('success', "{$count} entrées de log supprimées (> {$days} jours).");
    }
}
