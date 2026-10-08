<?php

namespace App\Console\Commands;

use App\Mail\SystemNotificationMail;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTrialReminders extends Command
{
    protected $signature = 'trial:send-reminders {--days=7 : Envoyer le rappel quand il reste ce nombre de jours ou moins}';

    protected $description = "Envoie un email de rappel aux membres dont l'essai « Full Access » Premium se termine bientôt";

    public function handle(): int
    {
        if (! (bool) SystemSetting::get('trial.enabled', false)) {
            $this->info("Essai Full Access désactivé — rien à envoyer.");
            return self::SUCCESS;
        }

        $months     = (int) SystemSetting::get('trial.duration_months', 3);
        $daysBefore = (int) $this->option('days');

        // Même critère d'éligibilité que la page admin Réglages > Essai : des membres "basic"
        // qui ne sont ni ambassadeur/consul approuvés, ni déjà sur un plan payant.
        $users = User::where('role', 'user')
            ->whereNull('trial_reminder_sent_at')
            ->where(fn ($q) => $q->whereNull('ambassador_status')->orWhere('ambassador_status', '!=', 'approved'))
            ->where(fn ($q) => $q->whereNull('consul_status')->orWhere('consul_status', '!=', 'approved'))
            ->whereDoesntHave('subscription', fn ($q) => $q->whereHas('plan', fn ($p) => $p->where('name', '!=', 'basic')))
            ->whereRaw('DATE_ADD(created_at, INTERVAL ? MONTH) > NOW()', [max($months, 0)])
            ->whereRaw('DATE_ADD(created_at, INTERVAL ? MONTH) <= DATE_ADD(NOW(), INTERVAL ? DAY)', [max($months, 0), $daysBefore])
            ->get();

        $count = 0;
        foreach ($users as $user) {
            $daysLeft = $user->trialDaysRemaining();

            try {
                Mail::to($user->email)->send(new SystemNotificationMail(
                    recipientName: $user->first_name,
                    title:         "Votre essai Premium se termine bientôt",
                    body:          "Il reste <strong>{$daysLeft} jour" . ($daysLeft > 1 ? 's' : '') . "</strong> à votre essai Premium gratuit. "
                                   . "Passez à un plan payant pour continuer à profiter de toutes les fonctionnalités Premium sans interruption.",
                    actionLabel:   'Voir les plans',
                    actionUrl:     route('upgrade'),
                    templateKey:   'trial_ending_soon',
                ));

                $user->update(['trial_reminder_sent_at' => now()]);
                $count++;
            } catch (\Throwable $e) {
                Log::warning('Trial reminder email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Rappel de fin d'essai envoyé à {$count} membre(s).");
        return self::SUCCESS;
    }
}
