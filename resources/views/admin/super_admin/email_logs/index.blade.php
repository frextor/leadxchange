@extends('admin.layouts.admin')
@section('title', 'Journal des emails')
@section('page-title', 'Journal des emails')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm">
    <span class="text-gray-400">Système</span>
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-300"><path d="m9 18 6-6-6-6"/></svg>
    <span class="text-gray-700 font-semibold">Journal des emails</span>
</div>

{{-- Stats (7 derniers jours) --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Envoyés (7 jours)</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
        <p class="text-2xl font-bold text-emerald-600">{{ number_format($stats['sent']) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Réussis</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
        <p class="text-2xl font-bold text-red-500">{{ number_format($stats['failed']) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Échoués</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
        <p class="text-2xl font-bold text-amber-500">{{ number_format($stats['pending']) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">En attente</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 mb-5 flex flex-wrap items-end gap-3">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-semibold text-gray-500 mb-1">Rechercher</label>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Email, nom, sujet…"
               class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-indigo-400">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Statut</label>
        <select name="status" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-indigo-400 bg-white">
            <option value="">Tous</option>
            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Envoyé</option>
            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Échoué</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Type</label>
        <select name="type" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-indigo-400 bg-white">
            <option value="">Tous</option>
            @foreach($types as $t)
            <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit"
            class="px-4 py-2 rounded-xl text-sm font-semibold text-white"
            style="background:linear-gradient(135deg,#6366F1,#4338CA);">
        Filtrer
    </button>
    @if(request()->hasAny(['search','status','type']))
    <a href="{{ route('admin.super.email-logs.index') }}" class="px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
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
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Destinataire</th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Sujet</th>
                    <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Type</th>
                    <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Statut</th>
                    <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Date</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50/50 transition {{ $log->status === 'failed' ? 'bg-red-50/30' : '' }}">
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-gray-800 text-sm">{{ $log->recipient_name ?: '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $log->recipient_email }}</p>
                    </td>
                    <td class="px-4 py-3.5">
                        <p class="text-gray-700 max-w-xs truncate" title="{{ $log->subject }}">{{ $log->subject }}</p>
                        @if($log->status === 'failed' && $log->error_message)
                        <p class="text-xs text-red-500 max-w-xs truncate mt-0.5" title="{{ $log->error_message }}">{{ $log->error_message }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-[11px] font-mono px-2 py-0.5 rounded-lg bg-gray-100 text-gray-600">{{ $log->type }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        {!! $log->status_badge !!}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs text-gray-400" title="{{ $log->created_at->format('d/m/Y H:i') }}">
                            {{ $log->created_at->diffForHumans() }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-end gap-2">
                            @if($log->status === 'failed')
                            <button type="button"
                                    onclick="resendLog({{ $log->id }}, this)"
                                    data-url="{{ route('admin.super.email-logs.resend', $log) }}"
                                    title="Renvoie une notification générique avec le même sujet — le contenu d'origine n'est pas conservé"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 transition remind-btn">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                                Renvoyer
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">
                        Aucun email trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $logs->withQueryString()->links() }}
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
function resendLog(id, btn) {
    const url = btn.getAttribute('data-url');
    btn.disabled = true;
    btn.innerHTML = 'Envoi…';

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        const isError = data.message.startsWith('Échec');
        showToast(data.message, isError ? 'error' : 'success');
        if (!isError) {
            btn.outerHTML = '<span class="text-xs font-semibold text-emerald-600">✓ Renvoyé</span>';
        } else {
            btn.disabled = false;
            btn.innerHTML = 'Renvoyer';
        }
    })
    .catch(() => {
        showToast('Erreur réseau', 'error');
        btn.disabled = false;
        btn.innerHTML = 'Renvoyer';
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
