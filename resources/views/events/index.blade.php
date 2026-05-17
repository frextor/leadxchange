@extends('layouts.dashboard')

@section('title', 'Events — LeadXchange')

@push('styles')
<style>
    .ev-sidebar-section { margin-bottom: 1.25rem; }
    .ev-sidebar-title {
        font-size: 11px; font-weight: 700; letter-spacing: .08em;
        text-transform: uppercase; color: #9CA3AF; margin-bottom: .5rem; padding: 0 4px;
    }
    .ev-filter-btn {
        width: 100%; text-align: left; padding: 7px 10px; border-radius: 8px;
        font-size: 13px; font-weight: 500; color: #6B7280; cursor: pointer;
        background: transparent; border: none; display: flex; align-items: center;
        justify-content: space-between; transition: all .15s;
    }
    .ev-filter-btn:hover  { background: #F3F4F6; color: #111827; }
    .ev-filter-btn.active { background: #E6F7F4; color: #1E8F88; font-weight: 600; }
    .ev-filter-btn .count {
        font-size: 11px; font-weight: 600; padding: 2px 7px; border-radius: 20px;
        background: #F3F4F6; color: #6B7280;
    }
    .ev-filter-btn.active .count { background: #C7EDE9; color: #1E8F88; }

    .ev-search {
        height: 40px; padding: 0 14px 0 38px; border-radius: 10px;
        border: 1.5px solid #E5E7EB; background: white; font-size: 13px;
        color: #111827; outline: none; width: 100%; font-family: inherit;
        transition: border-color .15s, box-shadow .15s;
    }
    .ev-search:focus { border-color: #1E8F88; box-shadow: 0 0 0 3px rgba(30,143,136,.1); }

    .ev-card {
        background: white; border-radius: 16px; border: 1px solid #E5E7EB;
        overflow: hidden; transition: box-shadow .2s, transform .2s;
    }
    .ev-card:hover { box-shadow: 0 8px 28px rgba(0,0,0,.09); transform: translateY(-2px); }

    .ev-featured {
        border-radius: 20px; overflow: hidden; position: relative; min-height: 220px;
        display: flex; flex-direction: column; justify-content: flex-end;
    }

    .gr-input {
        height: 40px; padding: 0 14px; border-radius: 10px; border: 1.5px solid #E5E7EB;
        background: white; font-size: 13px; color: #111827; outline: none; width: 100%;
        font-family: inherit; transition: border-color .15s, box-shadow .15s;
    }
    .gr-input:focus { border-color: #1E8F88; box-shadow: 0 0 0 3px rgba(30,143,136,.1); }

    .swatch-label { cursor: pointer; position: relative; }
    .swatch-label input { position: absolute; opacity: 0; pointer-events: none; }
    .swatch-circle {
        width: 28px; height: 28px; border-radius: 50%; display: block; transition: all .15s;
    }
</style>
@endpush

@section('content')
@php
    $categoryLabels = App\Models\Event::$categoryLabels;
    $categoryIcons  = [
        'networking'  => 'M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0',
        'workshop'    => 'M9.663 17h4.673M12 3v1m6.364 1.636-.707.707M21 12h-1M4 12H3m3.343-5.657-.707-.707m2.828 9.9a5 5 0 1 1 7.072 0l-.548.547A3.374 3.374 0 0 0 14 18.469V19a2 2 0 1 1-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
        'conference'  => 'M19 11H5m14 0a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2m14 0V9a2 2 0 0 0-2-2M5 11V9a2 2 0 0 1 2-2m0 0V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2M7 7h10',
        'pitch'       => 'M13 10V3L4 14h7v7l9-11h-7z',
        'after_work'  => 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z',
        'webinar'     => 'M15 10l4.553-2.069A1 1 0 0 1 21 8.82v6.36a1 1 0 0 1-1.447.889L15 14M5 18h8a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2z',
        'community'   => 'M3 12l2-2m0 0 7-7 7 7M5 10v10a1 1 0 0 0 1 1h3m10-11 2 2m-2-2v10a1 1 0 0 0-1 1h-3m-6 0a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1',
    ];
    $typeColors = ['virtual' => '#6366F1', 'in_person' => '#1E8F88', 'hybrid' => '#F59E0B'];
    $typeLabels = ['virtual' => 'Virtual', 'in_person' => 'In-person', 'hybrid' => 'Hybrid'];
@endphp

<div class="max-w-7xl mx-auto px-4 lg:px-8 py-8 flex gap-6 items-start">

    {{-- ════════════════════════════════════════════════════════
         LEFT SIDEBAR
    ════════════════════════════════════════════════════════ --}}
    <aside class="w-60 flex-shrink-0 sticky top-20 space-y-4">

        {{-- Search --}}
        <form method="GET" action="{{ route('events.index') }}" id="filterForm">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" width="14" height="14"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="ev-search" placeholder="Search events…">
            </div>

            {{-- Hidden filter values --}}
            <input type="hidden" name="category"     id="inp_category"     value="{{ request('category') }}">
            <input type="hidden" name="type"         id="inp_type"         value="{{ request('type') }}">
            <input type="hidden" name="when"         id="inp_when"         value="{{ request('when') }}">
            <input type="hidden" name="price_filter" id="inp_price_filter" value="{{ request('price_filter') }}">
        </form>

        {{-- Categories --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <div class="ev-sidebar-section">
                <p class="ev-sidebar-title">Categories</p>
                <div class="space-y-0.5">
                    <button type="button" onclick="setFilter('category','')"
                            class="ev-filter-btn {{ !request('category') ? 'active' : '' }}">
                        <span>All events</span>
                    </button>
                    @foreach($categoryLabels as $key => $label)
                    <button type="button" onclick="setFilter('category','{{ $key }}')"
                            class="ev-filter-btn {{ request('category') === $key ? 'active' : '' }}">
                        <span class="flex items-center gap-2">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="{{ $categoryIcons[$key] ?? 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' }}"/>
                            </svg>
                            {{ $label }}
                        </span>
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- WHEN --}}
            <div class="ev-sidebar-section">
                <p class="ev-sidebar-title">When</p>
                <div class="space-y-0.5">
                    @foreach([''=>'Any time','today'=>'Today','this_week'=>'This week','this_month'=>'This month'] as $val => $lbl)
                    <button type="button" onclick="setFilter('when','{{ $val }}')"
                            class="ev-filter-btn {{ request('when', '') === $val ? 'active' : '' }}">
                        {{ $lbl }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- PRICE --}}
            <div class="ev-sidebar-section mb-0">
                <p class="ev-sidebar-title">Price</p>
                <div class="space-y-0.5">
                    @foreach([''=>'Any price','free'=>'Free','paid'=>'Paid'] as $val => $lbl)
                    <button type="button" onclick="setFilter('price_filter','{{ $val }}')"
                            class="ev-filter-btn {{ request('price_filter', '') === $val ? 'active' : '' }}">
                        {{ $lbl }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Dark CTA --}}
        <button onclick="document.getElementById('createEventModal').classList.remove('hidden')"
                class="w-full py-3 px-4 rounded-2xl text-sm font-semibold text-white text-center transition flex items-center justify-center gap-2"
                style="background:#111827;" onmouseover="this.style.background='#1F2937'" onmouseout="this.style.background='#111827'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Organize an event
        </button>

    </aside>

    {{-- ════════════════════════════════════════════════════════
         MAIN CONTENT
    ════════════════════════════════════════════════════════ --}}
    <div class="flex-1 min-w-0">

        {{-- Flash messages --}}
        @foreach(['success' => 'E6F7F4|1E8F88', 'error' => 'FEF2F2|EF4444', 'info' => 'EFF6FF|3B82F6'] as $type => $colors)
        @if(session($type))
        @php [$bg, $fg] = explode('|', $colors); @endphp
        <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium" style="background:#{{ $bg }};color:#{{ $fg }};">
            {{ session($type) }}
        </div>
        @endif
        @endforeach

        @if($upcoming->isEmpty() && $past->isEmpty())

        {{-- Empty state --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-16 text-center">
            <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#E6F7F4;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="1.5">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <p class="font-semibold text-gray-700 text-base">No events found</p>
            <p class="text-sm text-gray-400 mt-1">Be the first to organize a networking event!</p>
            <button onclick="document.getElementById('createEventModal').classList.remove('hidden')"
                    class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                    style="background:#111827;" onmouseover="this.style.background='#1F2937'" onmouseout="this.style.background='#111827'">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                Organize an event
            </button>
        </div>

        @else

        {{-- ── Featured event ── --}}
        @if($featured)
        @php
            $featuredColor  = $typeColors[$featured->type] ?? $featured->cover_color;
            $featuredIsReg  = in_array($featured->id, $attendingEventIds);
        @endphp
        <div class="ev-featured mb-8" style="background: linear-gradient(140deg, {{ $featured->cover_color }}, {{ $featured->cover_color }}99);">
            @if($featured->cover_image)
            <img src="{{ Storage::url($featured->cover_image) }}" alt="{{ $featured->title }}"
                 class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0" style="background:linear-gradient(to top, rgba(0,0,0,.75) 50%, rgba(0,0,0,.15));"></div>
            @else
            <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 70% 30%,white 1px,transparent 1px);background-size:20px 20px;"></div>
            @endif

            <div class="relative p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex flex-wrap gap-2 mb-3">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold text-white" style="background:rgba(0,0,0,.35);">
                                {{ $typeLabels[$featured->type] ?? $featured->type }}
                            </span>
                            @if($featured->category && isset($categoryLabels[$featured->category]))
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold text-white" style="background:rgba(0,0,0,.25);">
                                {{ $categoryLabels[$featured->category] }}
                            </span>
                            @endif
                            @if($featured->is_free)
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background:#10B981;color:white;">Free</span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background:rgba(0,0,0,.3);color:white;">
                                {{ number_format($featured->price, 2) }} MAD
                            </span>
                            @endif
                        </div>
                        <h2 class="text-xl font-bold text-white leading-snug mb-2">{{ $featured->title }}</h2>
                        <div class="flex flex-wrap items-center gap-3 text-sm text-white/80">
                            <span class="flex items-center gap-1.5">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                {{ $featured->starts_at->isoFormat('ddd, D MMM · H:mm') }}
                            </span>
                            @if($featured->location)
                            <span class="flex items-center gap-1.5">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                </svg>
                                {{ $featured->location }}
                            </span>
                            @endif
                            <span class="flex items-center gap-1.5">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                                </svg>
                                {{ number_format($featured->attendees_count) }} attending
                            </span>
                        </div>
                    </div>

                    <div class="flex-shrink-0">
                        @if($featuredIsReg)
                        <form method="POST" action="{{ route('events.leave', $featured->id) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold transition"
                                    style="background:rgba(255,255,255,.2);color:white;border:1.5px solid rgba(255,255,255,.4);"
                                    onmouseover="this.style.background='rgba(255,255,255,.3)'" onmouseout="this.style.background='rgba(255,255,255,.2)'">
                                Registered ✓
                            </button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('events.join', $featured->id) }}">
                            @csrf
                            <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                                    style="background:#1E8F88;" onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                                Register now
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Upcoming ── --}}
        @if($upcoming->count() > 1)
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-4">
                <h2 class="text-base font-semibold text-gray-900">Upcoming</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold" style="background:#E6F7F4;color:#1E8F88;">
                    {{ $upcoming->count() }}
                </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($upcoming->skip(1) as $event)
                    @include('events._card', ['event' => $event, 'attendingEventIds' => $attendingEventIds])
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── Past ── --}}
        @if($past->isNotEmpty())
        <div>
            <div class="flex items-center gap-2 mb-4">
                <h2 class="text-base font-semibold text-gray-900">Past events</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold" style="background:#F3F4F6;color:#6B7280;">
                    {{ $past->count() }}
                </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 opacity-80">
                @foreach($past as $event)
                    @include('events._card', ['event' => $event, 'attendingEventIds' => $attendingEventIds])
                @endforeach
            </div>
        </div>
        @endif

        @endif

    </div>{{-- /main --}}
</div>

{{-- ════════════════════════════════════════════════════════
     CREATE EVENT MODAL
════════════════════════════════════════════════════════ --}}
<div id="createEventModal"
     class="{{ $errors->any() ? '' : 'hidden' }} fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,0.5);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[92vh] overflow-y-auto">

        {{-- Modal header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
            <h2 class="font-semibold text-gray-900">Organize an event</h2>
            <button type="button" onclick="document.getElementById('createEventModal').classList.add('hidden')"
                    class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 transition">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
            @csrf

            {{-- Title --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-400">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                       placeholder="Ex: B2B Networking Casablanca Spring Edition"
                       required maxlength="150"
                       class="gr-input @error('title') border-red-400 @enderror">
                @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="description" rows="3" placeholder="What is this event about?" maxlength="1000"
                          class="gr-input" style="height:auto;padding-top:10px;padding-bottom:10px;resize:none;">{{ old('description') }}</textarea>
            </div>

            {{-- Type + Category --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Type <span class="text-red-400">*</span></label>
                    <select name="type" id="evType" required class="gr-input" style="appearance:none;" onchange="toggleTypeFields()">
                        <option value="virtual"   {{ old('type','virtual') === 'virtual'   ? 'selected' : '' }}>Virtual</option>
                        <option value="in_person" {{ old('type') === 'in_person' ? 'selected' : '' }}>In-person</option>
                        <option value="hybrid"    {{ old('type') === 'hybrid'    ? 'selected' : '' }}>Hybrid</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                    <select name="category" class="gr-input" style="appearance:none;">
                        <option value="">— None —</option>
                        @foreach($categoryLabels as $key => $label)
                        <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Location --}}
            <div id="fieldLocation" class="{{ old('type','virtual') === 'virtual' ? 'hidden' : '' }}">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Location</label>
                <input type="text" name="location" value="{{ old('location') }}"
                       placeholder="Ex: 23 Rue des Entreprises, Casablanca"
                       maxlength="255" class="gr-input @error('location') border-red-400 @enderror">
            </div>

            {{-- Meeting link --}}
            <div id="fieldMeetingLink" class="{{ old('type') === 'in_person' ? 'hidden' : '' }}">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Meeting link</label>
                <input type="url" name="meeting_link" value="{{ old('meeting_link') }}"
                       placeholder="https://meet.google.com/…"
                       maxlength="500" class="gr-input @error('meeting_link') border-red-400 @enderror">
            </div>

            {{-- Dates --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Start date <span class="text-red-400">*</span></label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"
                           required class="gr-input @error('starts_at') border-red-400 @enderror">
                    @error('starts_at') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">End date</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}"
                           class="gr-input">
                </div>
            </div>

            {{-- Sector + City --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Sector</label>
                    <select name="sector_id" class="gr-input" style="appearance:none;">
                        <option value="">— Any —</option>
                        @foreach($sectors as $sector)
                        <option value="{{ $sector->id }}" {{ old('sector_id') == $sector->id ? 'selected' : '' }}>
                            {{ $sector->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                    <select name="city_id" class="gr-input" style="appearance:none;">
                        <option value="">— Any —</option>
                        @foreach($cities as $city)
                        <option value="{{ $city->id }}"
                            {{ old('city_id', auth()->user()->city_id) == $city->id ? 'selected' : '' }}>
                            {{ $city->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('city_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Price --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Price (MAD)</label>
                    <input type="number" name="price" value="{{ old('price') }}"
                           placeholder="0 = Free" min="0" step="0.01" class="gr-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Max attendees</label>
                    <input type="number" name="max_attendees" value="{{ old('max_attendees') }}"
                           placeholder="Unlimited" min="1" class="gr-input">
                </div>
            </div>

            {{-- Cover: photo or color --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cover</label>
                <div class="flex gap-2 mb-3">
                    <button type="button" id="btnCoverPhoto"
                            onclick="switchCoverMode('photo')"
                            class="flex-1 py-2 rounded-lg text-xs font-semibold border transition cover-mode-btn active-mode"
                            style="border-color:#1E8F88;background:#E6F7F4;color:#1E8F88;">
                        Add photo
                    </button>
                    <button type="button" id="btnCoverColor"
                            onclick="switchCoverMode('color')"
                            class="flex-1 py-2 rounded-lg text-xs font-semibold border border-gray-200 text-gray-600 transition cover-mode-btn">
                        Choose color
                    </button>
                </div>

                <div id="coverPhotoArea">
                    <label class="block w-full cursor-pointer border-2 border-dashed border-gray-200 rounded-xl p-5 text-center hover:border-teal-400 transition" id="photoDropZone">
                        <input type="file" name="cover_image" id="coverImageInput" accept="image/*" class="hidden" onchange="previewPhoto(this)">
                        <div id="photoPlaceholder">
                            <svg class="mx-auto mb-2 text-gray-300" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                <path d="m21 15-5-5L5 21"/>
                            </svg>
                            <p class="text-sm text-gray-400">Click to upload a cover photo</p>
                            <p class="text-xs text-gray-300 mt-0.5">PNG, JPG up to 2MB</p>
                        </div>
                        <img id="photoPreview" src="" alt="" class="hidden w-full h-28 object-cover rounded-lg">
                    </label>
                </div>

                <div id="coverColorArea" class="hidden">
                    <div class="flex gap-2 flex-wrap">
                        @foreach(['#1E8F88','#6366F1','#F59E0B','#EF4444','#8B5CF6','#EC4899','#10B981','#3B82F6'] as $color)
                        <label class="swatch-label">
                            <input type="radio" name="cover_color" value="{{ $color }}"
                                   {{ (old('cover_color','#1E8F88') === $color) ? 'checked' : '' }}
                                   onchange="updateSwatches()">
                            <span class="swatch-circle"
                                  style="background:{{ $color }};box-shadow:{{ (old('cover_color','#1E8F88') === $color) ? '0 0 0 2px white,0 0 0 4px '.$color : 'none' }};">
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-2">
                <button type="button"
                        onclick="document.getElementById('createEventModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                        style="background:#111827;" onmouseover="this.style.background='#1F2937'" onmouseout="this.style.background='#111827'">
                    Create event
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    /* ── Sidebar filters ── */
    function setFilter(name, val) {
        document.getElementById('inp_' + name).value = val;
        document.getElementById('filterForm').submit();
    }

    /* ── Modal type toggle ── */
    function toggleTypeFields() {
        const type = document.getElementById('evType').value;
        document.getElementById('fieldLocation').classList.toggle('hidden',   type === 'virtual');
        document.getElementById('fieldMeetingLink').classList.toggle('hidden', type === 'in_person');
    }

    /* ── Cover mode toggle ── */
    function switchCoverMode(mode) {
        const isPhoto = mode === 'photo';
        document.getElementById('coverPhotoArea').classList.toggle('hidden', !isPhoto);
        document.getElementById('coverColorArea').classList.toggle('hidden',  isPhoto);

        document.getElementById('btnCoverPhoto').classList.toggle('active-mode', isPhoto);
        document.getElementById('btnCoverColor').classList.toggle('active-mode', !isPhoto);

        const activeStyle  = 'border-color:#1E8F88;background:#E6F7F4;color:#1E8F88;';
        const defaultStyle = 'border-color:#E5E7EB;background:white;color:#6B7280;';
        document.getElementById('btnCoverPhoto').style.cssText = isPhoto  ? activeStyle : defaultStyle;
        document.getElementById('btnCoverColor').style.cssText = !isPhoto ? activeStyle : defaultStyle;

        if (!isPhoto) document.getElementById('coverImageInput').value = '';
    }

    /* ── Photo preview ── */
    function previewPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('photoPlaceholder').classList.add('hidden');
                const img = document.getElementById('photoPreview');
                img.src = e.target.result;
                img.classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    /* ── Color swatches ── */
    function updateSwatches() {
        document.querySelectorAll('.swatch-label').forEach(label => {
            const input = label.querySelector('input');
            const span  = label.querySelector('.swatch-circle');
            span.style.boxShadow = input.checked
                ? `0 0 0 2px white, 0 0 0 4px ${input.value}`
                : 'none';
        });
    }
    document.querySelectorAll('.swatch-label input').forEach(i => i.addEventListener('change', updateSwatches));

    /* ── Close modal on backdrop click ── */
    document.getElementById('createEventModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
</script>
@endpush
