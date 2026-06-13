<?php

namespace App\Providers;

use App\Models\EmailSetting;
use App\Services\SmtpService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

/**
 * Loads SMTP configuration from the database and overrides Laravel's mail
 * config on every request boot — making SMTP fully admin-configurable.
 */
class MailConfigServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SmtpService::class);
    }

    public function boot(): void
    {
        try {
            // Skip during migrations / CLI commands that don't need mail
            if (! $this->tableExists()) {
                return;
            }

            $setting = EmailSetting::getActive();

            if ($setting) {
                app(SmtpService::class)->applyToMailConfig($setting);
            }
        } catch (\Exception $e) {
            Log::warning('MailConfigServiceProvider: could not load SMTP settings from DB', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function tableExists(): bool
    {
        return \Illuminate\Support\Facades\Schema::hasTable('email_settings');
    }
}
