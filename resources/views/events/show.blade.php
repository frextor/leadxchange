@extends('layouts.app')

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
    $typeColors   = ['virtual' => '#6366F1', 'in_person' => '#1E8F88', 'hybrid' => '#F59E0B'];
    $typeLabels   = ['virtual' => 'Virtual', 'in_person' => 'In-person', 'hybrid' => 'Hybrid'];
    $typeColor    = $typeColors[$event->type] ?? $event->cover_color;
    $typeLabel    = $typeLabels[$event->type] ?? $event->type;
    $isPast       = $event->starts_at->isPast();
    $catLabel     = App\Models\Event::categoryLabels()[$event->category ?? ''] ?? null;
    $capacity     = $event->max_attendees;
    $pct          = $capacity ? min(100, round($event->attendees_count / $capacity * 100)) : null;
    $isOrganizer  = $event->created_by === auth()->id();
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- ── HERO COVER ── --}}
    <div class="rounded-2xl overflow-hidden mb-6 shadow-sm border border-gray-200">
        <div class="h-48 sm:h-64 relative"
             @unless($event->cover_image) style="background:linear-gradient(135deg,{{ $event->cover_color }},{{ $event->cover_color }}99);" @endunless>
            @if($event->cover_image)
                <img src="{{ Storage::disk('public')->url($event->cover_image) }}" alt="{{ $event->title }}"
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
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">{{ $event->title }}</h1>
                    @if($isOrganizer)
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold" style="background:#FEF3C7;color:#92400E;">Organizer</span>
                    @endif
                    @if(!$event->is_public)
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold" style="background:#EDE9FE;color:#5B21B6;">Privé</span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-gray-400">
                    @if($event->creator)
                    <span>Organized by <strong class="text-gray-700">{{ $event->creator->first_name }} {{ $event->creator->last_name }}</strong></span>
                    @endif
                    @if($event->sector)
                    <span>·</span>
                    <span class="px-2 py-0.5 rounded-full font-medium" style="background:#E6F7F4;color:#1E8F88;">{{ $event->sector->name }}</span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('events.index') }}"
                   class="px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                    ← Events
                </a>
                @if($isOrganizer && !$isPast)
                <button type="button" onclick="openShowInviteModal()"
                        class="px-4 py-2 rounded-xl text-sm font-semibold text-white transition flex items-center gap-1.5"
                        style="background:#1E8F88;" onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <line x1="19" y1="8" x2="19" y2="14"/>
                        <line x1="22" y1="11" x2="16" y2="11"/>
                    </svg>
                    Invite
                </button>
                @if($organizerGroups->isNotEmpty())
                <button type="button" onclick="openInviteGroupModal()"
                        class="px-4 py-2 rounded-xl text-sm font-semibold text-white transition flex items-center gap-1.5"
                        style="background:#6366F1;" onmouseover="this.style.background='#4F46E5'" onmouseout="this.style.background='#6366F1'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    Inviter un groupe
                </button>
                @endif
                <form method="POST" action="{{ route('events.destroy', $event->id) }}"
                      onsubmit="return confirm('Delete this event? This action cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-xl text-sm font-semibold border transition"
                            style="border-color:#EF4444;color:#EF4444;"
                            onmouseover="this.style.background='#FEF2F2'" onmouseout="this.style.background='transparent'">
                        Delete event
                    </button>
                </form>
                @endif
            </div>
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
                        <p class="text-sm font-semibold text-gray-900">{{ currency_format($event->price, 2) }}</p>
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
            @if($event->creator)
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
            @endif
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
                @if(!$event->is_public)
                <div class="text-center py-4">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full" style="background:#EDE9FE;color:#5B21B6;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Sur invitation uniquement
                    </span>
                </div>
                @elseif(auth()->user()->canFeature('can_participate_events'))
                <p class="text-xs text-gray-400 mb-3 text-center">Rejoignez cet événement</p>
                @if($event->is_free)
                <form method="POST" action="{{ route('events.join', $event->id) }}">
                    @csrf
                    <button type="submit" class="w-full py-3 rounded-xl text-sm font-semibold text-white transition shadow-sm"
                            style="background:{{ $typeColor }};"
                            onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                        S'inscrire — Gratuit
                    </button>
                </form>
                @else
                <button type="button" onclick="openStripeModal()"
                        class="w-full py-3 rounded-xl text-sm font-semibold text-white transition shadow-sm flex items-center justify-center gap-2"
                        style="background:{{ $typeColor }};"
                        onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
                    Payer · {{ currency_format($event->price) }}
                </button>
                @endif {{-- is_free --}}
                @else
                <div class="text-center py-2">
                    <p class="text-xs text-gray-500 mb-3">Participation aux événements réservée aux plans Premium.</p>
                    <button type="button" onclick="openUpgradeModal('can_participate_events')"
                            class="block w-full py-3 rounded-xl text-sm font-semibold border border-dashed transition cursor-pointer"
                            style="border-color:#6366F1;color:#6366F1;background:transparent;">
                        <svg class="inline mr-1.5" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Upgrade pour participer
                    </button>
                </div>
                @endif {{-- is_public / canFeature / else --}}
                @endif {{-- isPast / isFull / isAttending / else --}}
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
                <div class="space-y-2">
                    @foreach($attendees->take(12) as $attendee)
                    <div class="flex items-center gap-3 group/att rounded-xl p-1.5 -mx-1.5 hover:bg-gray-50 transition">
                        <a href="{{ route('profile.show', $attendee->id) }}" class="flex items-center gap-3 flex-1 min-w-0">
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
                                    <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full font-medium" style="background:#FEF3C7;color:#92400E;">Organizer</span>
                                    @endif
                                </p>
                                @if($attendee->profile?->job_title)
                                <p class="text-xs text-gray-400 truncate">{{ $attendee->profile->job_title }}</p>
                                @elseif($attendee->company)
                                <p class="text-xs text-gray-400 truncate">{{ $attendee->company->name }}</p>
                                @endif
                            </div>
                        </a>
                        @if($isOrganizer && $attendee->id !== auth()->id())
                        <form method="POST" action="{{ route('events.attendees.destroy', [$event->id, $attendee->id]) }}"
                              class="opacity-0 group-hover/att:opacity-100 transition flex-shrink-0">
                            @csrf @method('DELETE')
                            <button type="submit" title="Remove attendee"
                                    class="w-6 h-6 flex items-center justify-center rounded-full text-gray-300 hover:text-red-400 hover:bg-red-50 transition">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>
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

{{-- ── STRIPE PAYMENT MODAL ── --}}
@if(!$event->is_free && !$isAttending && !$isPast)
<div id="stripeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.5);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-100" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-gray-900 text-sm">Paiement sécurisé</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $event->title }}</p>
                </div>
                <button type="button" onclick="closeStripeModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 transition">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div class="px-6 py-5 space-y-4">

            {{-- Amount --}}
            <div class="flex items-center justify-between p-3.5 rounded-xl bg-gray-50 border border-gray-100">
                <span class="text-sm text-gray-600">Montant total</span>
                <span class="text-lg font-bold text-gray-900">{{ currency_format($event->price, 2) }}</span>
            </div>

            {{-- Stripe Elements --}}
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Carte bancaire</label>
                <div id="card-element" class="border border-gray-200 rounded-xl px-3.5 py-3 bg-white focus-within:border-emerald-400 transition"></div>
                <div id="card-errors" class="text-xs text-red-500 mt-1.5 hidden"></div>
            </div>

            {{-- State messages --}}
            <div id="stripeProcessing" class="hidden flex items-center gap-2 px-3 py-2 rounded-xl bg-blue-50 text-blue-700 text-xs font-medium">
                <svg class="animate-spin w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-dasharray="40" stroke-dashoffset="10"/></svg>
                Paiement en cours…
            </div>
            <div id="stripeSuccess" class="hidden flex items-center gap-2 px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-medium">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
                Paiement confirmé ! Inscription en cours…
            </div>

            {{-- Submit --}}
            <button type="button" id="stripePayBtn" onclick="confirmStripePayment()"
                    class="w-full py-3 rounded-xl text-sm font-semibold text-white flex items-center justify-center gap-2 transition"
                    style="background:linear-gradient(135deg,#10B981,#059669);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
                Payer {{ currency_format($event->price, 2) }}
            </button>

            <p class="text-center text-[10px] text-gray-300 flex items-center justify-center gap-1.5">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Paiement sécurisé par Stripe
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
(async function() {
    const EVENT_ID   = {{ $event->id }};
    const JOIN_URL   = '{{ route('events.join', $event->id) }}';
    const CSRF_TOKEN = document.querySelector('meta[name=csrf-token]').content;
    let stripe, cardElement, clientSecret;

    async function initStripe() {
        try {
            const cfgRes = await fetch('/api/payments/config', {
                headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + window.API_TOKEN },
                credentials: 'same-origin',
            });
            const cfg = await cfgRes.json();
            if (!cfg.publishable_key) throw new Error('No Stripe key');

            stripe = Stripe(cfg.publishable_key);
            const elements = stripe.elements();
            cardElement = elements.create('card', {
                style: { base: { fontSize: '14px', color: '#111827', fontFamily: 'Inter, sans-serif', '::placeholder': { color: '#9CA3AF' } } }
            });
            cardElement.mount('#card-element');
            cardElement.on('change', function(e) {
                const errEl = document.getElementById('card-errors');
                if (e.error) { errEl.textContent = e.error.message; errEl.classList.remove('hidden'); }
                else { errEl.textContent = ''; errEl.classList.add('hidden'); }
            });
        } catch(e) {
            document.getElementById('card-errors').textContent = 'Impossible de charger Stripe. Réessayez.';
            document.getElementById('card-errors').classList.remove('hidden');
        }
    }

    window.openStripeModal = async function() {
        document.getElementById('stripeModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (!stripe) await initStripe();

        // Get PaymentIntent
        try {
            const res = await fetch('/api/payments/events/' + EVENT_ID + '/intent', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + window.API_TOKEN, 'X-CSRF-TOKEN': CSRF_TOKEN },
                credentials: 'same-origin',
            });
            const data = await res.json();
            clientSecret = data.client_secret;
        } catch {
            document.getElementById('card-errors').textContent = 'Erreur lors de la création du paiement.';
            document.getElementById('card-errors').classList.remove('hidden');
        }
    };

    window.closeStripeModal = function() {
        document.getElementById('stripeModal').classList.add('hidden');
        document.body.style.overflow = '';
    };

    window.confirmStripePayment = async function() {
        if (!stripe || !cardElement || !clientSecret) return;
        const payBtn = document.getElementById('stripePayBtn');
        const processing = document.getElementById('stripeProcessing');
        const errEl = document.getElementById('card-errors');

        payBtn.disabled = true;
        payBtn.style.opacity = '.6';
        processing.classList.remove('hidden');
        errEl.classList.add('hidden');

        const { paymentIntent, error } = await stripe.confirmCardPayment(clientSecret, {
            payment_method: { card: cardElement },
        });

        processing.classList.add('hidden');

        if (error) {
            errEl.textContent = error.message;
            errEl.classList.remove('hidden');
            payBtn.disabled = false;
            payBtn.style.opacity = '1';
            return;
        }

        if (paymentIntent && paymentIntent.status === 'succeeded') {
            document.getElementById('stripeSuccess').classList.remove('hidden');
            // Join the event via web form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = JOIN_URL;
            const csrf = document.createElement('input');
            csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = CSRF_TOKEN;
            form.appendChild(csrf);
            document.body.appendChild(form);
            setTimeout(() => form.submit(), 1500);
        }
    };

    document.getElementById('stripeModal').addEventListener('click', function(e) {
        if (e.target === this) window.closeStripeModal();
    });
})();
</script>
@endpush
@endif

{{-- ── INVITE MODAL (organizer only) ── --}}
@if($isOrganizer && !$isPast)
<div id="inviteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Invite to "{{ Str::limit($event->title, 30) }}"</h2>
            <button type="button" onclick="document.getElementById('inviteModal').classList.add('hidden')"
                    class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 transition">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('events.invite', $event->id) }}" id="inviteForm" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Search a connection</label>
                <input type="text" id="inviteSearch" placeholder="Name…" oninput="filterConnections(this.value)"
                       class="w-full h-10 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-teal-500 transition">
            </div>
            <div id="connectionList" class="space-y-1 max-h-52 overflow-y-auto"></div>
            <input type="hidden" name="user_id" id="inviteUserId">
            <div id="inviteSelected" class="hidden px-3 py-2 rounded-xl text-sm font-medium" style="background:#E6F7F4;color:#1E8F88;"></div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="document.getElementById('inviteModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" id="inviteSubmitBtn" disabled
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition disabled:opacity-40"
                        style="background:#1E8F88;" onmouseover="if(!this.disabled)this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                    Send invitation
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const connections = @json($eventConnections);

function renderConnections(list) {
    const el = document.getElementById('connectionList');
    if (!list.length) {
        el.innerHTML = '<p class="text-xs text-gray-400 text-center py-4">No connections available to invite</p>';
        return;
    }
    el.innerHTML = list.map(u => `
        <button type="button" onclick="selectUser(${u.id},'${u.name.replace(/'/g,"\\'")}','${(u.job_title||'').replace(/'/g,"\\'")}')"
            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 transition text-left">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                 style="background:linear-gradient(135deg,#34d4bf,#1E8F88);">${u.name.charAt(0).toUpperCase()}</div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">${u.name}</p>
                ${u.job_title ? `<p class="text-xs text-gray-400 truncate">${u.job_title}</p>` : ''}
            </div>
        </button>
    `).join('');
}

function filterConnections(q) {
    renderConnections(q ? connections.filter(u => u.name.toLowerCase().includes(q.toLowerCase())) : connections);
}

function selectUser(id, name, jobTitle) {
    document.getElementById('inviteUserId').value = id;
    document.getElementById('inviteSubmitBtn').disabled = false;
    const sel = document.getElementById('inviteSelected');
    sel.textContent = '✓ ' + name + (jobTitle ? ' — ' + jobTitle : '');
    sel.classList.remove('hidden');
    document.getElementById('connectionList').innerHTML = '';
    document.getElementById('inviteSearch').value = name;
}

function openShowInviteModal() {
    document.getElementById('inviteSearch').value = '';
    document.getElementById('inviteUserId').value = '';
    document.getElementById('inviteSubmitBtn').disabled = true;
    document.getElementById('inviteSelected').classList.add('hidden');
    renderConnections(connections);
    document.getElementById('inviteModal').classList.remove('hidden');
    setTimeout(() => document.getElementById('inviteSearch').focus(), 50);
}

document.getElementById('inviteModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
});
</script>
@endpush

@if($organizerGroups->isNotEmpty())
{{-- ── INVITE GROUP MODAL ── --}}
<div id="inviteGroupModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Inviter un groupe</h2>
            <button type="button" onclick="document.getElementById('inviteGroupModal').classList.add('hidden')"
                    class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 transition">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('events.invite-group', $event->id) }}" class="px-6 py-5 space-y-4">
            @csrf
            <p class="text-xs text-gray-500">Sélectionnez un de vos groupes — tous les membres qui ne participent pas encore seront invités.</p>
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
                <button type="button" onclick="document.getElementById('inviteGroupModal').classList.add('hidden')"
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

@push('scripts')
<script>
function openInviteGroupModal() {
    document.getElementById('inviteGroupModal').classList.remove('hidden');
}
document.getElementById('inviteGroupModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
});
</script>
@endpush
@endif

@endif
@endsection
