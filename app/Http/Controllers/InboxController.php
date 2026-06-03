<?php

namespace App\Http\Controllers;

use App\Models\InboxItem;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();

        // Load ALL items (including archived) — Alpine handles client-side filtering
        $items = InboxItem::where('user_id', $user->id)
            ->orderByDesc('ts')
            ->get();

        $connectionIds = $user->connectionIds();
        $connections   = User::with('company')
            ->whereIn('id', $connectionIds)
            ->select('id', 'first_name', 'last_name', 'points_balance')
            ->orderBy('first_name')
            ->get()
            ->map(fn($u) => [
                'id'       => $u->id,
                'name'     => trim($u->first_name . ' ' . $u->last_name),
                'initials' => strtoupper(mb_substr($u->first_name, 0, 1) . mb_substr($u->last_name, 0, 1)),
                'company'  => $u->company?->name,
                'points'   => $u->points_balance ?? 0,
            ]);

        // 6 most recent leads the user is involved in (for context picker)
        $leads = Lead::where(fn($q) => $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id))
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->map(fn($l) => [
                'id'      => $l->id,
                'ref'     => 'L-' . $l->id,
                'company' => $l->company_name,
                'contact' => $l->contact_name,
                'heat'    => $l->qualification ?? 'cold',
            ]);

        return view('inbox.index', [
            'itemsData'   => $items->map(fn($i) => $this->format($i))->values(),
            'connections' => $connections,
            'leads'       => $leads,
        ]);
    }

    public function read(Request $request, int $id)
    {
        $item = InboxItem::where('user_id', $request->user()->id)->findOrFail($id);
        $item->update(['read' => !$item->read]);

        return response()->json(['ok' => true, 'read' => $item->read]);
    }

    public function archive(Request $request, int $id)
    {
        $item = InboxItem::where('user_id', $request->user()->id)->findOrFail($id);
        $item->update(['archived' => true]);

        return response()->json(['ok' => true]);
    }

    public function reply(Request $request, int $id)
    {
        $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $sender = $request->user();
        $item   = InboxItem::where('user_id', $sender->id)->findOrFail($id);
        $item->update(['read' => true]);

        if ($item->actor_id) {
            InboxItem::create([
                'user_id'       => $item->actor_id,
                'kind'          => 'message',
                'type'          => 'message',
                'read'          => false,
                'archived'      => false,
                'ts'            => now(),
                'actor_id'      => $sender->id,
                'actor_name'    => trim($sender->first_name . ' ' . $sender->last_name),
                'actor_title'   => $sender->profile?->job_title,
                'actor_company' => $sender->company?->name,
                'title'         => 'Re : ' . $item->title,
                'preview'       => mb_strimwidth($request->body, 0, 80, '…'),
                'body'          => $request->body,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function compose(Request $request)
    {
        $request->validate([
            'recipient_ids'   => ['required', 'array', 'min:1'],
            'recipient_ids.*' => ['integer', 'exists:users,id'],
            'subject'         => ['nullable', 'string', 'max:150'],
            'body'            => ['required', 'string', 'max:2000'],
            'lead_id'         => ['nullable', 'integer', 'exists:leads,id'],
            'urgent'          => ['nullable', 'boolean'],
        ]);

        $sender = $request->user();
        $lead   = $request->lead_id ? Lead::find($request->lead_id) : null;
        $count  = 0;

        foreach ($request->recipient_ids as $recipientId) {
            $recipient = User::find($recipientId);
            if (!$recipient) continue;

            InboxItem::create([
                'user_id'       => $recipientId,
                'kind'          => 'message',
                'type'          => 'message',
                'read'          => false,
                'archived'      => false,
                'ts'            => now(),
                'actor_id'      => $sender->id,
                'actor_name'    => trim($sender->first_name . ' ' . $sender->last_name),
                'actor_title'   => $sender->profile?->job_title,
                'actor_company' => $sender->company?->name,
                'lead_ref'      => $lead ? 'L-' . $lead->id : null,
                'lead_id'       => $lead?->id,
                'title'         => $request->subject ?: 'Message sans sujet',
                'preview'       => mb_strimwidth($request->body, 0, 80, '…'),
                'body'          => $request->body,
            ]);
            $count++;
        }

        return response()->json(['ok' => true, 'count' => $count]);
    }

    private function format(InboxItem $item): array
    {
        return [
            'id'            => $item->id,
            'ref_id'        => 'I-' . $item->id,
            'kind'          => $item->kind,
            'type'          => $item->type,
            'read'          => $item->read,
            'archived'      => $item->archived,
            'ts'            => $item->ts->toIso8601String(),
            'actor_id'      => $item->actor_id,
            'actor_name'    => $item->actor_name,
            'actor_title'   => $item->actor_title,
            'actor_company' => $item->actor_company,
            'lead_ref'      => $item->lead_ref,
            'lead_id'       => $item->lead_id,
            'title'         => $item->title,
            'preview'       => $item->preview,
            'body'          => $item->body,
        ];
    }
}
