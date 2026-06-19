<?php

namespace App\Console\Commands;

use App\Services\LeadScoreService;
use Illuminate\Console\Command;

class UpdateLeadScores extends Command
{
    protected $signature   = 'leads:update-scores';
    protected $description = 'Recalcule les points et badges de tous les utilisateurs (fenêtre 60 jours glissants)';

    public function handle(LeadScoreService $scorer): int
    {
        $this->info('Recalcul des scores en cours…');
        $count = $scorer->updateAll();
        $this->info("✓ {$count} utilisateur(s) mis à jour.");
        return 0;
    }
}
