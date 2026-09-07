@extends('admin.layouts.admin')

@section('title', $group->name . ' — Membres')
@section('page-title', 'Groupes')

@section('content')

{{-- ── Breadcrumb ───────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('admin.groups.index') }}" class="text-gray-400 hover:text-gray-600 transition">Groupes</a>
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-300"><path d="m9 18 6-6-6-6"/></svg>
    <span class="text-gray-700 font-semibold truncate max-w-xs">{{ $group->name }}</span>
</div>

<div class="grid gap-6 lg:grid-cols-[300px_1fr] items-start">

    {{-- ── Left: Group info card ──────────────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Group card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            {{-- Cover --}}
            <div class="h-20 relative" style="background:{{ $group->cover_color ?? '#6366F1' }};">
                <div class="absolute inset-0 opacity-10"
                     style="background-image:radial-gradient(circle at 70% 30%,white 1px,transparent 1px);background-size:18px 18px;"></div>
            </div>

            <div class="px-5 pb-5 -mt-7">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-md border-4 border-white mb-3"
                     style="background:{{ $group->cover_color ?? '#6366F1' }};">
                    {{ strtoupper(substr($group->name, 0, 1)) }}
                </div>
                <h2 class="text-base font-bold text-gray-900 leading-tight">{{ $group->name }}</h2>
                @if($group->description)
                <p class="text-xs text-gray-400 mt-1.5 leading-relaxed">{{ $group->description }}</p>
                @endif

                <div class="mt-4 space-y-2">
                    @if($group->sector)
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        {{ $group->sector->name }}
                    </div>
                    @endif
                    @if($group->city)
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $group->city->name }}
                    </div>
                    @endif
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        Créé le {{ $group->created_at->isoFormat('D MMM YYYY') }}
                    </div>
                </div>

                {{-- Visibility badge --}}
                <div class="mt-4">
                    @if($group->is_public)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Public
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-500 border border-gray-200">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>Privé
                    </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Statistiques</p>

            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500 flex items-center gap-1.5">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Membres
                </span>
                <span class="text-sm font-bold text-gray-800">{{ $members->count() }}</span>
            </div>

            @php
                $roleCounts = $members->groupBy(fn($m) => $m->pivot->role);
            @endphp
            @foreach(['owner' => ['Propriétaires', '#7C3AED'], 'admin' => ['Admins', '#D97706'], 'member' => ['Membres', '#2F44E0']] as $role => $cfg)
            @if($roleCounts->has($role))
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400 pl-4">{{ $cfg[0] }}</span>
                <span class="text-xs font-semibold" style="color:{{ $cfg[1] }};">{{ $roleCounts->get($role)->count() }}</span>
            </div>
            @endif
            @endforeach

            <div class="flex items-center justify-between pt-1 border-t border-gray-50">
                <span class="text-xs text-gray-500 flex items-center gap-1.5">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    Posts
                </span>
                <span class="text-sm font-bold text-gray-800">{{ $group->posts()->count() }}</span>
            </div>
        </div>

        {{-- Creator --}}
        @if($group->creator)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Créé par</p>
            <a href="{{ route('admin.users.show', $group->creator) }}"
               class="flex items-center gap-3 hover:bg-gray-50 rounded-xl p-2 -mx-2 transition">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#7181ED,#2F44E0);">
                    {{ strtoupper(substr($group->creator->first_name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900">{{ $group->creator->first_name }} {{ $group->creator->last_name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $group->creator->email }}</p>
                </div>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="2" class="ml-auto flex-shrink-0"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>
        @endif

        {{-- Actions --}}
        <form method="POST" action="{{ route('admin.groups.destroy', $group) }}">
            @csrf @method('DELETE')
            <button type="button"
                    data-name="{{ $group->name }}"
                    onclick="swalDelete(this, this.dataset.name)"
                    class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold border border-red-200 text-red-500 hover:bg-red-50 transition">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
                Supprimer ce groupe
            </button>
        </form>

    </div>

    {{-- ── Right: Members table ────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-900">Membres du groupe</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ $members->count() }} membre{{ $members->count() > 1 ? 's' : '' }}</p>
            </div>
            {{-- Search (client-side) --}}
            <div class="relative">
                <svg width="12" height="12" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" id="memberSearch" placeholder="Rechercher…"
                       oninput="filterMembers(this.value)"
                       class="h-8 pl-7 pr-3 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-indigo-400 transition w-44">
            </div>
        </div>

        @if($members->isEmpty())
        <div class="px-5 py-16 text-center">
            <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center bg-gray-50">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-400">Aucun membre</p>
        </div>
        @else
        <table class="w-full" id="membersTable">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Membre</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Poste</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Rôle</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Rejoint le</th>
                    <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Profil</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50" id="memberRows">
                @foreach($members as $member)
                @php
                    $role = $member->pivot->role ?? 'member';
                    $roleCfg = [
                        'owner'  => ['label' => 'Propriétaire', 'bg' => '#F5F3FF', 'color' => '#7C3AED'],
                        'admin'  => ['label' => 'Admin',        'bg' => '#FFFBEB', 'color' => '#D97706'],
                        'member' => ['label' => 'Membre',       'bg' => '#F0FDF4', 'color' => '#15803D'],
                    ];
                    $rc = $roleCfg[$role] ?? $roleCfg['member'];
                    $joinedAt = $member->pivot->joined_at ? \Carbon\Carbon::parse($member->pivot->joined_at) : null;
                    $hue  = ($member->id * 47) % 360;
                    $hue2 = ($hue + 40) % 360;
                @endphp
                <tr class="hover:bg-gray-50/60 transition member-row">
                    {{-- Avatar + name --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            @if($member->profile?->avatar)
                            <img src="{{ $member->profile->avatar_url }}"
                                 class="w-9 h-9 rounded-full object-cover flex-shrink-0 border border-gray-100"
                                 alt="">
                            @else
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                 style="background:linear-gradient(135deg,hsl({{ $hue }} 60% 55%),hsl({{ $hue2 }} 55% 45%));">
                                {{ strtoupper(substr($member->first_name, 0, 1).substr($member->last_name, 0, 1)) }}
                            </div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 member-name leading-tight">
                                    {{ $member->first_name }} {{ $member->last_name }}
                                </p>
                                <p class="text-[11px] text-gray-400 truncate">{{ $member->email }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Poste / Entreprise --}}
                    <td class="px-4 py-3.5">
                        @if($member->profile?->job_title)
                        <p class="text-xs font-medium text-gray-700">{{ $member->profile->job_title }}</p>
                        @endif
                        @if($member->company)
                        <p class="text-[11px] text-gray-400">{{ $member->company->name }}</p>
                        @endif
                        @if(!$member->profile?->job_title && !$member->company)
                        <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>

                    {{-- Rôle --}}
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold"
                              style="background:{{ $rc['bg'] }};color:{{ $rc['color'] }};">
                            {{ $rc['label'] }}
                        </span>
                    </td>

                    {{-- Date --}}
                    <td class="px-4 py-3.5">
                        @if($joinedAt)
                        <p class="text-xs text-gray-600 font-medium">{{ $joinedAt->format('d/m/Y') }}</p>
                        <p class="text-[10px] text-gray-400">{{ $joinedAt->diffForHumans() }}</p>
                        @else
                        <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>

                    {{-- Profil --}}
                    <td class="px-4 py-3.5 text-right">
                        <a href="{{ route('admin.users.show', $member) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-[11px] font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                            Voir
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

</div>

@push('scripts')
<script>
function filterMembers(q) {
    const lq = q.toLowerCase().trim();
    document.querySelectorAll('#memberRows .member-row').forEach(function(row) {
        const name = row.querySelector('.member-name')?.textContent.toLowerCase() ?? '';
        row.style.display = (!lq || name.includes(lq)) ? '' : 'none';
    });
}
</script>
@endpush

@endsection
