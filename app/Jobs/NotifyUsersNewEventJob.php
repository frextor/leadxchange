<?php

namespace App\Jobs;

use App\Mail\NewEventMail;
use App\Models\Event;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyUsersNewEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 300;

    public function __construct(public readonly Event $event) {}

    public function handle(): void
    {
        $this->event->loadMissing(['city.country', 'sector', 'creator']);

        $cityId    = $this->event->city_id;
        $countryId = $this->event->city?->country_id;
        $sectorId  = $this->event->sector_id;
        $creatorId = $this->event->created_by;

        $subjectFallback = 'Événement à venir : ' . $this->event->title;

        $query = User::where('role', 'user')
            ->whereNotNull('email_verified_at')
            ->where('id', '!=', $creatorId)
            ->where(function ($q) use ($cityId, $countryId, $sectorId) {

                // ① Same city → always notify
                if ($cityId) {
                    $q->where('city_id', $cityId);
                }

                // ② Same country + matching sector in profile
                if ($countryId && $sectorId) {
                    $q->orWhere(function ($sq) use ($countryId, $sectorId) {
                        $sq->whereHas('city', fn ($cq) => $cq->where('country_id', $countryId))
                           ->whereHas('profile', fn ($pq) => $pq->whereJsonContains('sector_ids', $sectorId));
                    });
                }

                // ③ Same country + matching interest
                if ($countryId && $sectorId) {
                    $q->orWhere(function ($sq) use ($countryId, $sectorId) {
                        $sq->whereHas('city', fn ($cq) => $cq->where('country_id', $countryId))
                           ->whereHas('interests', fn ($iq) => $iq->where('sector_id', $sectorId));
                    });
                }

                // ④ Ambassador/Consul always notified (same country) — they organise their region
                if ($countryId) {
                    $q->orWhere(function ($sq) use ($countryId) {
                        $sq->whereHas('city', fn ($cq) => $cq->where('country_id', $countryId))
                           ->where('ambassador_status', 'approved');
                    });
                }
            });

        $sent     = 0;
        $firebase = app(FirebaseService::class);

        $query->select(['id', 'first_name', 'last_name', 'email', 'ambassador_status'])
              ->chunk(50, function ($users) use ($subjectFallback, $firebase, &$sent) {
                  foreach ($users as $user) {
                      try {
                          SendQueuedEmailJob::dispatch(
                              to:       $user->email,
                              subject:  $subjectFallback,
                              type:     'new_event',
                              mailable: new NewEventMail($this->event, $user),
                              toName:   $user->first_name . ' ' . $user->last_name,
                              metadata: [
                                  'event_id' => $this->event->id,
                                  'user_id'  => $user->id,
                              ],
                          );
                          $firebase->sendNewRegionalEventNotification($user, $this->event);
                          $sent++;
                      } catch (\Exception $e) {
                          Log::warning('NotifyUsersNewEventJob: échec envoi', [
                              'user_id'  => $user->id,
                              'event_id' => $this->event->id,
                              'error'    => $e->getMessage(),
                          ]);
                      }
                  }
              });

        Log::info('NotifyUsersNewEventJob terminé', [
            'event_id' => $this->event->id,
            'sent'     => $sent,
        ]);
    }
}
