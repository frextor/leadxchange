@extends('admin.layouts.admin')
@section('title', 'Historique des points')
@section('page-title', 'Points & Achats')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm">
    <span class="text-gray-400">Paiements</span>
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-300"><path d="m9 18 6-6-6-6"/></svg>
    <span class="text-gray-700 font-semibold">Historique des points</span>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalUsers) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Utilisateurs total</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="1.8"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2z"/><path d="M12 8v4M12 16h.01"/></svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-red-600">{{ number_format($lowBalanceCount) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Solde ≤ 0 pt</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-amber-600">{{ number_format($noPurchaseCount) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Jamais acheté de points</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 mb-5 flex flex-wrap items-end gap-3">
    <div class="flex-1 min-w-[180px]">
        <label class="block text-xs font-semibold text-gray-500 mb-1">Rechercher</label>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Nom, email…"
               class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-indigo-400">
    </div>
    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer pb-1">
        <input type="checkbox" name="low_balance" value="1" {{ request('low_balance') ? 'checked' : '' }}
               class="rounded border-gray-300 text-red-500 focus:ring-red-400">
        Solde ≤ 0 seulement
    </label>
    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer pb-1">
        <input type="checkbox" name="no_purchase" value="1" {{ request('no_purchase') ? 'checked' : '' }}
               class="rounded border-gray-300 text-amber-500 focus:ring-amber-400">
        Jamais acheté
    </label>
    <button type="submit"
            class="px-4 py-2 rounded-xl text-sm font-semibold text-white"
            style="background:linear-gradient(135deg,#6366F1,#4F46E5);">
        Filtrer
    </button>
    @if(request()->hasAny(['search','low_balance','no_purchase']))
    <a href="{{ route('admin.super.points.index') }}" class="px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
        Réinitialiser
    </a>
    @endif
</form>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Utilisateur</th>
                    <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Solde</th>
                    <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Achats</th>
                    <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Membre depuis</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                @php
                    $bal = (int) ($user->points_balance ?? 0);
                    $isLow = $bal <= 0;
                    $noPurchase = $user->purchases_count === 0;
                @endphp
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0"
                                 style="background:#EEF2FF;color:#6366F1;">
                                {{ strtoupper(substr($user->first_name,0,1)) }}{{ strtoupper(substr($user->last_name,0,1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">{{ $user->first_name }} {{ $user->last_name }}</p>
                                <p class="text-xs text-gray-400">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold
                            {{ $isLow ? 'bg-red-50 text-red-600 border border-red-100' : ($bal < 5 ? 'bg-amber-50 text-amber-700 border border-amber-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-100') }}">
                            ⭐ {{ $bal }} pt{{ abs($bal) > 1 ? 's' : '' }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if($noPurchase)
                        <span class="text-xs text-gray-400 italic">Aucun achat</span>
                        @else
                        <span class="text-sm font-semibold text-gray-700">{{ $user->purchases_count }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs text-gray-400">{{ $user->created_at->format('d/m/Y') }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.super.points.show', $user) }}"
                               class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                Historique
                            </a>
                            @if($isLow || $noPurchase)
                            <button type="button"
                                    onclick="sendReminder({{ $user->id }}, this)"
                                    data-url="{{ route('admin.super.points.remind', $user) }}"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 transition remind-btn">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                Rappel points
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">
                        Aucun utilisateur trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $users->withQueryString()->links() }}
    </div>
    @endif
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
    btn.innerHTML = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Envoi…';

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
            btn.innerHTML = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg> Envoyé';
            btn.style.background = '#DCFCE7';
            btn.style.color = '#16A34A';
        } else {
            btn.disabled = false;
            btn.innerHTML = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg> Rappel points';
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
