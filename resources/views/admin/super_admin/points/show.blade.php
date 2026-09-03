@extends('admin.layouts.admin')
@section('title', 'Points — ' . $user->first_name . ' ' . $user->last_name)
@section('page-title', 'Historique des points')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm">
    <span class="text-gray-400">Paiements</span>
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-300"><path d="m9 18 6-6-6-6"/></svg>
    <a href="{{ route('admin.super.points.index') }}" class="text-gray-400 hover:text-indigo-600 transition">Historique des points</a>
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-300"><path d="m9 18 6-6-6-6"/></svg>
    <span class="text-gray-700 font-semibold">{{ $user->first_name }} {{ $user->last_name }}</span>
</div>

{{-- User card + balance --}}
<div class="flex flex-wrap gap-4 mb-6">
    <div class="flex-1 min-w-[260px] bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5 flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-xl font-bold flex-shrink-0"
             style="background:#EEF2FF;color:#6366F1;">
            {{ strtoupper(substr($user->first_name,0,1)) }}{{ strtoupper(substr($user->last_name,0,1)) }}
        </div>
        <div>
            <p class="text-lg font-bold text-gray-800">{{ $user->first_name }} {{ $user->last_name }}</p>
            <p class="text-sm text-gray-400">{{ $user->email }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Membre depuis {{ $user->created_at->format('d/m/Y') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5 flex items-center gap-5">
        @php $isLow = $balance <= 0; @endphp
        <div class="text-center">
            <p class="text-4xl font-extrabold {{ $isLow ? 'text-red-500' : ($balance < 5 ? 'text-amber-500' : 'text-emerald-600') }}">
                {{ $balance }}
            </p>
            <p class="text-xs text-gray-400 mt-1">point{{ abs($balance) > 1 ? 's' : '' }} actuels</p>
        </div>
        <div class="border-l border-gray-100 pl-5">
            <p class="text-sm text-gray-500 mb-2">{{ $history->count() }} transaction{{ $history->count() > 1 ? 's' : '' }}</p>
            @php $noPurchase = $history->where('delta', '>', 0)->isEmpty(); @endphp
            @if($isLow || $noPurchase)
            <button type="button"
                    onclick="sendReminder({{ $user->id }}, this)"
                    data-url="{{ route('admin.super.points.remind', $user) }}"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 transition">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Envoyer rappel points
            </button>
            @endif
        </div>
    </div>
</div>

{{-- History table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <span class="text-sm font-bold text-gray-800">Toutes les transactions</span>
        <span class="text-xs text-gray-400">{{ $history->count() }} entrée{{ $history->count() > 1 ? 's' : '' }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Type</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Raison</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Delta</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Solde après</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($history as $entry)
                @php
                    $isCredit = $entry->delta > 0;
                    $icon = $isCredit ? '⬆️' : '⬇️';
                    if (str_contains($entry->reason, 'achat'))      $icon = '💳';
                    elseif (str_contains($entry->reason, 'parrain')) $icon = '🎁';
                    elseif (str_contains($entry->reason, 'envoi'))   $icon = '📤';
                    elseif (str_contains($entry->reason, 'reçu'))    $icon = '📥';
                @endphp
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-5 py-3">
                        <span class="text-lg">{{ $icon }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $entry->reason }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-bold {{ $isCredit ? 'text-emerald-600' : 'text-red-500' }}">
                            {{ $isCredit ? '+' : '' }}{{ $entry->delta }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold
                            {{ ($entry->balance_after ?? 0) <= 0 ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ $entry->balance_after ?? '—' }} pts
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right text-xs text-gray-400">
                        @if($entry->created_at)
                            <span title="{{ $entry->created_at->format('d/m/Y H:i') }}">
                                {{ $entry->created_at->diffForHumans() }}
                            </span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">
                        Aucune transaction enregistrée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Toast --}}
<div id="toast" class="fixed bottom-6 right-6 z-50 hidden">
    <div id="toastInner" class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium text-white max-w-xs">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
        <span id="toastMsg"></span>
    </div>
</div>

@endsection

@push('scripts')
<script>
function sendReminder(userId, btn) {
    const url = btn.getAttribute('data-url');
    btn.disabled = true;
    btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Envoi…';

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.message.startsWith('Erreur') ? 'error' : 'success');
        if (!data.message.startsWith('Erreur')) {
            btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg> Email envoyé';
            btn.style.background = '#DCFCE7';
            btn.style.color = '#16A34A';
        } else {
            btn.disabled = false;
        }
    })
    .catch(() => {
        showToast('Erreur réseau', 'error');
        btn.disabled = false;
    });
}

function showToast(msg, type) {
    const toast = document.getElementById('toast');
    const inner = document.getElementById('toastInner');
    const msgEl = document.getElementById('toastMsg');

    inner.style.background = type === 'error' ? '#EF4444' : '#10B981';
    msgEl.textContent = msg;
    toast.classList.remove('hidden');

    setTimeout(() => toast.classList.add('hidden'), 4000);
}
</script>
@endpush
