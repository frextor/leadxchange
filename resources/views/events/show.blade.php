@extends('layouts.dashboard')

@section('title', $event->title . ' — LeadXchange')

@push('styles')
<style>
    .avatar-circle {
        width: 38px; height: 38px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 13px; color: #fff; flex-shrink: 0;
        background: linear-gradient(135deg, #34d4bf, #1E8F88);
    }
    .info-row {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 14px 0; border-bottom: 1px solid #F3F4F6;
    }
    .info-row:last-child { border-bottom: none; }
    .info-icon {
        width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: #F3F4F6;
    }
</style>
@endpush

@section('content')
@php
    $typeColors = ['virtual' => '#6366F1', 'in_person' => '#1E8F88', 'hybrid' => '#F59E0B'];
    $typeLabels = ['virtual' => 'Virtuel', 'in_person' => 'Présentiel', 'hybrid' => 'Hybride'];
    $typeColor  = $typeColors[$event->type] ?? $event->cover_color;
    $typeLabel  = $typeLabels[$event->type] ?? $event->type;
    $isPast     = $event->starts_at->isPast();
    $catLabel   = App\Models\Event::$categoryLabels[$event->category ?? ''] ?? null;
    $capacity   = $event->max_attendees;
    $pct        = $capacity ? min(100, round($event->attendees_count / $capacity * 100)) : null;
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- ── HERO COVER ── --}}
    <div class="rounded-2xl overflow-hidden mb-6 shadow-sm border border-gray-200">
        <div class="h-48 sm:h-64 relative"
             @unless($event->cover_image) style="background:linear-gradient(135deg,{{ $event->cover_color }},{{ $event->cover_color }}99);" @endunless>
            @if($event->cover_image)
                <img src="{{ Storage::url($event->cover_image) }}" alt="{{ $event->title }}"
                     class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/40"></div>
            @else
                <div class="absolute inset-0 opacity-10"
                     style="background-image:radial-gradient(circle at 70% 30%,white 1px,transparent 1px);background-size:20px 20px;"></div>
            @endif

            {{-- Type + category badges --}}
            <div class="absolute top-4 left-4 flex gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-semibold text-white shadow-sm"
                      style="background:{{ $typeColor }};">{{ $typeLabel }}</span>
                @if($catLabel)
                <span class="px-3 py-1 rounded-full text-xs font-semibold text-white shadow-sm" style="background:rgba(0,0,0,.35);">{{ $catLabel }}</span>
                @endif
            </div>

            {{-- Date badge --}}
            <div class="absolute top-4 right-4 rounded-xl overflow-hidden shadow-lg text-center min-w-[52px]">
                <div class="px-3 py-1 text-white text-[11px] font-bold uppercase" style="background:{{ $typeColor }};">
                    {{ $event->starts_at->isoFormat('MMM') }}
                </div>
                <div class="px-3 py-1.5 bg-white text-gray-900 text-2xl font-extrabold leading-none">
                    {{ $event->starts_at->format('d') }}
                </div>
            </div>

            @if($isPast)
            <div class="absolute bottom-4 left-4 px-3 py-1 rounded-full text-xs font-semibold bg-black/50 text-white">
                Événement terminé
            </div>
            @endif
        </div>

        {{-- Title bar --}}
        <div class="bg-white px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4 justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 leading-tight">{{ $event->title }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-gray-400">
                    <span>Organisé par <strong class="text-gray-700">{{ $event->creator->first_name }} {{ $event->creator->last_name }}</strong></span>
                    @if($event->sector)
                    <span>·</span>
                    <span class="px-2 py-0.5 rounded-full font-medium" style="background:#E6F7F4;color:#1E8F88;">{{ $event->sector->name }}</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('events.index') }}"
               class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                ← Événements
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @foreach(['success' => '#ECFDF5,#6EE7B7,#065F46', 'error' => '#FEF2F2,#FECACA,#991B1B', 'info' => '#EFF6FF,#BFDBFE,#1E40AF'] as $type => $colors)
    @if(session($type))
    @php [$bg, $border, $text] = explode(',', $colors); @endphp
    <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium" style="background:{{ $bg }};border:1px solid {{ $border }};color:{{ $text }};">
        {{ session($type) }}
    </div>
    @endif
    @endforeach

    {{-- ── BODY ── --}}
    <div class="grid gap-6 lg:grid-cols-[1fr_300px] items-start">

        {{-- Colonne gauche : détails --}}
        <div class="space-y-5">

            {{-- Infos clés --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-1">Détails de l'événement</h2>

                {{-- Date & heure --}}
                <div class="info-row">
                    <div class="info-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="{{ $typeColor }}" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $event->starts_at->isoFormat('dddd D MMMM YYYY') }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $event->starts_at->format('H:i') }}
                            @if($event->ends_at) → {{ $event->ends_at->format('H:i') }}
                            <span class="ml-1">({{ $event->starts_at->diffForHumans($event->ends_at, true) }})</span>
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Lieu / lien --}}
                @if($event->type !== 'virtual' && $event->location)
                <div class="info-row">
                    <div class="info-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="{{ $typeColor }}" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $event->location }}</p>
                        @if($event->city)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $event->city->name }}</p>
                        @endif
                    </div>
                </div>
                @endif

                @if($event->type !== 'in_person' && $event->meeting_link)
                <div class="info-row">
                    <div class="info-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="{{ $typeColor }}" stroke-width="2"><path d="M15 7h3a5 5 0 0 1 5 5 5 5 0 0 1-5 5h-3m-6 0H6a5 5 0 0 1-5-5 5 5 0 0 1 5-5h3"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Événement en ligne</p>
                        @if($isAttending)
                        <a href="{{ $event->meeting_link }}" target="_blank" rel="noopener"
                           class="text-xs font-medium mt-0.5 inline-block hover:underline"
                           style="color:{{ $typeColor }};">
                            Accéder au lien →
                        </a>
                        @else
                        <p class="text-xs text-gray-400 mt-0.5">Le lien sera visible après inscription</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Prix --}}
                <div class="info-row">
                    <div class="info-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="{{ $typeColor }}" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                    </div>
                    <div>
                        @if($event->is_free)
                        <p class="text-sm font-semibold text-emerald-600">Gratuit</p>
                        @else
                        <p class="text-sm font-semibold text-gray-900">{{ number_format($event->price, 2) }} €</p>
                        @endif
                    </div>
                </div>

                {{-- Capacité --}}
                <div class="info-row">
                    <div class="info-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="{{ $typeColor }}" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900">
                            {{ number_format($event->attendees_count) }} participant{{ $event->attendees_count > 1 ? 's' : '' }}
                            @if($capacity)
                            <span class="font-normal text-gray-400">/ {{ number_format($capacity) }} places</span>
                            @endif
                        </p>
                        @if($capacity)
                        <div class="mt-2 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full transition-all"
                                 style="width:{{ $pct }}%;background:{{ $pct >= 90 ? '#EF4444' : ($pct >= 60 ? '#F59E0B' : $typeColor) }};"></div>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">{{ $pct }}% des places réservées</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Description --}}
            @if($event->description)
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Description</h2>
                <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $event->description }}</p>
            </div>
            @endif

            {{-- Organisateur --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Organisateur</h2>
                <a href="{{ route('profile.show', $event->creator->id) }}"
                   class="flex items-center gap-3 hover:bg-gray-50 rounded-xl p-2 -mx-2 transition">
                    @if($event->creator->profile?->avatar)
                        <img src="{{ $event->creator->profile->avatar_url }}"
                             class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm flex-shrink-0">
                    @else
                        <div class="avatar-circle" style="width:48px;height:48px;font-size:16px;">
                            {{ strtoupper(substr($event->creator->first_name,0,1).substr($event->creator->last_name,0,1)) }}
                        </div>
                    @endif
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $event->creator->first_name }} {{ $event->creator->last_name }}</p>
                        @if($event->creator->profile?->job_title)
                        <p class="text-xs text-gray-400">{{ $event->creator->profile->job_title }}</p>
                        @endif
                    </div>
                </a>
            </div>
        </div>

        {{-- ── SIDEBAR ── --}}
        <aside class="space-y-4 lg:sticky lg:top-24">

            {{-- CTA --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                @if($isPast)
                <div class="text-center py-4">
                    <p class="text-sm font-semibold text-gray-400">Cet événement est terminé</p>
                </div>

                @elseif($capacity && $event->attendees_count >= $capacity && !$isAttending)
                <div class="w-full py-3 rounded-xl text-sm font-semibold text-center bg-gray-100 text-gray-400">
                    Complet
                </div>

                @elseif($isAttending)
                <div class="text-center mb-4">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold" style="background:#ECFDF5;color:#065F46;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
                        Vous êtes inscrit
                    </span>
                </div>
                <form method="POST" action="{{ route('events.leave', $event->id) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2.5 rounded-xl text-sm font-semibold border transition"
                            style="border-color:#1E8F88;color:#1E8F88;"
                            onmouseover="this.style.background='#E6F7F4'" onmouseout="this.style.background='transparent'">
                        Annuler mon inscription
                    </button>
                </form>

                @else
                <p class="text-xs text-gray-400 mb-3 text-center">Rejoignez cet événement</p>
                <form method="POST" action="{{ route('events.join', $event->id) }}">
                    @csrf
                    <button type="submit" class="w-full py-3 rounded-xl text-sm font-semibold text-white transition shadow-sm"
                            style="background:{{ $typeColor }};"
                            onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                        S'inscrire
                        @if(!$event->is_free)
                        · {{ number_format($event->price, 0) }} €
                        @endif
                    </button>
                </form>
                @endif
            </div>

            {{-- Participants --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">
                    Participants
                    <span class="ml-1 text-xs font-normal text-gray-400">({{ $attendees->count() }})</span>
                </h3>

                @if($attendees->isEmpty())
                <p class="text-xs text-gray-400 text-center py-4">Aucun participant pour le moment.</p>
                @else
                <div class="space-y-3">
                    @foreach($attendees->take(12) as $attendee)
                    <a href="{{ route('profile.show', $attendee->id) }}"
                       class="flex items-center gap-3 hover:bg-gray-50 rounded-xl p-1.5 -mx-1.5 transition">
                        @if($attendee->profile?->avatar)
                            <img src="{{ $attendee->profile->avatar_url }}"
                                 class="w-9 h-9 rounded-full object-cover flex-shrink-0 border border-gray-100">
                        @else
                            <div class="avatar-circle" style="width:36px;height:36px;font-size:12px;">
                                {{ strtoupper(substr($attendee->first_name,0,1).substr($attendee->last_name,0,1)) }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate leading-tight">
                                {{ $attendee->first_name }} {{ $attendee->last_name }}
                                @if($attendee->pivot->role === 'organizer')
                                <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full font-medium" style="background:#E6F7F4;color:#1E8F88;">Orga</span>
                                @endif
                            </p>
                            @if($attendee->profile?->job_title)
                            <p class="text-xs text-gray-400 truncate">{{ $attendee->profile->job_title }}</p>
                            @elseif($attendee->company)
                            <p class="text-xs text-gray-400 truncate">{{ $attendee->company->name }}</p>
                            @endif
                        </div>
                    </a>
                    @endforeach
                    @if($attendees->count() > 12)
                    <p class="text-xs text-center text-gray-400 pt-1">+ {{ $attendees->count() - 12 }} autres</p>
                    @endif
                </div>
                @endif
            </div>

        </aside>
    </div>
</div>
@endsection
