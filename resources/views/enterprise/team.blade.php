@extends('layouts.app')
@section('title', 'Mon équipe — LeadXchange Entreprise')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 mb-8 flex-wrap">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-bold uppercase tracking-widest px-2.5 py-0.5 rounded-full"
                      style="background:#EEF2FF;color:#6366F1;">Pack Entreprise</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Mon équipe</h1>
            <p class="text-sm text-gray-500 mt-1">Gérez les licences de votre pack entreprise.</p>
        </div>
        {{-- Seats gauge --}}
        <div class="flex items-center gap-4 bg-white border border-gray-200 rounded-2xl px-6 py-4 shadow-sm">
            <div class="text-center">
                <p class="text-3xl font-extrabold text-gray-900">{{ $license->seats_used }}</p>
                <p class="text-xs text-gray-400 mt-0.5">utilisées</p>
            </div>
            <div class="text-gray-300 text-2xl font-light">/</div>
            <div class="text-center">
                <p class="text-3xl font-extrabold" style="color:#6366F1;">{{ $license->seats_total }}</p>
                <p class="text-xs text-gray-400 mt-0.5">licences</p>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-2xl px-5 py-4 text-sm">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Invite form --}}
    @if($license->seatsAvailable() > 0)
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
        <h2 class="text-sm font-bold text-gray-900 mb-1">Inviter un collaborateur</h2>
        <p class="text-xs text-gray-400 mb-4">
            Il reste <strong>{{ $license->seatsAvailable() }}</strong> licence(s) disponible(s).
            Si l'email n'a pas encore de compte, il sera créé automatiquement.
        </p>
        <form method="POST" action="{{ route('enterprise.invite') }}" class="flex gap-3 flex-wrap">
            @csrf
            <input type="email" name="email" required
                   placeholder="prenom@entreprise.com"
                   class="flex-1 min-w-[220px] rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                   style="--tw-ring-color:#6366F1;">
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                    style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                Inviter
            </button>
        </form>
    </div>
    @else
    <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 mb-6 text-sm text-amber-800">
        <strong>Pack complet.</strong> Toutes vos licences sont utilisées.
        <a href="mailto:contact@leadxchange.com" class="underline ml-1">Contactez-nous</a> pour augmenter votre quota.
    </div>
    @endif

    {{-- Members list --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">Membres du pack</h2>
            <span class="text-xs text-gray-400">{{ $invitations->whereIn('status', ['active','pending'])->count() }} / {{ $license->seats_total - 1 }} sièges invités</span>
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

        @forelse($invitations as $inv)
        <div class="flex items-center gap-4 px-6 py-4 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
            {{-- Avatar --}}
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0 text-white"
                 style="background:{{ $inv->status === 'active' ? 'linear-gradient(135deg,#34d4bf,#1E8F88)' : ($inv->status === 'revoked' ? '#D1D5DB' : 'linear-gradient(135deg,#FDE68A,#F59E0B)') }};">
                {{ strtoupper(substr($inv->email, 0, 1)) }}
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                @if($inv->user)
                <p class="text-sm font-semibold text-gray-900 truncate">
                    {{ $inv->user->first_name }} {{ $inv->user->last_name }}
                </p>
                @endif
                <p class="text-xs text-gray-400 truncate">{{ $inv->email }}</p>
                @if($inv->accepted_at)
                <p class="text-[10px] text-gray-300 mt-0.5">Rejoint le {{ $inv->accepted_at->format('d/m/Y') }}</p>
                @endif
            </div>

            {{-- Status badge --}}
            @if($inv->status === 'active')
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 flex-shrink-0">Actif</span>
            @elseif($inv->status === 'pending')
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700 flex-shrink-0">En attente</span>
            @else
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-400 flex-shrink-0">Révoqué</span>
            @endif

            {{-- Actions --}}
            @if($inv->status !== 'revoked')
            <form method="POST" action="{{ route('enterprise.revoke', $inv->id) }}"
                  onsubmit="return confirm('Révoquer la licence de {{ addslashes($inv->email) }} ? L\'utilisateur repassera en plan Basic.')">
                @csrf
                <button type="submit"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-red-200 text-red-500 hover:bg-red-50 transition">
                    Révoquer
                </button>
            </form>
            @else
            {{-- Re-invite form for revoked seats --}}
            <form method="POST" action="{{ route('enterprise.invite') }}">
                @csrf
                <input type="hidden" name="email" value="{{ $inv->email }}">
                <button type="submit"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
                    Ré-inviter
                </button>
            </form>
            @endif
        </div>
        @empty
        <div class="px-6 py-10 text-center">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" class="mx-auto mb-3"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <p class="text-sm text-gray-400">Aucun membre invité pour l'instant.</p>
        </div>
        @endforelse
    </div>

    {{-- License info --}}
    <div class="mt-4 text-xs text-gray-400 text-center">
        @if($license->expires_at)
        Licence valide jusqu'au <strong>{{ $license->expires_at->format('d/m/Y') }}</strong>.
        @else
        Licence sans date d'expiration.
        @endif
        · Pour modifier votre quota, <a href="mailto:contact@leadxchange.com" class="underline hover:text-gray-600">contactez-nous</a>.
    </div>

</div>
@endsection
