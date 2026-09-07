<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Jobs\SendQueuedEmailJob;
use App\Mail\SystemNotificationMail;
use App\Models\EmailLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class EmailLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = EmailLog::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('recipient_email', 'like', "%{$s}%")
                ->orWhere('recipient_name', 'like', "%{$s}%")
                ->orWhere('subject', 'like', "%{$s}%")
            );
        }

        $logs = $query->orderByDesc('created_at')->paginate(30)->withQueryString();

        $stats = [
            'total'  => EmailLog::recent(7)->count(),
            'sent'   => EmailLog::recent(7)->where('status', 'sent')->count(),
            'failed' => EmailLog::recent(7)->where('status', 'failed')->count(),
            'pending'=> EmailLog::recent(7)->where('status', 'pending')->count(),
        ];

        $types = EmailLog::select('type')->distinct()->orderBy('type')->pluck('type');

        return view('admin.super_admin.email_logs.index', compact('logs', 'stats', 'types'));
    }

    /**
     * Renvoie un email qui a échoué (rejoue le même contenu — sujet, destinataire —
     * via une notification système générique, faute de pouvoir reconstruire le
     * mailable d'origine).
     */
    public function resend(EmailLog $log): JsonResponse
    {
        if ($log->status !== 'failed') {
            return response()->json(['message' => "Seuls les emails en échec peuvent être renvoyés."], 422);
        }

        try {
            Mail::to($log->recipient_email)->send(new SystemNotificationMail(
                recipientName: $log->recipient_name ?: $log->recipient_email,
                title:         $log->subject,
                body:          '<p>Ce message est un renvoi automatique suite à un échec de livraison précédent.</p>',
                actionLabel:   'Accéder à mon espace',
                actionUrl:     route('dashboard'),
            ));

            $log->update(['status' => 'sent', 'sent_at' => now(), 'error_message' => null]);

            return response()->json(['message' => 'Email renvoyé à ' . $log->recipient_email]);
        } catch (\Exception $e) {
            Log::warning('Email resend failed', ['log_id' => $log->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Échec du renvoi : ' . $e->getMessage()], 500);
        }
    }
}
