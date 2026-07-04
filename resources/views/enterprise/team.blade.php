@extends('layouts.app')
@section('title', 'Mon équipe — ' . $license->company_name)

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 mb-8 flex-wrap">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-bold uppercase tracking-widest px-2.5 py-0.5 rounded-full"
                      style="background:#EEF2FF;color:#6366F1;">Premium Entreprise</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $license->company_name }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gérez les licences de votre pack.</p>
        </div>
        {{-- Seats gauge --}}
        <div class="flex items-center gap-4 bg-white border border-gray-200 rounded-2xl px-6 py-4 shadow-sm">
            <div class="text-center">
                <p class="text-3xl font-extrabold text-gray-900">{{ $license->seats_used }}</p>
                <p class="text-xs text-gray-400 mt-0.5">attribuées</p>
            </div>
            <div class="text-gray-300 text-2xl font-light">/</div>
            <div class="text-center">
                <p class="text-3xl font-extrabold" style="color:#6366F1;">{{ $license->seats_total }}</p>
                <p class="text-xs text-gray-400 mt-0.5">licences</p>
            </div>
            @if($license->seatsAvailable() > 0)
            <div class="pl-3 border-l border-gray-100 text-center">
                <p class="text-xl font-extrabold text-emerald-600">{{ $license->seatsAvailable() }}</p>
                <p class="text-xs text-gray-400 mt-0.5">libres</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-2xl px-5 py-4 text-sm">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ session('error') }}
    </div>
    @endif
    @if(session('info'))
    <div class="mb-5 bg-blue-50 border border-blue-200 text-blue-700 rounded-2xl px-5 py-4 text-sm">{{ session('info') }}</div>
    @endif

    {{-- Members list --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">Membres du pack</h2>
            <span class="text-xs text-gray-400">{{ $license->seats_total }} licences au total</span>
        </div>

        {{-- Holder row --}}
        <div class="flex items-center gap-4 px-6 py-4 border-b border-gray-50">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                 style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                <p class="text-xs text-gray-400">{{ auth()->user()->email }}</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold" style="background:#EEF2FF;color:#6366F1;">
                Titulaire
            </span>
        </div>

        @foreach($invitations as $inv)
        <div class="flex items-center gap-4 px-6 py-4 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">

            @if($inv->status === 'available')
            {{-- Available slot --}}
            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 flex-shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <form method="POST" action="{{ route('enterprise.invite') }}" class="flex items-center gap-2 flex-wrap">
                    @csrf
                    <input type="email" name="email" required placeholder="email@collaborateur.com"
                           class="flex-1 min-w-[200px] rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <button type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-white hover:opacity-90 transition"
                            style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                        Inviter
                    </button>
                </form>
                <p class="text-[10px] text-gray-400 mt-1">
                    Ou partagez le lien directement :
                    <button type="button"
                            onclick="copyLink('{{ route('enterprise.join', $inv->token) }}', this)"
                            class="text-indigo-500 underline hover:text-indigo-700 ml-0.5">
                        Copier le lien
                    </button>
                </p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700 flex-shrink-0">Disponible</span>

            @elseif($inv->status === 'active')
            {{-- Active member --}}
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0 text-white"
                 style="background:linear-gradient(135deg,#34d4bf,#1E8F88);">
                {{ strtoupper(substr($inv->user?->first_name ?? $inv->email, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                @if($inv->user)
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $inv->user->first_name }} {{ $inv->user->last_name }}</p>
                @endif
                <p class="text-xs text-gray-400 truncate">{{ $inv->email }}</p>
                @if($inv->accepted_at)
                <p class="text-[10px] text-gray-300 mt-0.5">Rejoint le {{ $inv->accepted_at->format('d/m/Y') }}</p>
                @endif
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 flex-shrink-0">Actif</span>
            <form method="POST" action="{{ route('enterprise.revoke', $inv->id) }}"
                  onsubmit="return confirm('Révoquer la licence de {{ addslashes($inv->email) }} ? La licence sera libérée et le membre repassera en Basic.')">
                @csrf
                <button type="submit"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-red-200 text-red-500 hover:bg-red-50 transition">
                    Révoquer
                </button>
            </form>

            @elseif($inv->status === 'pending')
            {{-- Pending invitation --}}
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0 text-white"
                 style="background:linear-gradient(135deg,#FDE68A,#F59E0B);">
                {{ strtoupper(substr($inv->email, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 truncate">{{ $inv->email }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Invitation envoyée — en attente d'activation</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700 flex-shrink-0">En attente</span>
            <form method="POST" action="{{ route('enterprise.revoke', $inv->id) }}"
                  onsubmit="return confirm('Annuler cette invitation et libérer la licence ?')">
                @csrf
                <button type="submit"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
                    Annuler
                </button>
            </form>
            @endif

        </div>
        @endforeach

        @if($invitations->isEmpty())
        <div class="px-6 py-10 text-center">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" class="mx-auto mb-3"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <p class="text-sm text-gray-400">Aucune licence disponible dans ce pack.</p>
        </div>
        @endif
    </div>

    {{-- License info --}}
    <div class="mt-4 text-xs text-gray-400 text-center">
        @if($license->expires_at)
        Licences valides jusqu'au <strong>{{ $license->expires_at->format('d/m/Y') }}</strong>.
        @else
        Licences sans date d'expiration.
        @endif
        · Pour modifier votre quota, <a href="mailto:contact@leadxchange.com" class="underline hover:text-gray-600">contactez-nous</a>.
    </div>

</div>

<script>
function copyLink(url, btn) {
    navigator.clipboard.writeText(url).then(function() {
        const orig = btn.textContent;
        btn.textContent = 'Copié !';
        btn.style.color = '#059669';
        setTimeout(function() { btn.textContent = orig; btn.style.color = ''; }, 2000);
    }).catch(function() {
        prompt('Copiez ce lien :', url);
    });
}
</script>

@endsection
