<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ambassador\Concerns\ScopesAmbassadorRegion;
use App\Models\AmbassadorAnnouncement;
use App\Services\AmbassadorService;
use App\Services\FirebaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunicationController extends Controller
{
    use ScopesAmbassadorRegion;

    public function __construct(
        private AmbassadorService $service,
        private FirebaseService $firebase,
    ) {}

    public function index(Request $request): View
    {
        $ambassador    = $request->user()->load('city', 'region');
        $regionName    = $this->regionName($ambassador);
        $membersCount  = $this->service->regionMembersCount($ambassador);

        $announcements = AmbassadorAnnouncement::where('ambassador_id', $ambassador->id)
            ->latest()
            ->paginate(15);

        return view('ambassador.communication.index', compact(
            'ambassador', 'regionName', 'membersCount', 'announcements'
        ));
    }

    public function announce(Request $request): RedirectResponse
    {
        $ambassador = $request->user()->load('city', 'region');

        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'body'    => 'required|string|max:5000',
            'type'    => 'required|in:event_reminder,welcome,networking,general',
        ]);

        // Get region members
        $members = $this->service->regionMembersQuery($ambassador)->get();

        // Store announcement record
        AmbassadorAnnouncement::create([
            'ambassador_id'   => $ambassador->id,
            'region_id'       => $ambassador->region_id,
            'subject'         => $data['subject'],
            'body'            => $data['body'],
            'type'            => $data['type'],
            'recipients_count'=> $members->count(),
            'sent_at'         => now(),
        ]);

        // Send Firebase push notification to subscribed members
        $typeEmoji = ['general' => '📢', 'event_reminder' => '📅', 'welcome' => '👋', 'networking' => '🤝'];
        $title     = ($typeEmoji[$data['type']] ?? '📢') . ' ' . $data['subject'];
        $this->firebase->sendAmbassadorAnnouncement($members, $title, $data['body']);

        return back()->with('success', "Annonce envoyée à {$members->count()} membres de votre région.");
    }
}
