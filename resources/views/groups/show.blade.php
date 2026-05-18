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
    .comment-form textarea {
        resize: none;
        transition: height .15s;
    }
    .comment-form textarea:focus { outline: none; }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- ── HERO ── --}}
    <div class="rounded-2xl overflow-hidden mb-6 shadow-sm border border-gray-200">
        {{-- Cover --}}
        <div class="h-36 relative"
             @unless($group->cover_photo) style="background:linear-gradient(135deg,{{ $group->cover_color }},{{ $group->cover_color }}cc);" @endunless>
            @if($group->cover_photo)
                <img src="{{ Storage::url($group->cover_photo) }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/40"></div>
            @endif
        </div>

        {{-- Info bar --}}
        <div class="bg-white px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-4 justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl -mt-8 border-4 border-white shadow-md flex items-center justify-center text-white text-xl font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,{{ $group->cover_color }},{{ $group->cover_color }}bb);">
                    {{ strtoupper(substr($group->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 leading-tight">{{ $group->name }}</h1>
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
                @if($isMember)
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

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium text-emerald-700" style="background:#ECFDF5;border:1px solid #6EE7B7;">
        {{ session('success') }}
    </div>
    @endif

    {{-- ── BODY: 2 colonnes ── --}}
    <div class="grid gap-6 lg:grid-cols-[1fr_300px] items-start">

        {{-- ── FIL D'ÉCHANGE ── --}}
        <div class="space-y-4">

            {{-- Formulaire nouveau post --}}
            @if($isMember)
            <div class="post-card p-4">
                <form method="POST" action="{{ route('groups.posts.store', $group->id) }}">
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
                            <div class="flex justify-end mt-2">
                                <button type="submit"
                                    class="px-5 py-2 rounded-xl text-sm font-semibold text-white transition"
                                    style="background:#1E8F88;"
                                    onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                                    Publier
                                </button>
                            </div>
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
            <div class="post-card" id="post-{{ $post->id }}">
                {{-- Post header --}}
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
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <a href="{{ route('profile.show', $post->author->id) }}"
                                   class="text-sm font-semibold text-gray-900 hover:underline">
                                    {{ $post->author->first_name }} {{ $post->author->last_name }}
                                </a>
                                <span class="text-xs text-gray-400 ml-2">{{ $post->created_at->diffForHumans() }}</span>
                            </div>
                            @if($post->user_id === auth()->id())
                            <form method="POST" action="{{ route('groups.posts.destroy', [$group->id, $post->id]) }}"
                                  onsubmit="return confirm('Supprimer cette publication ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-300 hover:text-red-400 transition p-1" title="Supprimer">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                        {{-- Post body --}}
                        <p class="text-sm text-gray-800 mt-2 leading-relaxed whitespace-pre-line">{{ $post->body }}</p>
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

            {{-- Pagination --}}
            @if($posts->hasPages())
            <div class="flex justify-center">{{ $posts->links() }}</div>
            @endif
        </div>

        {{-- ── SIDEBAR : Membres ── --}}
        <aside class="space-y-4 lg:sticky lg:top-24">

            {{-- Stats --}}
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

            {{-- Membres --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">
                    Membres
                    <span class="ml-1 text-xs font-normal text-gray-400">({{ $members->count() }})</span>
                </h3>
                <div class="space-y-3">
                    @foreach($members->take(15) as $member)
                    <a href="{{ route('profile.show', $member->id) }}"
                       class="flex items-center gap-3 hover:bg-gray-50 rounded-xl p-1.5 -mx-1.5 transition">
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
                                @if($member->pivot->role === 'admin')
                                <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full font-medium" style="background:#E6F7F4;color:#1E8F88;">Admin</span>
                                @endif
                            </p>
                            @if($member->profile?->job_title)
                            <p class="text-xs text-gray-400 truncate">{{ $member->profile->job_title }}</p>
                            @elseif($member->company)
                            <p class="text-xs text-gray-400 truncate">{{ $member->company->name }}</p>
                            @endif
                        </div>
                    </a>
                    @endforeach
                    @if($members->count() > 15)
                    <p class="text-xs text-center text-gray-400 pt-1">+ {{ $members->count() - 15 }} autres membres</p>
                    @endif
                </div>
            </div>

        </aside>
    </div>
</div>
@endsection
