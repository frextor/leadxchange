@extends('admin.layouts.admin')
@section('title', 'Demandes Pack Entreprise')

@section('content')
<div class="p-6">

    <div class="mb-5 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                Pack Entreprise
                @if($pendingCount > 0)
                <span class="text-sm font-bold px-2.5 py-0.5 rounded-full text-white" style="background:#EF4444;">{{ $pendingCount }}</span>
                @endif
            </h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ $quotes->count() }} demande{{ $quotes->count() > 1 ? 's' : '' }} · {{ $licenses->count() }} pack{{ $licenses->count() > 1 ? 's' : '' }}</p>
        </div>
        <a href="{{ route('admin.super.enterprise.create') }}"
           class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white transition"
           style="background:linear-gradient(135deg,#6366F1,#4338CA);">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Créer une licence
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-5 py-3 text-sm flex items-center gap-2">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- ── Tabs ──────────────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 mb-5 bg-white border border-gray-100 rounded-2xl shadow-sm px-2 py-1.5 w-fit">
        <button type="button" onclick="switchEntTab('quotes', this)" class="ent-tab-btn active"
                data-color="#6366F1">
            Demandes de devis
            @if($pendingCount > 0)<span class="ent-tab-badge" style="background:#EF4444;">{{ $pendingCount }}</span>@endif
        </button>
        <button type="button" onclick="switchEntTab('packs', this)" class="ent-tab-btn" data-color="#4338CA">
            Packs Entreprise
            <span class="ent-tab-badge" style="background:#94A3B8;">{{ $licenses->count() }}</span>
        </button>
    </div>

    <style>
        .ent-tab-btn { display:flex; align-items:center; gap:6px; padding:8px 16px; border-radius:12px; font-size:13px; font-weight:600; color:#64748B; background:transparent; transition:all .15s; }
        .ent-tab-btn.active { color:#fff; background:var(--tab-color,#6366F1); }
        .ent-tab-badge { font-size:10px; font-weight:700; padding:1px 6px; border-radius:99px; color:#fff; }
    </style>

    {{-- ══ PANEL: Packs Entreprise (Actifs / Expirés) ══════════════════════════ --}}
    <div id="ent-panel-packs" class="hidden">

        <div class="flex items-center gap-2 mb-4">
            <button type="button" onclick="filterPacks('all', this)" class="pack-filter-btn active">Tous ({{ $licenses->count() }})</button>
            <button type="button" onclick="filterPacks('active', this)" class="pack-filter-btn">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span> Actifs ({{ $activeLicensesCount }})
            </button>
            <button type="button" onclick="filterPacks('expired', this)" class="pack-filter-btn">
                <span class="w-1.5 h-1.5 rounded-full bg-red-400 inline-block"></span> Expirés ({{ $expiredLicensesCount }})
            </button>
        </div>
        <style>
            .pack-filter-btn { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:10px; font-size:12.5px; font-weight:600; color:#64748B; background:#F8FAFC; border:1px solid #E2E8F0; transition:all .15s; }
            .pack-filter-btn.active { background:#EEF2FF; border-color:#C7D2FE; color:#4338CA; }
        </style>

        @if($licenses->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-16 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-2xl flex items-center justify-center bg-indigo-50">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.8"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-500">Aucun pack Entreprise pour le moment</p>
        </div>
        @else
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Entreprise</th>
                            <th class="text-left px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Titulaire</th>
                            <th class="text-center px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Sièges</th>
                            <th class="text-center px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Statut</th>
                            <th class="text-center px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Expiration</th>
                            <th class="text-right px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($licenses as $license)
                        <tr class="pack-row" data-status="{{ $license->pack_status }}">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0"
                                         style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                                        {{ strtoupper(substr($license->company_name,0,2)) }}
                                    </div>
                                    <span class="font-semibold text-gray-800">{{ $license->company_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ $license->holder ? $license->holder->first_name . ' ' . $license->holder->last_name : '—' }}
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-gray-700">{{ $license->seats_used }}/{{ $license->seats_total }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($license->pack_status === 'active')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Actif
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-50 text-red-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Expiré
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-xs text-gray-400">
                                {{ $license->expires_at ? $license->expires_at->format('d/m/Y') : 'Sans expiration' }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($license->pending_members->isNotEmpty())
                                    <button type="button" onclick="document.getElementById('pending-{{ $license->id }}').classList.toggle('hidden')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border border-amber-200 text-amber-700 bg-amber-50 hover:bg-amber-100 transition">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                        {{ $license->pending_members->count() }} en attente
                                    </button>
                                    @endif
                                    <a href="{{ route('admin.super.enterprise.edit', $license) }}"
                                       class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                                        Gérer
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @if($license->pending_members->isNotEmpty())
                        <tr id="pending-{{ $license->id }}" class="hidden pack-subrow" data-parent-status="{{ $license->pack_status }}">
                            <td colspan="6" class="px-5 py-3 bg-amber-50/50 border-t border-amber-100">
                                <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wide mb-2">Invitations en attente d'acceptation</p>
                                <div class="space-y-1.5">
                                    @foreach($license->pending_members as $member)
                                    <div class="flex items-center justify-between gap-3 bg-white rounded-lg border border-amber-100 px-3 py-2">
                                        <span class="text-sm text-gray-700">{{ $member->email }}</span>
                                        <form method="POST" action="{{ route('admin.super.enterprise.invitations.resend', $member) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold border border-indigo-200 text-indigo-600 hover:bg-indigo-50 transition">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                                                Renvoyer l'invitation
                                            </button>
                                        </form>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- ══ PANEL: Demandes de devis ══════════════════════════════════════════════ --}}
    <div id="ent-panel-quotes">

    @if($quotes->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-16 text-center">
        <div class="w-14 h-14 mx-auto mb-4 rounded-2xl flex items-center justify-center bg-indigo-50">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <p class="text-sm font-semibold text-gray-500">Aucune demande pour l'instant</p>
        <p class="text-xs text-gray-400 mt-1">Les demandes de devis apparaîtront ici dès qu'un utilisateur en soumet une.</p>
    </div>
    @else
    <div class="space-y-4">
        @foreach($quotes as $quote)
        @php $st = \App\Models\EnterpriseQuoteRequest::$statusLabels[$quote->status] ?? ['label' => $quote->status, 'bg' => '#F3F4F6', 'color' => '#6B7280']; @endphp
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 flex flex-wrap items-start gap-4">

                {{-- User info --}}
                <div class="flex items-center gap-3 flex-shrink-0">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                         style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                        {{ strtoupper(substr($quote->user->first_name,0,1)) }}{{ strtoupper(substr($quote->user->last_name,0,1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $quote->user->first_name }} {{ $quote->user->last_name }}</p>
                        <p class="text-xs text-gray-400">{{ $quote->user->email }}</p>
                    </div>
                </div>

                {{-- Request details --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <p class="text-sm font-bold text-gray-900">{{ $quote->company_name }}</p>
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                              style="background:{{ $st['bg'] }};color:{{ $st['color'] }};">
                            {{ $st['label'] }}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-3 text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            {{ $quote->seats_needed }} licences souhaitées
                        </span>
                        @if($quote->phone)
                        <span class="flex items-center gap-1">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.59 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8a16 16 0 0 0 6 6l.27-.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            {{ $quote->phone }}
                        </span>
                        @endif
                        <span class="flex items-center gap-1">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            {{ $quote->created_at->format('d/m/Y à H:i') }}
                        </span>
                    </div>
                    @if($quote->message)
                    <p class="text-xs text-gray-600 mt-2 bg-gray-50 rounded-lg px-3 py-2">{{ $quote->message }}</p>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-shrink-0 flex-wrap">
                    <a href="{{ route('admin.users.show', $quote->user) }}"
                       class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                        Voir profil
                    </a>
                    @if(!$quote->hasProposal() && !in_array($quote->status, ['closed', 'converted']))
                    <a href="{{ route('admin.super.enterprise.quotes.proposal.form', $quote) }}"
                       class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg text-white transition"
                       style="background:linear-gradient(135deg,#059669,#047857);" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Générer une proposition
                    </a>
                    @else
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-violet-100 text-violet-700">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/></svg>
                        Proposition envoyée {{ $quote->proposal_sent_at?->format('d/m') }}
                    </span>
                    @if($quote->isAccepted())
                    <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-700">✓ Acceptée</span>
                    @elseif($quote->status === 'contacted')
                    {{-- Client a confirmé un virement → admin doit approuver --}}
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-amber-100 text-amber-700">💳 Virement en attente</span>
                    <form method="POST" action="{{ route('admin.super.enterprise.quotes.approve-wire', $quote) }}" class="inline"
                          onsubmit="return confirm('Confirmer la réception du virement et activer le Pack Entreprise pour {{ addslashes($quote->company_name) }} ?')">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg text-white transition"
                                style="background:linear-gradient(135deg,#059669,#047857);">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 11 3 3L22 4"/></svg>
                            Approuver le virement
                        </button>
                    </form>
                    @elseif(!$quote->stripe_payment_link)
                    {{-- Proposition envoyée mais sans lien Stripe → bouton régénérer --}}
                    <form method="POST" action="{{ route('admin.super.enterprise.quotes.proposal.regenerate', $quote) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100 transition">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                            Régénérer le lien Stripe
                        </button>
                    </form>
                    @endif
                    @endif
                    <button type="button" onclick="toggleEdit({{ $quote->id }})"
                            class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
                        Modifier
                    </button>
                </div>
            </div>

            {{-- Inline edit form (hidden by default) --}}
            <div id="edit-{{ $quote->id }}" class="hidden border-t border-gray-100 bg-gray-50 px-5 py-4">
                <form method="POST" action="{{ route('admin.super.enterprise.quotes.update', $quote) }}" class="flex flex-wrap gap-4 items-end">
                    @csrf @method('PATCH')
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Statut</label>
                        <select name="status" class="rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2" style="--tw-ring-color:#6366F150;">
                            @foreach(\App\Models\EnterpriseQuoteRequest::$statusLabels as $val => $cfg)
                            <option value="{{ $val }}" {{ $quote->status === $val ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Notes admin</label>
                        <input type="text" name="admin_notes" value="{{ $quote->admin_notes }}" placeholder="Notes internes…"
                               class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2" style="--tw-ring-color:#6366F150;">
                    </div>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-white transition"
                            style="background:#6366F1;" onmouseover="this.style.background='#4338CA'" onmouseout="this.style.background='#6366F1'">
                        Enregistrer
                    </button>
                    <button type="button" onclick="toggleEdit({{ $quote->id }})"
                            class="px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-white transition">
                        Annuler
                    </button>
                </form>
                @if($quote->admin_notes)
                <p class="text-xs text-gray-500 mt-2">Note actuelle : <em>{{ $quote->admin_notes }}</em></p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    </div>{{-- /#ent-panel-quotes --}}

</div>

<script>
function toggleEdit(id) {
    document.getElementById('edit-' + id).classList.toggle('hidden');
}

function switchEntTab(tab, btn) {
    document.querySelectorAll('.ent-tab-btn').forEach(function(b) {
        b.classList.remove('active');
        b.style.removeProperty('--tab-color');
    });
    btn.classList.add('active');
    btn.style.setProperty('--tab-color', btn.dataset.color);

    document.getElementById('ent-panel-quotes').classList.toggle('hidden', tab !== 'quotes');
    document.getElementById('ent-panel-packs').classList.toggle('hidden', tab !== 'packs');
}

function filterPacks(status, btn) {
    document.querySelectorAll('.pack-filter-btn').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    document.querySelectorAll('.pack-row').forEach(function(row) {
        row.style.display = (status === 'all' || row.dataset.status === status) ? '' : 'none';
    });
    // Close any open "pending invitations" sub-panels when switching filters
    document.querySelectorAll('.pack-subrow').forEach(function(row) {
        row.classList.add('hidden');
    });
}
</script>
@endsection
