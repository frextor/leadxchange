@extends('admin.layouts.admin')
@section('title', 'Codes promo')
@section('page-title', 'Paramètres')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Codes promo</h1>
        <p class="text-sm text-gray-400 mt-1 max-w-xl">Générez des codes à usage unique donnant accès à Premium pendant une durée choisie. Le membre saisit le code sur sa page « J'ai un code promo ».</p>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-2xl px-5 py-4 text-sm">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('error') }}
</div>
@endif

{{-- Création --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Nouveau code</p>
    <form method="POST" action="{{ route('admin.super.promo-codes.store') }}" class="flex items-end gap-4 flex-wrap">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5" for="premium_days">Durée Premium offerte</label>
            <div class="flex items-center gap-2">
                <input type="number" name="premium_days" id="premium_days" min="1" max="365" value="{{ old('premium_days', 30) }}" required
                       class="w-24 px-3.5 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('premium_days') border-red-300 @else border-gray-200 @enderror">
                <span class="text-sm text-gray-400">jours</span>
            </div>
            @error('premium_days')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5" for="quantity">Nombre de codes</label>
            <input type="number" name="quantity" id="quantity" min="1" max="50" value="{{ old('quantity', 1) }}"
                   class="w-24 px-3.5 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-200 border-gray-200">
        </div>
        <div class="flex-1 min-w-[220px]">
            <label class="block text-xs font-semibold text-gray-500 mb-1.5" for="label">Note interne (optionnel)</label>
            <input type="text" name="label" id="label" maxlength="150" value="{{ old('label') }}" placeholder="Ex. Campagne LinkedIn octobre"
                   class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-200 border-gray-200">
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-sm">
            Générer
        </button>
    </form>
</div>

{{-- Liste --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full border-collapse text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-left px-5 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Code</th>
                <th class="text-left px-5 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Durée</th>
                <th class="text-left px-5 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Note</th>
                <th class="text-left px-5 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Statut</th>
                <th class="text-left px-5 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Créé le</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($codes as $promoCode)
            <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                <td class="px-5 py-3">
                    <button type="button" class="copy-code inline-flex items-center gap-1.5 font-mono font-semibold text-gray-800 bg-gray-100 rounded-lg px-2.5 py-1 hover:bg-gray-200 transition" data-code="{{ $promoCode->code }}" title="Copier">
                        {{ $promoCode->code }}
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                </td>
                <td class="px-5 py-3 text-gray-600">{{ $promoCode->premium_days }} jour{{ $promoCode->premium_days > 1 ? 's' : '' }}</td>
                <td class="px-5 py-3 text-gray-400">{{ $promoCode->label ?: '—' }}</td>
                <td class="px-5 py-3">
                    @if($promoCode->isUsed())
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">
                            Utilisé par {{ $promoCode->user ? $promoCode->user->first_name . ' ' . $promoCode->user->last_name : 'un compte supprimé' }}
                        </span>
                    @elseif(! $promoCode->is_active)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-600">Désactivé</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600">Disponible</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-gray-400">{{ $promoCode->created_at->format('d/m/Y') }}</td>
                <td class="px-5 py-3 text-right whitespace-nowrap">
                    @unless($promoCode->isUsed())
                    <button type="button" onclick="openSendModal('{{ route('admin.super.promo-codes.send', $promoCode) }}', '{{ $promoCode->code }}')" class="text-xs font-semibold text-indigo-500 hover:text-indigo-700 transition mr-3">
                        Envoyer
                    </button>
                    <form method="POST" action="{{ route('admin.super.promo-codes.toggle', $promoCode) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-gray-400 hover:text-gray-700 transition mr-3">
                            {{ $promoCode->is_active ? 'Désactiver' : 'Réactiver' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.super.promo-codes.destroy', $promoCode) }}" class="inline" onsubmit="return confirm('Supprimer ce code ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-red-400 hover:text-red-600 transition">Supprimer</button>
                    </form>
                    @endunless
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">Aucun code promo pour le moment.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="mt-5">{{ $codes->links() }}</div>

{{-- Send-by-email modal --}}
<div id="send-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <h3 class="text-base font-bold text-gray-900 mb-1">Envoyer le code par email</h3>
        <p id="send-modal-code" class="text-xs text-gray-400 mb-4 font-mono"></p>
        <form id="send-form" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5" for="send-email">Adresse email du destinataire</label>
                <input type="email" name="email" id="send-email" required placeholder="prenom.nom@exemple.com"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-50 transition">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">Envoyer</button>
                <button type="button" onclick="closeSendModal()" class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">Annuler</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.copy-code').forEach(function (btn) {
    btn.addEventListener('click', function () {
        navigator.clipboard.writeText(this.dataset.code).then(() => {
            const original = this.querySelector('svg');
            this.style.opacity = '0.6';
            setTimeout(() => { this.style.opacity = '1'; }, 300);
        });
    });
});

function openSendModal(url, code) {
    document.getElementById('send-form').action = url;
    document.getElementById('send-modal-code').textContent = 'Code : ' + code;
    document.getElementById('send-email').value = '';
    const m = document.getElementById('send-modal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeSendModal() {
    const m = document.getElementById('send-modal');
    m.classList.add('hidden'); m.classList.remove('flex');
}
document.getElementById('send-modal').addEventListener('click', function (e) { if (e.target === this) closeSendModal(); });
</script>
@endpush
