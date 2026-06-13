<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\EmailSetting;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Auth\LoginAuthenticator;

class SmtpService
{
    // ── Config management ─────────────────────────────────────────────────────

    public function getActiveSetting(): ?EmailSetting
    {
        return EmailSetting::getActive();
    }

    public function applyToMailConfig(?EmailSetting $setting = null): void
    {
        $setting ??= $this->getActiveSetting();

        if (! $setting) {
            return;
        }

        config([
            'mail.default'                 => 'smtp',
            'mail.mailers.smtp.host'       => $setting->host,
            'mail.mailers.smtp.port'       => $setting->port,
            'mail.mailers.smtp.encryption' => $setting->encryption === 'none' ? null : $setting->encryption,
            'mail.mailers.smtp.username'   => $setting->username,
            'mail.mailers.smtp.password'   => $setting->getDecryptedPassword(),
            'mail.from.address'            => $setting->from_address,
            'mail.from.name'               => $setting->from_name,
        ]);
    }

    // ── SMTP connectivity test ────────────────────────────────────────────────

    public function testConnection(?EmailSetting $setting = null): array
    {
        $setting ??= $this->getActiveSetting();

        if (! $setting) {
            return ['success' => false, 'message' => 'Aucune configuration SMTP trouvée.'];
        }

        try {
            $encryption = $setting->encryption === 'none' ? false : $setting->encryption;
            $transport  = new EsmtpTransport($setting->host, $setting->port, $encryption === 'ssl');

            $transport->setUsername($setting->username ?? '');
            $transport->setPassword($setting->getDecryptedPassword() ?? '');

            $transport->start();
            $transport->stop();

            return [
                'success' => true,
                'message' => "Connexion SMTP réussie vers {$setting->host}:{$setting->port}",
                'latency' => null,
            ];
        } catch (\Exception $e) {
            Log::error('SMTP connection test failed', [
                'host'    => $setting->host,
                'port'    => $setting->port,
                'error'   => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    // ── Email sending (with log) ──────────────────────────────────────────────

    public function send(
        string $to,
        string $subject,
        string $type,
        \Illuminate\Mail\Mailable $mailable,
        ?string $toName = null,
        array   $metadata = [],
    ): EmailLog {
        $log = EmailLog::create([
            'recipient_email' => $to,
            'recipient_name'  => $toName,
            'subject'         => $subject,
            'type'            => $type,
            'status'          => 'pending',
            'metadata'        => $metadata,
            'mailer'          => 'smtp',
        ]);

        try {
            Mail::to($to)->send($mailable);

            $log->update(['status' => 'sent', 'sent_at' => now()]);

            Log::info('Email sent', [
                'to'      => $to,
                'subject' => $subject,
                'type'    => $type,
                'log_id'  => $log->id,
            ]);
        } catch (\Exception $e) {
            $log->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Email send failed', [
                'to'      => $to,
                'subject' => $subject,
                'type'    => $type,
                'error'   => $e->getMessage(),
                'log_id'  => $log->id,
            ]);

            throw $e;
        }

        return $log;
    }

    // ── Diagnostics ───────────────────────────────────────────────────────────

    public function getDiagnostics(): array
    {
        $setting = $this->getActiveSetting();

        $stats = [
            'total'   => EmailLog::recent(30)->count(),
            'sent'    => EmailLog::recent(30)->where('status', 'sent')->count(),
            'failed'  => EmailLog::recent(30)->where('status', 'failed')->count(),
            'pending' => EmailLog::recent(30)->where('status', 'pending')->count(),
        ];

        $lastError = EmailLog::failed()->latest()->first();
        $lastSent  = EmailLog::where('status', 'sent')->latest('sent_at')->first();

        return compact('setting', 'stats', 'lastError', 'lastSent');
    }
}
