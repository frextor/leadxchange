<?php

namespace App\Jobs;

use App\Mail\NewGroupMail;
use App\Models\Group;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyUsersNewGroupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 300;

    public function __construct(public readonly Group $group) {}

    public function handle(): void
    {
        $this->group->loadMissing(['city.country', 'sector', 'creator']);

        $cityId    = $this->group->city_id;
        $countryId = $this->group->city?->country_id;
        $sectorId  = $this->group->sector_id;
        $creatorId = $this->group->created_by;

        // Resolve email subject once (from DB template, with sample creator name)
        $subjectFallback = 'Nouveau groupe LeadXchange : ' . $this->group->name;

        $query = User::where('role', 'user')
            ->whereNotNull('email_verified_at')
            ->where('id', '!=', $creatorId)
            ->where(function ($q) use ($cityId, $countryId, $sectorId) {

                // ① Same city → always notify
                if ($cityId) {
                    $q->where('city_id', $cityId);
                }

                // ② Same country + matching sector in profile → notify if interested
                if ($countryId && $sectorId) {
                    $q->orWhere(function ($sq) use ($countryId, $sectorId) {
                        $sq->whereHas('city', fn ($cq) => $cq->where('country_id', $countryId))
                           ->whereHas('profile', fn ($pq) => $pq->whereJsonContains('sector_ids', $sectorId));
                    });
                }

                // ③ Same country + matching interest (via sector linked to interest name)
                if ($countryId && $sectorId) {
                    $q->orWhere(function ($sq) use ($countryId, $sectorId) {
                        $sq->whereHas('city', fn ($cq) => $cq->where('country_id', $countryId))
                           ->whereHas('interests', fn ($iq) => $iq->where('sector_id', $sectorId));
                    });
                }
            });

        $sent = 0;

        $query->select(['id', 'first_name', 'last_name', 'email'])
              ->chunk(50, function ($users) use ($subjectFallback, &$sent) {
                  foreach ($users as $user) {
                      try {
                          SendQueuedEmailJob::dispatch(
                              to:       $user->email,
                              subject:  $subjectFallback,
                              type:     'new_group',
                              mailable: new NewGroupMail($this->group, $user),
                              toName:   $user->first_name . ' ' . $user->last_name,
                              metadata: [
                                  'group_id' => $this->group->id,
                                  'user_id'  => $user->id,
                              ],
                          );
                          $sent++;
                      } catch (\Exception $e) {
                          Log::warning('NotifyUsersNewGroupJob: échec envoi', [
                              'user_id'  => $user->id,
                              'group_id' => $this->group->id,
                              'error'    => $e->getMessage(),
                          ]);
                      }
                  }
              });

        Log::info('NotifyUsersNewGroupJob terminé', [
            'group_id' => $this->group->id,
            'sent'     => $sent,
        ]);
    }
}
