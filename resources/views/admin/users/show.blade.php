@extends('admin.layouts.admin')

@section('title', $user->first_name . ' ' . $user->last_name)
@section('page-title', $user->first_name . ' ' . $user->last_name)
@section('page-subtitle', 'Fiche utilisateur')

@section('content')
<div class="grid gap-5 lg:grid-cols-[300px_1fr] items-start">

    {{-- LEFT --}}
    <div class="space-y-4">

        {{-- Identity card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="h-16" style="background:linear-gradient(135deg,#34d4bf,#1E8F88);"></div>
            <div class="px-5 pb-5 -mt-8">
                <div class="w-16 h-16 rounded-full border-4 border-white flex items-center justify-center text-white font-bold text-xl mb-3"
                     style="background:linear-gradient(135deg,#34d4bf,#1E8F88);">
                    {{ strtoupper(substr($user->first_name,0,1).substr($user->last_name,0,1)) }}
                </div>
                <h2 class="text-base font-bold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ $user->email }}</p>

                <div class="mt-3 flex flex-wrap gap-2">
                    @if($user->role === 'admin')
                    <span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:#EEF2FF;color:#4338CA;">Admin</span>
                    @elseif($user->role === 'super_admin')
                    <span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:#F5F3FF;color:#6D28D9;">Super Admin</span>
                    @else
                    <span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:#F9FAFB;color:#6B7280;">User</span>
                    @endif

                    @if($user->email_verified_at)
                    <span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:#ECFDF5;color:#065F46;">Vérifié</span>
                    @else
                    <span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:#FEF3C7;color:#92400E;">Non vérifié</span>
                    @endif

                    @php
                        $badgeColors = ['neutre'=>['bg'=>'#F3F4F6','txt'=>'#6B7280'],'bronze'=>['bg'=>'#FFEDD5','txt'=>'#92400E'],'argent'=>['bg'=>'#F1F5F9','txt'=>'#475569'],'or'=>['bg'=>'#FEF3C7','txt'=>'#B45309'],'platinium'=>['bg'=>'#EEF2FF','txt'=>'#4338CA']];
                        $bc = $badgeColors[$user->badge_level ?? 'neutre'] ?? $badgeColors['neutre'];
                    @endphp
                    <span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:{{ $bc['bg'] }};color:{{ $bc['txt'] }};">{{ ucfirst($user->badge_level ?? 'bronze') }}</span>
                </div>

                {{-- Plan & badge images --}}
                @php
                    $adminPlanKey = 'basic';
                    if ($user->isAmbassador()) $adminPlanKey = 'ambassadeur';
                    elseif ($user->isConsul())  $adminPlanKey = 'consul';
                    elseif ($user->subscription?->plan?->name === 'premium') $adminPlanKey = 'premium';
                    $adminBadgeKey = $user->badge_level ?? 'neutre';
                @endphp
                <div class="mt-3 flex items-center gap-2">
                    <img src="{{ asset('images/plans/' . $adminPlanKey . '.jpg') }}"
                         alt="{{ $adminPlanKey }}"
                         onerror="this.style.display='none'"
                         class="h-12 w-auto object-contain">
                    <img src="{{ asset('images/badges/' . $adminBadgeKey . '.jpg') }}"
                         alt="{{ $adminBadgeKey }}"
                         onerror="this.style.display='none'"
                         class="h-12 w-12 object-contain">
                </div>

                <div class="mt-4 space-y-2 text-xs text-gray-500">
                    @if($user->city)
                    <div class="flex items-center gap-2"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>{{ $user->city->name }}, {{ $user->city->country?->name }}</div>
                    @endif
                    @if($user->region)
                    <div class="flex items-center gap-2"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 0-8 8c0 5.4 7.05 11.5 7.35 11.76a1 1 0 0 0 1.3 0C12.95 21.5 20 15.4 20 10a8 8 0 0 0-8-8z"/></svg>{{ $user->region->name }}</div>
                    @endif
                    <div class="flex items-center gap-2"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Inscrit le {{ $user->created_at->format('d/m/Y') }}</div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('admin.users.edit', $user) }}"
                       class="block text-center py-2 rounded-lg text-sm font-semibold text-white"
                       style="background:#1E8F88;">Modifier</a>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Statistiques</p>
            <div class="grid grid-cols-2 gap-3">
                @foreach([['Leads envoyés',$stats['leads_sent']],['Leads reçus',$stats['leads_received']],['Connexions',$stats['connections']],['Groupes',$stats['groups']],['Événements',$stats['events']],['Points',$user->points_balance]] as [$label, $val])
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-lg font-bold text-gray-900">{{ $val }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $label }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Plan --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Abonnement</p>

            {{-- Plan actuel --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                    {{ strtoupper(substr($user->subscription?->plan?->name ?? 'B', 0, 1)) }}
                </div>
                <div>
                    <p class="font-semibold text-gray-900 text-sm">{{ $user->subscription?->plan?->label ?? 'Basic' }}</p>
                    <p class="text-xs text-gray-400">
                        @if($user->subscription?->plan?->price > 0)
                            {{ currency_format($user->subscription->plan->price) }} / mois
                        @else
                            Basic
                        @endif
                    </p>
                </div>
                @if($user->subscription?->status === 'active')
                <span class="ml-auto text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Actif</span>
                @endif
            </div>

            {{-- Changer le plan --}}
            <form method="POST" action="{{ route('admin.users.change-plan', $user) }}" class="flex gap-2">
                @csrf
                <select name="plan_id"
                        class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 bg-white text-gray-700">
                    <option value="">— Basic —</option>
                    @foreach($plans->where('price', '>', 0) as $plan)
                    <option value="{{ $plan->id }}"
                            {{ $user->subscription?->plan_id == $plan->id ? 'selected' : '' }}>
                        {{ $plan->label }} — {{ currency_format($plan->price) }}/mois
                    </option>
                    @endforeach
                </select>
                <button type="submit"
                        class="px-3 py-2 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 flex-shrink-0"
                        style="background:#4338CA;">
                    Changer
                </button>
            </form>
            <p class="text-[11px] text-gray-400 mt-2">Sélectionner "Basic" remet l'utilisateur sur le plan gratuit.</p>
        </div>
    </div>

    {{-- RIGHT --}}
    <div class="space-y-4">

        {{-- Profile --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Profil professionnel</p>
            @if($user->profile)
            <div class="grid grid-cols-2 gap-4 text-sm">
                @if($user->profile->job_title)
                <div><p class="text-xs text-gray-400 mb-1">Poste</p><p class="font-medium text-gray-900">{{ $user->profile->job_title }}</p></div>
                @endif
                @if($user->profile->sector)
                <div><p class="text-xs text-gray-400 mb-1">Secteur</p><p class="font-medium text-gray-900">{{ $user->profile->sector }}</p></div>
                @endif
                @if($user->profile->experience_level)
                <div><p class="text-xs text-gray-400 mb-1">Expérience</p><p class="font-medium text-gray-900">{{ $user->profile->experience_level }}</p></div>
                @endif
                @if($user->profile->open_to_network)
                <div><p class="text-xs text-gray-400 mb-1">Réseau</p><span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:#ECFDF5;color:#065F46;">Open to network</span></div>
                @endif
            </div>
            @if($user->profile->bio)
            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-400 mb-1">Bio</p>
                <p class="text-sm text-gray-700">{{ $user->profile->bio }}</p>
            </div>
            @endif

            {{-- Video --}}
            @if($user->profile->presentation_video)
            <div class="mt-4 p-3 bg-gray-50 rounded-lg flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 mb-1">Vidéo de présentation</p>
                    @php $vs = $user->profile->presentation_video_status; @endphp
                    @if($vs === 'pending')
                    <span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:#FEF3C7;color:#92400E;">En attente</span>
                    @elseif($vs === 'approved')
                    <span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:#ECFDF5;color:#065F46;">Approuvée</span>
                    @elseif($vs === 'rejected')
                    <span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:#FEF2F2;color:#991B1B;">Rejetée</span>
                    @endif
                </div>
                <a href="{{ route('admin.videos.index') }}" class="text-xs text-teal-600 font-medium hover:underline">Gérer →</a>
            </div>
            @endif
            @else
            <p class="text-sm text-gray-400">Profil non complété.</p>
            @endif
        </div>

        {{-- Company --}}
        @if($user->company)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Entreprise</p>
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-lg font-bold flex-shrink-0" style="color:#1E8F88;">{{ strtoupper(substr($user->company->name,0,1)) }}</div>
                <div>
                    <p class="font-semibold text-gray-900">{{ $user->company->name }}</p>
                    @if($user->company->siret)<p class="text-xs text-gray-400">SIRET : {{ $user->company->siret }}</p>@endif
                    @if($user->company->sector)<p class="text-xs text-gray-400">{{ $user->company->sector->name }}</p>@endif
                    @if($user->company->website)<a href="{{ $user->company->website }}" target="_blank" class="text-xs text-teal-600 hover:underline">{{ $user->company->website }}</a>@endif
                </div>
            </div>
        </div>
        @endif

        {{-- Interests --}}
        @if($user->interests->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Centres d'intérêt</p>
            <div class="flex flex-wrap gap-2">
                @foreach($user->interests as $interest)
                <span class="px-3 py-1.5 rounded-full text-xs font-medium border" style="background:#E6F7F4;color:#1E8F88;border-color:#A8E2D9;">{{ $interest->icon }} {{ $interest->name }}</span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Ambassador --}}
        @if($user->ambassador_status !== 'none')
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Statut Ambassadeur</p>
            @if($user->ambassador_status === 'pending')
            <div class="flex items-center justify-between">
                <span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:#FEF3C7;color:#92400E;">En attente</span>
                <a href="{{ route('admin.super.ambassadors.manage') }}" class="text-xs text-teal-600 font-medium hover:underline">Examiner →</a>
            </div>
            @elseif($user->ambassador_status === 'approved')
            <span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:#ECFDF5;color:#065F46;">Ambassadeur approuvé</span>
            @elseif($user->ambassador_status === 'rejected')
            <span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:#FEF2F2;color:#991B1B;">Rejeté</span>
            @endif
        </div>
        @endif

    </div>
</div>
@endsection
