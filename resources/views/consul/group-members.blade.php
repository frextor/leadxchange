@extends('consul.layouts.consul')
@section('title', 'Membres — ' . $group->name)
@section('page-title', $group->name . ' · Membres')

@section('content')

<div class="mb-5 flex items-center justify-between gap-4">
    <div>
        <a href="{{ route('consul.dashboard') }}" class="text-sm text-gray-400 hover:text-gray-600 transition flex items-center gap-1">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
            Mes groupes
        </a>
        <h1 class="text-xl font-bold text-gray-900 mt-0.5">{{ $group->name }}</h1>
    </div>
    <div class="flex items-center gap-2">
        <span class="text-sm font-bold px-3 py-1.5 rounded-xl" style="background:#CCFBF1;color:#0F766E;">
            {{ $members->count() }} abonné{{ $members->count() > 1 ? 's' : '' }}
        </span>
        <a href="{{ route('consul.group.events', $group) }}"
           class="flex items-center gap-1.5 text-sm font-semibold px-3 py-1.5 rounded-xl border transition"
           style="border-color:#99F6E4;color:#0F766E;"
           onmouseover="this.style.background='#F0FDFA'" onmouseout="this.style.background='transparent'">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Événements du groupe
        </a>
    </div>
</div>

<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    {{-- Search --}}
    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
        <input type="text" id="memberSearch" placeholder="Rechercher un membre…"
               class="w-full max-w-sm rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full" id="membersTable">
            <thead>
                <tr style="border-bottom:1px solid #F1F5F9;">
                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wide">Membre</th>
                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wide hidden sm:table-cell">Poste</th>
                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wide hidden md:table-cell">Ville</th>
                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wide hidden lg:table-cell">Plan</th>
                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wide">Rôle</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($members as $member)
                <tr class="member-row hover:bg-teal-50 transition">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            @if($member->profile?->avatar)
                                <img src="{{ $member->profile->avatar_url }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0 ring-2 ring-teal-100">
                            @else
                                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-sm"
                                     style="background:linear-gradient(135deg,#14B8A6,#0D9488);">
                                    {{ strtoupper(substr($member->first_name,0,1)) }}{{ strtoupper(substr($member->last_name,0,1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 member-name">{{ $member->first_name }} {{ $member->last_name }}</p>
                                <p class="text-xs text-gray-400">{{ $member->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 hidden sm:table-cell">
                        <p class="text-sm text-gray-600">{{ $member->profile?->job_title ?? '—' }}</p>
                    </td>
                    <td class="px-5 py-3 hidden md:table-cell">
                        <p class="text-sm text-gray-600">{{ $member->city?->name ?? '—' }}</p>
                    </td>
                    <td class="px-5 py-3 hidden lg:table-cell">
                        @php $plan = $member->subscription?->plan; @endphp
                        @if($plan)
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ ucfirst($plan->name) }}</span>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @php $role = $member->pivot->role ?? 'member'; @endphp
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                            {{ $role === 'owner' ? '' : ($role === 'admin' ? '' : 'bg-gray-100 text-gray-500') }}"
                            style="{{ $role === 'owner' ? 'background:#CCFBF1;color:#0F766E;' : ($role === 'admin' ? 'background:#DBEAFE;color:#1D4ED8;' : '') }}">
                            {{ $role === 'owner' ? 'Propriétaire' : ($role === 'admin' ? 'Admin' : 'Membre') }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">Aucun membre dans ce groupe.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('memberSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.member-row').forEach(function(row) {
        const name = row.querySelector('.member-name')?.textContent.toLowerCase() ?? '';
        row.style.display = name.includes(q) ? '' : 'none';
    });
});
</script>
@endpush

@endsection
