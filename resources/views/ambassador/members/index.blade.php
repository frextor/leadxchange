@extends('ambassador.layouts.ambassador')
@section('title', 'Mes Membres')
@section('page-title', 'Mes Membres')
@section('page-subtitle', $regionName . ' · ' . $members->total() . ' membre(s)')

@section('content')

{{-- Search + Filters ─────────────────────────────────────────────────────── --}}
<form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 flex flex-wrap gap-3">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Rechercher un membre..."
           class="flex-1 min-w-[200px] rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">

    <select name="plan" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
        <option value="">Tous les plans</option>
        <option value="basic" @selected(request('plan')=='basic')>Basic</option>
        <option value="consul" @selected(request('plan')=='consul')>Consul</option>
        <option value="ambassadeur" @selected(request('plan')=='ambassadeur')>Ambassadeur</option>
    </select>

    <select name="sort" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
        <option value="">Plus récent</option>
        <option value="name" @selected(request('sort')=='name')>Nom A→Z</option>
        <option value="activity" @selected(request('sort')=='activity')>Dernière activité</option>
    </select>

    <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white"
            style="background:linear-gradient(135deg,#14B8A6,#0F766E);">
        Filtrer
    </button>
    @if(request()->hasAny(['search','plan','sort']))
    <a href="{{ route('ambassador.members.index') }}" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-500 hover:bg-gray-100 transition">Réinitialiser</a>
    @endif
</form>

{{-- Members table ────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Membre</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3 hidden lg:table-cell">Entreprise / Poste</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3 hidden md:table-cell">Ville</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3 hidden md:table-cell">Abonnement</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3 hidden xl:table-cell">Dernière activité</th>
                    <th class="text-right text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            @if($member->profile?->avatar)
                                <img src="{{ $member->profile->avatar_url }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                            @else
                                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-[11px] font-bold flex-shrink-0"
                                     style="background:linear-gradient(135deg,#2DD4BF,#14A98C);">
                                    {{ strtoupper(substr($member->first_name,0,1)) }}{{ strtoupper(substr($member->last_name,0,1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="text-[13px] font-semibold text-slate-800">{{ $member->first_name }} {{ $member->last_name }}</p>
                                <p class="text-[11px] text-slate-400">{{ $member->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 hidden lg:table-cell">
                        <p class="text-sm text-slate-700">{{ $member->company?->name ?? '—' }}</p>
                        <p class="text-[11px] text-slate-400">{{ $member->profile?->position ?? '' }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-slate-600 hidden md:table-cell">{{ $member->city?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-center hidden md:table-cell">
                        @if($member->subscription?->plan)
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-teal-50 text-teal-700">
                            {{ $member->subscription->plan->label }}
                        </span>
                        @else
                        <span class="text-[11px] text-slate-400">Basic</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-center text-xs text-slate-400 hidden xl:table-cell">
                        {{ $member->updated_at->diffForHumans() }}
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('profile.show', $member->id) }}" target="_blank"
                               class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg bg-gray-100 text-slate-600 hover:bg-gray-200 transition">
                                Profil
                            </a>
                            {{-- Invite to event --}}
                            @if($events->count())
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open=!open"
                                        class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg text-white transition hover:opacity-90"
                                        style="background:#14B8A6;">
                                    Inviter
                                </button>
                                <div x-show="open" @click.outside="open=false"
                                     class="absolute right-0 top-full mt-1 w-56 bg-white rounded-xl shadow-2xl border border-gray-200 z-20 py-1">
                                    @foreach($events as $evt)
                                    <form method="POST" action="{{ route('ambassador.members.invite', $member) }}">
                                        @csrf
                                        <input type="hidden" name="event_id" value="{{ $evt->id }}">
                                        <button type="submit" class="w-full text-left px-4 py-2 text-xs text-slate-700 hover:bg-gray-50 transition truncate">
                                            {{ $evt->title }}
                                        </button>
                                    </form>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-16 text-center text-sm text-slate-400">
                    Aucun membre trouvé dans votre région.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($members->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $members->links() }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
@endpush
