@extends('admin.layouts.admin')
@section('title', 'Gestion Consuls')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900">Nommer des Consuls</h1>
        <p class="text-sm text-gray-400 mt-1">
            Un membre <strong>Prémium</strong> peut être nommé Consul par l'admin — le plan bascule automatiquement.
        </p>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm font-medium">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-2xl px-5 py-3 text-sm font-medium">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('error') }}
</div>
@endif

{{-- KPI strip --}}
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0D9488" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <div>
            <p class="text-2xl font-extrabold text-gray-900">{{ $counts['consul'] }}</p>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Consuls actifs</p>
        </div>
    </div>
    <a href="{{ route('admin.super.consuls.manage', ['status' => 'pending']) }}"
       class="bg-white rounded-2xl border {{ $counts['pending'] > 0 ? 'border-amber-300' : 'border-gray-100' }} shadow-sm px-5 py-4 flex items-center gap-4 hover:bg-amber-50/50 transition">
        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
            <p class="text-2xl font-extrabold {{ $counts['pending'] > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $counts['pending'] }}</p>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Demandes en attente</p>
        </div>
    </a>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div>
            <p class="text-2xl font-extrabold text-gray-900">{{ $counts['eligible'] }}</p>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Prémium éligibles</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0D9488" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <div>
            <p class="text-2xl font-extrabold text-gray-900">{{ $counts['total'] }}</p>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Membres payants</p>
        </div>
    </div>
</div>

{{-- ════ Demandes Consul ════ --}}
<div class="mb-6">
    <div class="flex items-center gap-3 mb-3">
        <h2 class="text-base font-bold text-gray-800">Demandes Consul</h2>
        @if($pendingConsulUsers->isNotEmpty())
        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-bold text-white" style="background:#D97706;">
            {{ $pendingConsulUsers->count() }} en attente
        </span>
        @endif
    </div>

    {{-- Tabs --}}
    <div class="flex items-center gap-1 mb-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-1.5 w-fit">
        <a href="{{ route('admin.super.consuls.manage', ['req_status' => 'pending']) }}"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition
                  {{ $reqStatus === 'pending' ? 'bg-teal-600 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-50' }}">
            En attente
            @if($pendingConsulUsers->isNotEmpty())
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $reqStatus === 'pending' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">
                {{ $pendingConsulUsers->count() }}
            </span>
            @endif
        </a>
        @foreach(['approved' => 'Approuvées', 'rejected' => 'Refusées'] as $s => $label)
        <a href="{{ route('admin.super.consuls.manage', ['req_status' => $s]) }}"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition
                  {{ $reqStatus === $s ? 'bg-teal-600 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-50' }}">
            {{ $label }}
            @if($consulRequestCounts[$s] > 0)
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $reqStatus === $s ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">
                {{ $consulRequestCounts[$s] }}
            </span>
            @endif
        </a>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Onglet En attente : users avec consul_status = pending --}}
        @if($reqStatus === 'pending')
            @forelse($pendingConsulUsers as $user)
            <div class="flex items-center gap-4 px-5 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#2DD4BF,#0D9488);">
                    {{ strtoupper(substr($user->first_name ?? '?', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-semibold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</p>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-medium">
                            {{ $user->subscription?->plan?->label ?? 'Sans plan' }}
                        </span>
                        @if($user->city)
                        <span class="text-xs text-gray-400">📍 {{ $user->city->name }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $user->email }}</p>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 flex-shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> En attente
                </span>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <form method="POST" action="{{ route('admin.super.consuls.nominate', $user) }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white hover:opacity-90 transition"
                                style="background:linear-gradient(135deg,#2DD4BF,#0D9488);">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                            Approuver
                        </button>
                    </form>
                    <button type="button"
                            onclick="openConsulRejectModal('{{ route('admin.super.consuls.reject-request', $user) }}')"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border border-red-200 text-red-600 hover:bg-red-50 transition">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        Refuser
                    </button>
                </div>
            </div>
            @empty
            <div class="px-5 py-10 text-center">
                <p class="text-sm text-gray-400">Aucune demande en attente.</p>
            </div>
            @endforelse

        {{-- Onglets Approuvées / Refusées : historique ConsulRequest --}}
        @else
            @forelse($consulRequests as $req)
            <div class="flex items-start gap-4 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#2DD4BF,#0D9488);">
                    {{ strtoupper(substr($req->user->first_name ?? '?', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-semibold text-gray-900">{{ $req->user->first_name }} {{ $req->user->last_name }}</p>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-medium">
                            {{ $req->user->subscription?->plan?->label ?? 'Sans plan' }}
                        </span>
                        @if($req->user->city)
                        <span class="text-xs text-gray-400">📍 {{ $req->user->city->name }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $req->user->email }}</p>
                    @if($req->isApproved())
                    <p class="text-xs text-emerald-600 mt-1">Approuvé par {{ $req->validator?->first_name }} le {{ $req->validated_at?->format('d/m/Y') }}</p>
                    @elseif($req->rejection_reason)
                    <p class="text-xs text-red-500 mt-1">Raison : {{ $req->rejection_reason }}</p>
                    @endif
                </div>
                @if($req->isApproved())
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 flex-shrink-0 self-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approuvée
                </span>
                @else
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-600 flex-shrink-0 self-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Refusée
                </span>
                @endif
            </div>
            @empty
            <div class="px-5 py-10 text-center">
                <p class="text-sm text-gray-400">Aucune demande {{ $reqStatus === 'approved' ? 'approuvée' : 'refusée' }}.</p>
            </div>
            @endforelse
            @if($consulRequests->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $consulRequests->links() }}</div>
            @endif
        @endif

    </div>
</div>

{{-- Reject modal --}}
<div id="reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <h3 class="text-base font-bold text-gray-900 mb-4">Refuser la demande ambassadeur</h3>
        <form id="reject-form" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Raison (optionnelle)</label>
                <textarea name="reason" rows="3"
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-red-300 focus:ring-2 focus:ring-red-50 transition resize-none"
                          placeholder="Expliquez la raison du refus…"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 transition" style="background:#DC2626;">Confirmer le refus</button>
                <button type="button" onclick="closeRejectModal()" class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">Annuler</button>
            </div>
        </form>
    </div>
</div>

{{-- Consul reject modal (DELETE + reason) --}}
<div id="consul-reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <h3 class="text-base font-bold text-gray-900 mb-4">Refuser la demande Consul</h3>
        <form id="consul-reject-form" method="POST">
            @csrf
            @method('DELETE')
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Raison (optionnelle)</label>
                <textarea name="reason" rows="3"
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-red-300 focus:ring-2 focus:ring-red-50 transition resize-none"
                          placeholder="Expliquez la raison du refus…"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 transition" style="background:#DC2626;">Confirmer le refus</button>
                <button type="button" onclick="closeConsulRejectModal()" class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">Annuler</button>
            </div>
        </form>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="flex items-center gap-3 mb-5">
    <div class="relative flex-1 max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-300" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, prénom, email…"
               class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-teal-400 transition">
    </div>
    <select name="status" onchange="this.form.submit()"
            class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white text-gray-700 focus:outline-none focus:border-teal-400 transition">
        <option value="">Tous les membres payants</option>
        <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Demandes en attente ({{ $counts['pending'] }})</option>
        <option value="consul"   {{ request('status') === 'consul'   ? 'selected' : '' }}>Consuls uniquement</option>
        <option value="eligible" {{ request('status') === 'eligible' ? 'selected' : '' }}>Éligibles uniquement</option>
    </select>
    @if(request()->hasAny(['search','status']))
    <a href="{{ route('admin.super.consuls.manage') }}"
       class="px-3 py-2 rounded-xl text-sm text-gray-500 border border-gray-200 hover:bg-gray-50 transition">Reset</a>
    @endif
</form>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100">
        <span class="text-sm text-gray-500">{{ $users->total() }} membre{{ $users->total() > 1 ? 's' : '' }}</span>
    </div>

    <div class="divide-y divide-gray-50">
        @forelse($users as $user)
        @php $isConsul = $user->isConsul(); $isAmbassador = $user->isAmbassador(); $isPendingConsul = $user->consul_status === 'pending'; @endphp
        <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/50 transition {{ $isConsul ? 'bg-teal-50/30' : ($isPendingConsul ? 'bg-amber-50/40' : '') }}">

            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                 style="background: {{ $isConsul ? 'linear-gradient(135deg,#2DD4BF,#0D9488)' : 'linear-gradient(135deg,#34d4bf,#1E8F88)' }};">
                {{ strtoupper(substr($user->first_name, 0, 1)) }}
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-semibold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</p>
                    @if($isConsul)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-700">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Consul
                    </span>
                    @elseif($isPendingConsul)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Demande Consul en attente
                    </span>
                    @endif
                    {{-- Show Ambassador badge as read-only info only --}}
                    @if($isAmbassador)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        Ambassadeur
                    </span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $user->email }}</p>
            </div>

            <div class="hidden sm:block text-right flex-shrink-0">
                <p class="text-xs font-semibold text-gray-700">{{ $user->subscription?->plan?->label ?? '—' }}</p>
                <p class="text-[10px] text-gray-400">{{ $user->city?->name ?? '—' }}</p>
            </div>

            <div class="flex-shrink-0 flex items-center gap-2">
                @if($isConsul)
                {{-- Consul: revoke --}}
                <form method="POST" action="{{ route('admin.super.consuls.revoke', $user) }}"
                      onsubmit="return confirm('Retirer le rôle Consul à {{ addslashes($user->first_name . ' ' . $user->last_name) }} ?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border border-red-200 text-red-600 hover:bg-red-50 transition">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        Retirer Consul
                    </button>
                </form>
                @elseif($isPendingConsul)
                {{-- Pending: approve or reject --}}
                <form method="POST" action="{{ route('admin.super.consuls.nominate', $user) }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white hover:opacity-90 transition"
                            style="background:linear-gradient(135deg,#2DD4BF,#0D9488);">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                        Approuver
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.super.consuls.reject-request', $user) }}"
                      onsubmit="return confirm('Refuser la demande Consul de {{ addslashes($user->first_name . ' ' . $user->last_name) }} ?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border border-red-200 text-red-600 hover:bg-red-50 transition">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        Refuser
                    </button>
                </form>
                @else
                {{-- Premium eligible: nominate --}}
                <form method="POST" action="{{ route('admin.super.consuls.nominate', $user) }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white hover:opacity-90 transition"
                            style="background:linear-gradient(135deg,#2DD4BF,#0D9488);">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Nommer Consul
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="px-5 py-16 text-center">
            <div class="w-14 h-14 rounded-2xl bg-teal-50 flex items-center justify-center mx-auto mb-3">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0D9488" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-400">Aucun membre éligible trouvé.</p>
            <p class="text-xs text-gray-300 mt-1">Seuls les membres avec un plan payant apparaissent ici.</p>
        </div>
        @endforelse
    </div>

    @if($users->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $users->links() }}</div>
    @endif
</div>

<div class="mt-4 flex items-start gap-3 bg-teal-50 border border-teal-100 rounded-2xl px-5 py-4 text-sm text-teal-800">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
    <ul class="space-y-0.5 text-teal-700 text-xs">
        <li>• Tout membre avec un abonnement payant (Prémium…) peut être nommé Consul par l'admin.</li>
        <li>• La nomination attribue automatiquement le plan <strong>Consul</strong> et notifie l'utilisateur.</li>
        <li>• Révoquer un Consul le fait revenir au plan <strong>Prémium</strong>. S'il était aussi Ambassadeur, le rôle Ambassadeur est retiré en même temps.</li>
        <li>• Pour nommer un Ambassadeur, rendez-vous dans la page <a href="{{ route('admin.super.ambassadors.manage') }}" class="font-semibold underline">Ambassadeurs</a>.</li>
    </ul>
</div>

@endsection

@push('scripts')
<script>
const rejectUrlTemplate = '{{ route("admin.super.consul.reject", ["consulRequest" => "PLACEHOLDER_ID"]) }}';
function openRejectModal(id) {
    document.getElementById('reject-form').action = rejectUrlTemplate.replace('PLACEHOLDER_ID', id);
    const m = document.getElementById('reject-modal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeRejectModal() {
    const m = document.getElementById('reject-modal');
    m.classList.add('hidden'); m.classList.remove('flex');
}
document.getElementById('reject-modal').addEventListener('click', function(e) { if(e.target===this) closeRejectModal(); });

function openConsulRejectModal(url) {
    document.getElementById('consul-reject-form').action = url;
    const m = document.getElementById('consul-reject-modal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeConsulRejectModal() {
    const m = document.getElementById('consul-reject-modal');
    m.classList.add('hidden'); m.classList.remove('flex');
}
document.getElementById('consul-reject-modal').addEventListener('click', function(e) { if(e.target===this) closeConsulRejectModal(); });
</script>
@endpush
