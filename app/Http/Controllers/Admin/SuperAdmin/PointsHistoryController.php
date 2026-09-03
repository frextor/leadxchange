<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\PointsHistory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PointsHistoryController extends Controller
{
    /**
     * List all users with their points balance + purchase history.
     */
    public function index(Request $request): View
    {
        $query = User::query()
            ->select(['id', 'first_name', 'last_name', 'email', 'points_balance', 'created_at'])
            ->withCount(['pointsHistory as purchases_count' => function ($q) {
                $q->where('delta', '>', 0)->where('reason', 'like', '%achat%');
            }]);

        // Filter: low balance only
        if ($request->boolean('low_balance')) {
            $query->where('points_balance', '<=', 0);
        }

        // Filter: no purchase yet
        if ($request->boolean('no_purchase')) {
            $query->whereDoesntHave('pointsHistory', fn($q) => $q->where('delta', '>', 0)->where('reason', 'like', '%achat%'));
        }

        // Search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('first_name', 'like', "%{$s}%")
                ->orWhere('last_name',  'like', "%{$s}%")
                ->orWhere('email',      'like', "%{$s}%")
            );
        }

        $users = $query->orderBy('points_balance')->paginate(30)->withQueryString();

        // Stats
        $totalUsers      = User::count();
        $lowBalanceCount = User::where('points_balance', '<=', 0)->count();
        $noPurchaseCount = User::whereDoesntHave('pointsHistory', fn($q) => $q->where('delta', '>', 0)->where('reason', 'like', '%achat%'))->count();

        return view('admin.super_admin.points.index', compact(
            'users', 'totalUsers', 'lowBalanceCount', 'noPurchaseCount'
        ));
    }

    /**
     * Show detailed history for one user.
     */
    public function show(User $user): View
    {
        $history = PointsHistory::where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        $balance   = (int) ($user->points_balance ?? 0);
        $purchases = $history->where('delta', '>', 0)->where('reason', 'like', '*achat*')->count();

        return view('admin.super_admin.points.show', compact('user', 'history', 'balance'));
    }

    /**
     * Send a "buy points" reminder email to one user.
     */
    public function sendReminder(User $user): JsonResponse
    {
        $meta     = EmailTemplate::TEMPLATES['buy_points_reminder'];
        $template = EmailTemplate::where('key', 'buy_points_reminder')->first();

        $subject = $template?->subject ?: $meta['default_subject'];
        $body    = $template?->body    ?: $this->defaultBody();

        $vars = [
            'name'       => $user->first_name . ' ' . $user->last_name,
            'balance'    => (string) ((int) ($user->points_balance ?? 0)),
            'points_url' => url('/points'),
        ];

        $renderedSubject = EmailTemplate::interpolate($subject, $vars);
        $renderedBody    = EmailTemplate::interpolate($body, $vars);

        try {
            Mail::html(
                view('emails.db_template', [
                    'content'    => $renderedBody,
                    'emailTitle' => $renderedSubject,
                ])->render(),
                function ($message) use ($user, $renderedSubject) {
                    $message->to($user->email, $user->first_name . ' ' . $user->last_name)
                            ->subject($renderedSubject);
                }
            );

            return response()->json(['message' => 'Email envoyé à ' . $user->email]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    // ── Default body if template not yet created in DB ────────────────────────

    private function defaultBody(): string
    {
        return <<<'HTML'
<p>Bonjour <strong>{{name}}</strong>,</p>

<p>Nous avons remarqué que votre solde de points LeadXchange est actuellement de <strong>{{balance}} point(s)</strong>.</p>

<p>Les points vous permettent :</p>
<ul style="margin:12px 0;padding-left:24px;line-height:1.8;">
  <li>D'envoyer des leads à vos contacts</li>
  <li>D'accéder aux fonctionnalités premium de mise en relation</li>
  <li>De booster votre visibilité sur la plateforme</li>
</ul>

<p>Rechargez votre compte dès maintenant pour continuer à profiter pleinement de LeadXchange.</p>

<div style="text-align:center;margin:28px 0;">
  <a href="{{points_url}}" style="display:inline-block;padding:13px 32px;background:linear-gradient(135deg,#6366F1,#4F46E5);color:#fff;font-weight:700;font-size:15px;border-radius:10px;text-decoration:none;">
    ⭐ Acheter des points
  </a>
</div>

<p style="color:#94a3b8;font-size:13px;">Si vous avez des questions, notre équipe est disponible pour vous aider.</p>
HTML;
    }
}
