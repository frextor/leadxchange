@extends('admin.layouts.admin')
@section('title', 'Demandes Pack Entreprise')

@section('content')
<div class="p-6">

    <div class="mb-5 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                Demandes Pack Entreprise
                @if($pendingCount > 0)
                <span class="text-sm font-bold px-2.5 py-0.5 rounded-full text-white" style="background:#EF4444;">{{ $pendingCount }}</span>
                @endif
            </h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ $quotes->count() }} demande{{ $quotes->count() > 1 ? 's' : '' }} au total</p>
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

</div>

<script>
function toggleEdit(id) {
    document.getElementById('edit-' + id).classList.toggle('hidden');
}
</script>
@endsection
