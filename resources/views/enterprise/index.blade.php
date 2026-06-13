@extends('layouts.app')
@section('title', 'Équipe entreprise — LeadXchange')

@section('content')
<div class="max-w-4xl mx-auto px-6 lg:px-8 py-10">

    {{-- Header --}}
    <div class="mb-8">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Votre compte</p>
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Gestion d'équipe</h1>
        <p class="text-sm text-gray-400 mt-1">Invitez des membres à rejoindre votre abonnement entreprise.</p>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-5 flex items-center gap-2.5 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm font-medium">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 5 5L20 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if($errors->has('error'))
    <div class="mb-5 flex items-center gap-2.5 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-red-600 text-sm font-medium">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        {{ $errors->first('error') }}
    </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_340px]">

        {{-- LEFT: Invitations list --}}
        <div class="space-y-5">

            {{-- Sent invitations --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">Membres invités</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Invitations envoyées depuis votre compte</p>
                    </div>
                    @if($isEnterpriseOwner)
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-600">
                        {{ $seatUsed }}/{{ $seatTotal }} sièges
                    </span>
                    @endif
                </div>

                @if($isEnterpriseOwner)
                {{-- Seat progress --}}
                <div class="px-6 pt-4">
                    <div class="flex items-center justify-between text-xs text-gray-400 mb-1.5">
                        <span>Utilisation</span>
                        <span>{{ $seatUsed }}/{{ $seatTotal }}</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all"
                             style="width:{{ min(100, round($seatUsed / max(1, $seatTotal) * 100)) }}%;background:linear-gradient(90deg,#6366F1,#4338CA);"></div>
                    </div>
                </div>
                @endif

                <div class="divide-y divide-gray-50 mt-2">
                    @forelse($sentInvitations as $inv)
                    @php
                        $statusCfg = [
                            'pending'  => ['label' => 'En attente', 'classes' => 'bg-amber-50 text-amber-700'],
                            'accepted' => ['label' => 'Acceptée',   'classes' => 'bg-emerald-50 text-emerald-700'],
                            'expired'  => ['label' => 'Expirée',    'classes' => 'bg-gray-100 text-gray-400'],
                            'revoked'  => ['label' => 'Révoquée',   'classes' => 'bg-red-50 text-red-500'],
                        ];
                        $sc = $statusCfg[$inv->status] ?? $statusCfg['expired'];
                    @endphp
                    <div class="flex items-center gap-4 px-6 py-4">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                             style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                            {{ strtoupper(substr($inv->email, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900">{{ $inv->email }}</p>
                            @if($inv->acceptedUser)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $inv->acceptedUser->first_name }} {{ $inv->acceptedUser->last_name }}</p>
                            @else
                            <p class="text-xs text-gray-400 mt-0.5">Envoyée {{ $inv->created_at->diffForHumans() }}</p>
                            @endif
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $sc['classes'] }}">{{ $sc['label'] }}</span>
                        @if($inv->status === 'pending')
                        <form method="POST" action="{{ route('enterprise.invitations.revoke', $inv) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-gray-300 hover:text-red-400 transition"
                                    title="Révoquer l'invitation">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-400">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto mb-3 opacity-30"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Aucune invitation envoyée
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Received invitations --}}
            @if($receivedInvitations->isNotEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900">Invitations reçues</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Rejoignez l'équipe d'un autre membre</p>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($receivedInvitations as $inv)
                    @php
                        $statusCfg = ['pending' => ['label' => 'En attente', 'classes' => 'bg-amber-50 text-amber-700'], 'accepted' => ['label' => 'Acceptée', 'classes' => 'bg-emerald-50 text-emerald-700'], 'expired' => ['label' => 'Expirée', 'classes' => 'bg-gray-100 text-gray-400'], 'revoked' => ['label' => 'Révoquée', 'classes' => 'bg-red-50 text-red-500']];
                        $sc = $statusCfg[$inv->status] ?? $statusCfg['expired'];
                    @endphp
                    <div class="flex items-center gap-4 px-6 py-4">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                             style="background:linear-gradient(135deg,#1E8F88,#34d4bf);">
                            {{ strtoupper(substr($inv->owner->first_name ?? 'E', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900">{{ $inv->owner->first_name ?? '' }} {{ $inv->owner->last_name ?? '' }}</p>
                            <p class="text-xs text-gray-400">Invité {{ $inv->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $sc['classes'] }}">{{ $sc['label'] }}</span>
                        @if($inv->status === 'pending')
                        <form method="POST" action="{{ route('enterprise.invitations.accept', $inv->token_hash) }}">
                            @csrf
                            <button type="submit"
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold text-white flex-shrink-0 transition hover:opacity-90"
                                    style="background:linear-gradient(135deg,#1E8F88,#34d4bf);">
                                Rejoindre
                            </button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- RIGHT: Send invitation form --}}
        <div class="space-y-4">

            @if($isEnterpriseOwner)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100" style="background:linear-gradient(135deg,#EEF2FF,#E0E7FF);">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white shadow-sm flex items-center justify-center">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 3.1 10.5 19.79 19.79 0 0 1 .07 1.9 2 2 0 0 1 2 0h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L6.09 7.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-indigo-900">Inviter un membre</p>
                            <p class="text-[10px] text-indigo-400">L'invitation expire dans 7 jours</p>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('enterprise.invitations.store') }}" class="px-6 py-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               placeholder="jean.dupont@entreprise.com"
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition @error('email') border-red-300 @enderror">
                        @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    @if($seatUsed >= $seatTotal)
                    <div class="flex items-start gap-2 p-3 rounded-xl bg-amber-50 border border-amber-100">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2" class="flex-shrink-0 mt-0.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4M12 17h.01"/></svg>
                        <p class="text-xs text-amber-700 font-medium">Vous avez atteint la limite de {{ $seatTotal }} sièges. Mettez votre plan à niveau.</p>
                    </div>
                    @else
                    <button type="submit"
                            class="w-full py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                            style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                        Envoyer l'invitation
                    </button>
                    @endif
                </form>
            </div>
            @else
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-center">
                <div class="w-12 h-12 rounded-2xl mx-auto mb-4 flex items-center justify-center bg-indigo-50">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
                <p class="text-sm font-bold text-gray-900 mb-1">Plan Enterprise requis</p>
                <p class="text-xs text-gray-400 leading-relaxed">Passez à un plan Enterprise pour inviter des membres dans votre équipe et partager votre abonnement.</p>
                <a href="{{ route('leads.index') }}"
                   class="inline-block mt-4 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                   style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                    Voir les plans
                </a>
            </div>
            @endif

            {{-- Plan info --}}
            @if($subscription)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Abonnement actif</p>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                         style="background:linear-gradient(135deg,#7C3AED,#4C1D95);">
                        {{ strtoupper(substr($subscription->plan->label ?? 'E', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ $subscription->plan->label ?? 'Enterprise' }}</p>
                        <p class="text-xs text-gray-400">{{ $seatTotal }} siège{{ $seatTotal > 1 ? 's' : '' }} · Expire {{ $subscription->ends_at?->isoFormat('D MMM YYYY') ?? 'Jamais' }}</p>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
