@extends('layouts.dashboard')

@section('title', $group->name . ' — LeadXchange')

@push('styles')
<style>
    .post-card {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 1rem;
        overflow: hidden;
    }
    .avatar-circle {
        width: 38px; height: 38px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 13px; color: #fff; flex-shrink: 0;
        background: linear-gradient(135deg, #34d4bf, #1E8F88);
    }
    .comment-form textarea { resize: none; transition: height .15s; }
    .comment-form textarea:focus { outline: none; }
    .role-badge-owner  { background:#FEF3C7;color:#92400E; }
    .role-badge-admin  { background:#E6F7F4;color:#1E8F88; }
    .activity-card     { background:#FFFBEB;border-left:4px solid #F59E0B; }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- ── HERO ── --}}
    <div class="rounded-2xl overflow-hidden mb-6 shadow-sm border border-gray-200">
        <div class="h-36 relative"
             @unless($group->cover_photo) style="background:linear-gradient(135deg,{{ $group->cover_color }},{{ $group->cover_color }}cc);" @endunless>
            @if($group->cover_photo)
                <img src="{{ Storage::url($group->cover_photo) }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/40"></div>
            @endif
        </div>

        <div class="bg-white px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-4 justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl -mt-8 border-4 border-white shadow-md flex items-center justify-center text-white text-xl font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,{{ $group->cover_color }},{{ $group->cover_color }}bb);">
                    {{ strtoupper(substr($group->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900 leading-tight">{{ $group->name }}</h1>
                        @if($isOwner)
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold role-badge-owner">Owner</span>
                        @elseif($isAdmin)
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold role-badge-admin">Admin</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-0.5 text-xs text-gray-400">
                        @if($group->sector)
                        <span class="px-2 py-0.5 rounded-full font-medium" style="background:#E6F7F4;color:#1E8F88;">{{ $group->sector->name }}</span>
                        @endif
                        <span class="flex items-center gap-1">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            {{ number_format($group->members_count) }} membres
                        </span>
                        <span>· Créé par {{ $group->creator->first_name }} {{ $group->creator->last_name }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('groups.index') }}"
                   class="px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                    ← Groupes
                </a>

                @if($isOwner)
                    {{-- Owner: activity button + delete --}}
                    <button onclick="document.getElementById('activityModal').classList.remove('hidden')"
                        class="px-4 py-2 rounded-xl text-sm font-semibold border transition"
                        style="border-color:#F59E0B;color:#F59E0B;"
                        onmouseover="this.style.background='#FFFBEB'" onmouseout="this.style.background='transparent'">
                        + Activité
                    </button>
                    <form method="POST" action="{{ route('groups.destroy', $group->id) }}"
                          onsubmit="return confirm('Supprimer ce groupe définitivement ?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-red-500 border border-red-200 hover:bg-red-50 transition">
                            Supprimer
                        </button>
                    </form>
                @elseif($isAdmin)
                    {{-- Admin: activity button + leave --}}
                    <button onclick="document.getElementById('activityModal').classList.remove('hidden')"
                        class="px-4 py-2 rounded-xl text-sm font-semibold border transition"
                        style="border-color:#F59E0B;color:#F59E0B;"
                        onmouseover="this.style.background='#FFFBEB'" onmouseout="this.style.background='transparent'">
                        + Activité
                    </button>
                    <form method="POST" action="{{ route('groups.leave', $group->id) }}">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 rounded-xl text-sm font-semibold border transition"
                            style="border-color:#1E8F88;color:#1E8F88;"
                            onmouseover="this.style.background='#E6F7F4'" onmouseout="this.style.background='transparent'">
                            Quitter
                        </button>
                    </form>
                @elseif($isMember)
                    <form method="POST" action="{{ route('groups.leave', $group->id) }}">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 rounded-xl text-sm font-semibold border transition"
                            style="border-color:#1E8F88;color:#1E8F88;"
                            onmouseover="this.style.background='#E6F7F4'" onmouseout="this.style.background='transparent'">
                            Quitter le groupe
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('groups.join', $group->id) }}">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-white transition shadow-sm"
                            style="background:#1E8F88;"
                            onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                            Rejoindre
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if($group->description)
    <p class="text-sm text-gray-500 mb-6 px-1">{{ $group->description }}</p>
    @endif

    {{-- Flash messages --}}
    @foreach(['success' => 'emerald', 'error' => 'red', 'info' => 'blue'] as $type => $color)
    @if(session($type))
    <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium"
         style="background:{{ $color === 'emerald' ? '#ECFDF5' : ($color === 'red' ? '#FEF2F2' : '#EFF6FF') }};
                color:{{ $color === 'emerald' ? '#065F46' : ($color === 'red' ? '#991B1B' : '#1E40AF') }};">
        {{ session($type) }}
    </div>
    @endif
    @endforeach

    {{-- ── BODY ── --}}
    <div class="grid gap-6 lg:grid-cols-[1fr_300px] items-start">

        {{-- ── FIL D'ÉCHANGE ── --}}
        <div class="space-y-4">

            {{-- Post form --}}
            @if($isMember)
            <div class="post-card p-4">
                <form method="POST" action="{{ route('groups.posts.store', $group->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="flex gap-3">
                        @if(auth()->user()->profile?->avatar)
                            <img src="{{ auth()->user()->profile->avatar_url }}"
                                 class="w-10 h-10 rounded-full object-cover flex-shrink-0 border-2 border-white shadow-sm">
                        @else
                            <div class="avatar-circle" style="width:40px;height:40px;font-size:14px;">
                                {{ strtoupper(substr(auth()->user()->first_name,0,1).substr(auth()->user()->last_name,0,1)) }}
                            </div>
                        @endif
                        <div class="flex-1">
                            <textarea name="body" rows="2"
                                placeholder="Partagez quelque chose avec le groupe…"
                                class="w-full text-sm text-gray-800 border border-gray-200 rounded-xl px-4 py-3 focus:border-teal-400 focus:ring-2 focus:ring-teal-100 resize-none outline-none transition"
                                oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'">{{ old('body') }}</textarea>
                            @error('body')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror

                            {{-- Photo upload --}}
                            <div class="mt-2 flex items-center justify-between gap-3">
                                <label class="flex items-center gap-1.5 text-xs text-gray-400 cursor-pointer hover:text-teal-600 transition">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    Photo
                                    <input type="file" name="photo" accept="image/*" class="sr-only"
                                           onchange="previewPostPhoto(this)">
                                </label>
                                <button type="submit"
                                    class="px-5 py-2 rounded-xl text-sm font-semibold text-white transition"
                                    style="background:#1E8F88;"
                                    onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                                    Publier
                                </button>
                            </div>
                            <img id="postPhotoPreview" src="" alt="" class="hidden mt-2 rounded-xl max-h-40 object-cover border border-gray-100">
                        </div>
                    </div>
                </form>
            </div>
            @else
            <div class="post-card p-5 text-center text-sm text-gray-400">
                <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Rejoignez le groupe pour participer aux échanges.
            </div>
            @endif

            {{-- Posts --}}
            @forelse($posts as $post)
            <div class="post-card {{ $post->type === 'activity' ? 'activity-card' : '' }}" id="post-{{ $post->id }}">
                <div class="flex items-start gap-3 p-4 pb-3">
                    @if($post->author->profile?->avatar)
                        <img src="{{ $post->author->profile->avatar_url }}"
                             class="w-10 h-10 rounded-full object-cover flex-shrink-0 border border-gray-100">
                    @else
                        <div class="avatar-circle">
                            {{ strtoupper(substr($post->author->first_name,0,1).substr($post->author->last_name,0,1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                @if($post->type === 'activity')
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 mb-1">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        Activité
                                    </span>
                                    <p class="text-sm font-bold text-gray-900 leading-tight">{{ $post->activity_title }}</p>
                                    <p class="text-xs text-amber-700 font-medium mt-0.5">
                                        📅 {{ $post->activity_date->isoFormat('ddd D MMM YYYY à HH:mm') }}
                                    </p>
                                @endif
                                <div class="flex items-center gap-2 {{ $post->type === 'activity' ? 'mt-1' : '' }}">
                                    <a href="{{ route('profile.show', $post->author->id) }}"
                                       class="text-sm font-semibold text-gray-900 hover:underline">
                                        {{ $post->author->first_name }} {{ $post->author->last_name }}
                                    </a>
                                    <span class="text-xs text-gray-400">{{ $post->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            {{-- Delete: own post or admin --}}
                            @if($post->user_id === auth()->id() || $isAdmin)
                            <form method="POST" action="{{ route('groups.posts.destroy', [$group->id, $post->id]) }}"
                                  onsubmit="return confirm('Supprimer cette publication ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-300 hover:text-red-400 transition p-1" title="Supprimer">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>

                        @if($post->body)
                        <p class="text-sm text-gray-800 mt-2 leading-relaxed whitespace-pre-line">{{ $post->body }}</p>
                        @endif

                        @if($post->photo_path)
                        <img src="{{ $post->photo_url }}" alt="" class="mt-3 rounded-xl max-h-80 w-full object-cover border border-gray-100">
                        @endif
                    </div>
                </div>

                {{-- Comments --}}
                @if($post->comments->isNotEmpty())
                <div class="border-t border-gray-100 divide-y divide-gray-50 bg-gray-50/50">
                    @foreach($post->comments as $comment)
                    <div class="flex gap-3 px-4 py-3">
                        @if($comment->author->profile?->avatar)
                            <img src="{{ $comment->author->profile->avatar_url }}"
                                 class="w-8 h-8 rounded-full object-cover flex-shrink-0 border border-gray-100">
                        @else
                            <div class="avatar-circle" style="width:32px;height:32px;font-size:11px;">
                                {{ strtoupper(substr($comment->author->first_name,0,1).substr($comment->author->last_name,0,1)) }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="bg-white rounded-xl px-3 py-2 border border-gray-100 text-sm">
                                <a href="{{ route('profile.show', $comment->author->id) }}"
                                   class="font-semibold text-gray-900 hover:underline text-xs">
                                    {{ $comment->author->first_name }} {{ $comment->author->last_name }}
                                </a>
                                <p class="text-gray-700 mt-0.5 leading-snug whitespace-pre-line">{{ $comment->body }}</p>
                            </div>
                            <span class="text-[11px] text-gray-400 mt-0.5 ml-1">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Comment form --}}
                @if($isMember)
                <div class="px-4 py-3 border-t border-gray-100 comment-form">
                    <form method="POST" action="{{ route('groups.comments.store', [$group->id, $post->id]) }}"
                          class="flex gap-2 items-end">
                        @csrf
                        @if(auth()->user()->profile?->avatar)
                            <img src="{{ auth()->user()->profile->avatar_url }}"
                                 class="w-8 h-8 rounded-full object-cover flex-shrink-0 border border-gray-100">
                        @else
                            <div class="avatar-circle" style="width:32px;height:32px;font-size:11px;">
                                {{ strtoupper(substr(auth()->user()->first_name,0,1).substr(auth()->user()->last_name,0,1)) }}
                            </div>
                        @endif
                        <div class="flex-1 flex items-end gap-2 bg-gray-50 rounded-xl border border-gray-200 px-3 py-2 focus-within:border-teal-400 focus-within:ring-2 focus-within:ring-teal-100 transition">
                            <textarea name="body" rows="1"
                                placeholder="Écrire un commentaire…"
                                class="flex-1 text-sm text-gray-800 bg-transparent resize-none outline-none leading-snug"
                                style="max-height:120px;"
                                oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'"
                                onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();this.closest('form').submit();}"></textarea>
                            <button type="submit" class="flex-shrink-0 text-teal-600 hover:text-teal-800 transition pb-0.5">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
            @empty
            <div class="post-card p-10 text-center text-gray-400">
                <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <p class="text-sm font-medium text-gray-400">Aucune publication pour le moment.</p>
                @if($isMember)
                <p class="text-xs mt-1">Soyez le premier à partager quelque chose !</p>
                @endif
            </div>
            @endforelse

            @if($posts->hasPages())
            <div class="flex justify-center">{{ $posts->links() }}</div>
            @endif
        </div>

        {{-- ── SIDEBAR ── --}}
        <aside class="space-y-4 lg:sticky lg:top-24">

            {{-- Invite button (admin/owner only) --}}
            @if($isAdmin && $connections->isNotEmpty())
            <button onclick="document.getElementById('inviteModal').classList.remove('hidden')"
                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition shadow-sm"
                style="background:#1E8F88;"
                onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                Inviter un membre
            </button>
            @endif

            {{-- Group info --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">À propos du groupe</h3>
                <div class="space-y-2 text-sm text-gray-500">
                    <div class="flex items-center gap-2">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span><strong class="text-gray-800">{{ number_format($group->members_count) }}</strong> membres</span>
                    </div>
                    @if($group->sector)
                    <div class="flex items-center gap-2">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        <span>{{ $group->sector->name }}</span>
                    </div>
                    @endif
                    @if($group->city)
                    <div class="flex items-center gap-2">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>{{ $group->city->name }}</span>
                    </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>Créé le {{ $group->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Members list --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">
                    Membres
                    <span class="ml-1 text-xs font-normal text-gray-400">({{ $members->count() }})</span>
                </h3>
                <div class="space-y-3">
                    @foreach($members->take(15) as $member)
                    <div class="flex items-center gap-3 group/member">
                        <a href="{{ route('profile.show', $member->id) }}"
                           class="flex items-center gap-3 flex-1 min-w-0 hover:bg-gray-50 rounded-xl p-1.5 -mx-1.5 transition">
                            @if($member->profile?->avatar)
                                <img src="{{ $member->profile->avatar_url }}"
                                     class="w-9 h-9 rounded-full object-cover flex-shrink-0 border border-gray-100">
                            @else
                                <div class="avatar-circle" style="width:36px;height:36px;font-size:12px;">
                                    {{ strtoupper(substr($member->first_name,0,1).substr($member->last_name,0,1)) }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate leading-tight">
                                    {{ $member->first_name }} {{ $member->last_name }}
                                    @if($member->pivot->role === 'owner')
                                        <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full font-semibold role-badge-owner">Owner</span>
                                    @elseif($member->pivot->role === 'admin')
                                        <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full font-medium role-badge-admin">Admin</span>
                                    @endif
                                </p>
                                @if($member->profile?->job_title)
                                <p class="text-xs text-gray-400 truncate">{{ $member->profile->job_title }}</p>
                                @elseif($member->company)
                                <p class="text-xs text-gray-400 truncate">{{ $member->company->name }}</p>
                                @endif
                            </div>
                        </a>

                        {{-- Admin actions (owner only for promote/demote, admin for remove) --}}
                        @if($isAdmin && $member->id !== auth()->id() && $member->pivot->role !== 'owner')
                        <div class="flex-shrink-0 hidden group-hover/member:flex items-center gap-1">
                            @if($isOwner)
                                @if($member->pivot->role === 'admin')
                                <form method="POST" action="{{ route('groups.members.demote', [$group->id, $member->id]) }}">
                                    @csrf
                                    <button type="submit" title="Rétrograder en membre"
                                        class="text-xs px-2 py-1 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 transition">
                                        ↓
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('groups.members.promote', [$group->id, $member->id]) }}">
                                    @csrf
                                    <button type="submit" title="Promouvoir admin"
                                        class="text-xs px-2 py-1 rounded-lg border text-teal-600 hover:bg-teal-50 transition" style="border-color:#1E8F88;">
                                        ↑
                                    </button>
                                </form>
                                @endif
                            @endif
                            @if($isOwner || $member->pivot->role === 'member')
                            <form method="POST" action="{{ route('groups.members.destroy', [$group->id, $member->id]) }}"
                                  onsubmit="return confirm('Retirer {{ $member->first_name }} du groupe ?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Retirer du groupe"
                                    class="text-xs px-2 py-1 rounded-lg border border-red-100 text-red-400 hover:bg-red-50 transition">
                                    ✕
                                </button>
                            </form>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                    @if($members->count() > 15)
                    <p class="text-xs text-center text-gray-400 pt-1">+ {{ $members->count() - 15 }} autres membres</p>
                    @endif
                </div>
            </div>

        </aside>
    </div>
</div>

{{-- ── INVITE MODAL ── --}}
@if($isAdmin)
<div id="inviteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Inviter un membre</h2>
            <button type="button" onclick="document.getElementById('inviteModal').classList.add('hidden')"
                class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 transition">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('groups.invite', $group->id) }}" class="px-6 py-5">
            @csrf
            <div class="mb-4">
                <input type="text" id="inviteSearch" placeholder="Rechercher une connexion…"
                    class="w-full h-10 px-4 rounded-xl border border-gray-200 text-sm focus:border-teal-400 focus:ring-2 focus:ring-teal-100 outline-none transition">
            </div>
            <div class="space-y-1 max-h-64 overflow-y-auto" id="connectionsList">
                @foreach($connections as $conn)
                <label class="flex items-center gap-3 p-2.5 rounded-xl cursor-pointer hover:bg-gray-50 transition connection-item"
                       data-name="{{ strtolower($conn->first_name . ' ' . $conn->last_name) }}">
                    <input type="radio" name="user_id" value="{{ $conn->id }}" class="sr-only peer" required>
                    <div class="w-4 h-4 rounded-full border-2 border-gray-200 peer-checked:border-teal-500 peer-checked:bg-teal-500 flex-shrink-0 transition"></div>
                    @if($conn->profile?->avatar)
                        <img src="{{ $conn->profile->avatar_url }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                    @else
                        <div class="avatar-circle" style="width:36px;height:36px;font-size:12px;">
                            {{ strtoupper(substr($conn->first_name,0,1).substr($conn->last_name,0,1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900">{{ $conn->first_name }} {{ $conn->last_name }}</p>
                        @if($conn->profile?->job_title)
                        <p class="text-xs text-gray-400 truncate">{{ $conn->profile->job_title }}</p>
                        @endif
                    </div>
                </label>
                @endforeach
            </div>
            @if($connections->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">Toutes vos connexions sont déjà membres.</p>
            @endif
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="document.getElementById('inviteModal').classList.add('hidden')"
                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition">
                    Annuler
                </button>
                <button type="submit"
                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                    style="background:#1E8F88;"
                    onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                    Inviter
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ── ACTIVITY MODAL ── --}}
@if($isAdmin)
<div id="activityModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Créer une activité</h2>
            <button type="button" onclick="document.getElementById('activityModal').classList.add('hidden')"
                class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 transition">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('groups.activities.store', $group->id) }}" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Titre <span class="text-red-400">*</span></label>
                <input type="text" name="activity_title" value="{{ old('activity_title') }}"
                    placeholder="Ex: Réunion mensuelle du réseau"
                    required maxlength="150"
                    class="w-full h-10 px-4 rounded-xl border border-gray-200 text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none transition">
                @error('activity_title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="body" rows="3" placeholder="Détails de l'activité…" maxlength="1000"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none resize-none transition">{{ old('body') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Date et heure <span class="text-red-400">*</span></label>
                <input type="datetime-local" name="activity_date" value="{{ old('activity_date') }}"
                    required min="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                    class="w-full h-10 px-4 rounded-xl border border-gray-200 text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none transition">
                @error('activity_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('activityModal').classList.add('hidden')"
                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition">
                    Annuler
                </button>
                <button type="submit"
                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                    style="background:#F59E0B;"
                    onmouseover="this.style.background='#D97706'" onmouseout="this.style.background='#F59E0B'">
                    Créer
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    function previewPostPhoto(input) {
        const preview = document.getElementById('postPhotoPreview');
        if (input.files && input.files[0]) {
            preview.src = URL.createObjectURL(input.files[0]);
            preview.classList.remove('hidden');
        }
    }

    // Invite search filter
    const inviteSearch = document.getElementById('inviteSearch');
    if (inviteSearch) {
        inviteSearch.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.connection-item').forEach(item => {
                item.style.display = item.dataset.name.includes(q) ? '' : 'none';
            });
        });
    }

    // Close modals on backdrop click
    ['inviteModal', 'activityModal'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('click', e => { if (e.target === el) el.classList.add('hidden'); });
    });
</script>
@endpush
