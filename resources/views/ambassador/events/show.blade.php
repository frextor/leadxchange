@extends('ambassador.layouts.ambassador')
@section('title', $event->title)
@section('page-title', $event->title)
@section('page-subtitle', $event->starts_at?->format('d/m/Y H:i') . ' · ' . ($event->city?->name ?? $event->location ?? 'En ligne'))

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Event details --}}
    <div class="lg:col-span-2 space-y-5">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="h-24 flex items-end px-6 pb-4"
                 style="background:{{ $event->cover_color ?? 'linear-gradient(135deg,#14B8A6,#0F766E)' }};">
                <div class="flex items-center gap-3">
                    <span class="px-2 py-1 rounded-lg bg-white/20 text-white text-[11px] font-bold">{{ ucfirst($event->type) }}</span>
                    @if(!$event->is_public)
                    <span class="px-2 py-1 rounded-lg bg-red-500/40 text-white text-[11px] font-bold">Annulé</span>
                    @endif
                </div>
            </div>
            <div class="p-6">
                @if($event->description)
                <p class="text-sm text-slate-600 leading-relaxed mb-4">{{ $event->description }}</p>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    @if($event->location)
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#14B8A6" stroke-width="2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $event->location }}
                    </div>
                    @endif
                    @if($event->meeting_link)
                    <a href="{{ $event->meeting_link }}" target="_blank" class="flex items-center gap-2 text-sm text-teal-600 hover:underline">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 10l4.553-2.069A1 1 0 0 1 21 8.845v6.31a1 1 0 0 1-1.447.894L15 14M3 8a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8z"/></svg>
                        Rejoindre le lien
                    </a>
                    @endif
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#14B8A6" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        {{ $event->attendees_count }} / {{ $event->max_attendees ?? '∞' }} participants
                    </div>
                    @if($event->price)
                    <div class="text-sm text-slate-600 font-semibold">{{ number_format($event->price, 2) }} €</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Registrations --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800">Participants ({{ $registrations->count() }})</h3>
                <a href="{{ route('ambassador.events.export', $event) }}"
                   class="text-[11px] font-bold px-3 py-1.5 rounded-lg border border-teal-200 text-teal-700 hover:bg-teal-50 transition">
                    ↓ Exporter CSV
                </a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($registrations as $user)
                <div class="px-5 py-3 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0"
                         style="background:linear-gradient(135deg,#2DD4BF,#14A98C);">
                        {{ strtoupper(substr($user->first_name,0,1)) }}{{ strtoupper(substr($user->last_name,0,1)) }}
                    </div>
                    <div class="flex-1">
                        <p class="text-[13px] font-semibold text-slate-800">{{ $user->first_name }} {{ $user->last_name }}</p>
                        <p class="text-[11px] text-slate-400">{{ $user->email }}</p>
                    </div>
                    <span class="text-[11px] text-slate-400">{{ $user->pivot->registered_at ? \Carbon\Carbon::parse($user->pivot->registered_at)->format('d/m/Y') : '—' }}</span>
                </div>
                @empty
                <p class="text-center py-8 text-sm text-slate-400">Aucun participant inscrit.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Actions sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('ambassador.events.edit', $event) }}"
                   class="flex items-center gap-2 w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-white"
                   style="background:linear-gradient(135deg,#14B8A6,#0F766E);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Modifier l'événement
                </a>
                @if($event->is_public)
                <form method="POST" action="{{ route('ambassador.events.cancel', $event) }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2 w-full px-4 py-2.5 rounded-xl text-sm font-semibold border border-red-200 text-red-600 hover:bg-red-50 transition"
                            onclick="return confirm('Annuler cet événement ?')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        Annuler l'événement
                    </button>
                </form>
                @endif
                <a href="{{ route('ambassador.events.export', $event) }}"
                   class="flex items-center gap-2 w-full px-4 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-slate-600 hover:bg-gray-50 transition">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Exporter les participants
                </a>
                @if($event->created_by === auth()->id())
                {{-- Inviter toute la région --}}
                <form method="POST" action="{{ route('ambassador.events.invite-region', $event) }}"
                      onsubmit="return confirm('Inviter tous les membres de votre région à cet événement ?')">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2 w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                            style="background:#D97706;" onmouseover="this.style.background='#B45309'" onmouseout="this.style.background='#D97706'">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Inviter toute ma région
                    </button>
                </form>
                @endif
                @if($event->created_by === auth()->id() && $organizerGroups->isNotEmpty())
                <button type="button" onclick="document.getElementById('ambInviteGroupModal').classList.remove('hidden')"
                        class="flex items-center gap-2 w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                        style="background:#6366F1;" onmouseover="this.style.background='#4F46E5'" onmouseout="this.style.background='#6366F1'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Inviter un groupe
                </button>
                @endif
            </div>
        </div>

        {{-- QR Code placeholder (future) --}}
        <div class="bg-white rounded-2xl border border-dashed border-gray-200 p-5 text-center">
            <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 rounded-xl flex items-center justify-center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5"><rect x="3" y="3" width="5" height="5"/><rect x="16" y="3" width="5" height="5"/><rect x="3" y="16" width="5" height="5"/><path d="M21 16h-3v3M21 21v.01M16 16v.01"/></svg>
            </div>
            <p class="text-xs font-semibold text-slate-500">QR Code de présence</p>
            <p class="text-[11px] text-slate-400 mt-1">Bientôt disponible</p>
        </div>
    </div>
</div>

@if($event->created_by === auth()->id() && $organizerGroups->isNotEmpty())
{{-- ── INVITE GROUP MODAL ── --}}
<div id="ambInviteGroupModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Inviter un groupe</h2>
            <button type="button" onclick="document.getElementById('ambInviteGroupModal').classList.add('hidden')"
                    class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 transition">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('ambassador.events.invite-group', $event) }}" class="px-6 py-5 space-y-4">
            @csrf
            <p class="text-xs text-gray-500">Sélectionnez un de vos groupes — tous les membres non encore inscrits seront invités.</p>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($organizerGroups as $grp)
                @php $isMatch = $matchingGroup && $grp->id === $matchingGroup->id; @endphp
                <label class="flex items-center gap-3 p-3 rounded-xl cursor-pointer transition border
                    {{ $isMatch ? 'border-indigo-300 bg-indigo-50' : 'border-transparent hover:bg-indigo-50' }}
                    has-[:checked]:border-indigo-300 has-[:checked]:bg-indigo-50">
                    <input type="radio" name="group_id" value="{{ $grp->id }}"
                           class="accent-indigo-600 flex-shrink-0"
                           {{ $isMatch ? 'checked' : '' }} required>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $grp->name }}</p>
                            @if($isMatch)
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold flex-shrink-0" style="background:#EDE9FE;color:#5B21B6;">Groupe de l'événement</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400">{{ $grp->members_count }} membre{{ $grp->members_count > 1 ? 's' : '' }}</p>
                    </div>
                </label>
                @endforeach
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="document.getElementById('ambInviteGroupModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition">Annuler</button>
                <button type="submit"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                        style="background:#6366F1;" onmouseover="this.style.background='#4F46E5'" onmouseout="this.style.background='#6366F1'">
                    Inviter le groupe
                </button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('ambInviteGroupModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
});
</script>
@endif

@endsection
