@extends('layouts.dashboard')

@section('title', 'Events — LeadXchange')

@push('styles')
<style>
    .gr-input {
        height: 40px; padding: 0 14px; border-radius: 10px;
        border: 1px solid #E5E7EB; background: white;
        font-size: 14px; color: #111827; outline: none; width: 100%;
        font-family: inherit; transition: border-color .15s, box-shadow .15s;
    }
    .gr-input:focus { border-color: #1E8F88; box-shadow: 0 0 0 3px rgba(30,143,136,0.1); }
    .cat-btn {
        width: 100%; text-align: left; padding: 8px 12px; border-radius: 8px;
        font-size: 13px; font-weight: 500; color: #6B7280;
        transition: all .15s; cursor: pointer; background: transparent; border: none;
        display: flex; align-items: center; justify-content: space-between;
    }
    .cat-btn:hover { background: #F3F4F6; color: #111827; }
    .cat-btn.active { background: #E6F7F4; color: #1E8F88; font-weight: 600; }
    .group-card {
        background: white; border-radius: 16px; border: 1px solid #E5E7EB;
        overflow: hidden; transition: box-shadow .2s, transform .2s;
    }
    .group-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.08); transform: translateY(-2px); }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 tracking-tight">Events</h1>
            <p class="text-sm text-gray-500 mt-1">Discover professional events and networking opportunities</p>
        </div>
        <button onclick="document.getElementById('createEventModal').classList.remove('hidden')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition"
            style="background:#1E8F88;" onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            Create an event
        </button>
    </div>

    {{-- ── CREATE EVENT MODAL ── --}}
    <div id="createEventModal" class="{{ $errors->any() ? '' : 'hidden' }} fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Create an event</h2>
                <button type="button" onclick="document.getElementById('createEventModal').classList.add('hidden')"
                    class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 transition">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('events.store') }}" class="px-6 py-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-400">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="Ex: B2B Networking Casablanca Spring Edition"
                        required maxlength="150" class="gr-input @error('title') border-red-400 @enderror">
                    @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="3" placeholder="What is this event about?" maxlength="1000"
                        class="gr-input" style="height:auto;padding-top:10px;padding-bottom:10px;resize:none;">{{ old('description') }}</textarea>
                    @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Type <span class="text-red-400">*</span></label>
                    <select name="type" id="eventType" required class="gr-input" style="appearance:none;" onchange="toggleTypeFields()">
                        <option value="virtual"  {{ old('type', 'virtual') === 'virtual'  ? 'selected' : '' }}>Virtual</option>
                        <option value="in_person"{{ old('type') === 'in_person' ? 'selected' : '' }}>In-person</option>
                        <option value="hybrid"   {{ old('type') === 'hybrid'   ? 'selected' : '' }}>Hybrid</option>
                    </select>
                    @error('type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div id="fieldLocation" class="{{ old('type', 'virtual') === 'virtual' ? 'hidden' : '' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Location</label>
                    <input type="text" name="location" value="{{ old('location') }}" placeholder="Ex: 23 Rue des Entreprises, Casablanca"
                        maxlength="255" class="gr-input @error('location') border-red-400 @enderror">
                    @error('location') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div id="fieldMeetingLink" class="{{ old('type') === 'in_person' ? 'hidden' : '' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Meeting link</label>
                    <input type="url" name="meeting_link" value="{{ old('meeting_link') }}" placeholder="https://meet.google.com/…"
                        maxlength="500" class="gr-input @error('meeting_link') border-red-400 @enderror">
                    @error('meeting_link') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

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
                            class="gr-input @error('ends_at') border-red-400 @enderror">
                        @error('ends_at') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Sector</label>
                        <select name="sector_id" class="gr-input" style="appearance:none;">
                            <option value="">— Any sector —</option>
                            @foreach($sectors as $sector)
                                <option value="{{ $sector->id }}" {{ old('sector_id') == $sector->id ? 'selected' : '' }}>
                                    {{ $sector->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Max attendees</label>
                        <input type="number" name="max_attendees" value="{{ old('max_attendees') }}"
                            placeholder="Unlimited" min="1" class="gr-input">
                        @error('max_attendees') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cover color</label>
                    <div class="flex gap-2 flex-wrap">
                        @foreach(['#1E8F88','#6366F1','#F59E0B','#EF4444','#8B5CF6','#EC4899','#10B981','#3B82F6'] as $color)
                        <label class="cursor-pointer color-swatch" style="position:relative;">
                            <input type="radio" name="cover_color" value="{{ $color }}" class="sr-only"
                                {{ (old('cover_color', '#1E8F88') === $color) ? 'checked' : '' }}
                                onchange="updateSwatches()">
                            <span class="block w-7 h-7 rounded-full transition-all"
                                  style="background:{{ $color }}; box-shadow: {{ (old('cover_color', '#1E8F88') === $color) ? '0 0 0 2px white, 0 0 0 4px '.$color : 'none' }};"></span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('createEventModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                        style="background:#1E8F88;" onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="flex gap-6 items-start">

        {{-- ── LEFT SIDEBAR ── --}}
        <aside class="w-64 flex-shrink-0 sticky top-20">
            <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">

                <form method="GET" action="{{ route('events.index') }}" id="filterForm">
                    <div class="relative mb-4">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search events…" class="gr-input pl-9"
                            oninput="document.getElementById('filterForm').submit()">
                    </div>

                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">Type</p>
                    <div class="space-y-0.5 mb-4">
                        <button type="button" onclick="setType('')"
                            class="cat-btn {{ !request('type') ? 'active' : '' }}">
                            <span>All types</span>
                        </button>
                        <button type="button" onclick="setType('virtual')"
                            class="cat-btn {{ request('type') === 'virtual' ? 'active' : '' }}">
                            <span>Virtual</span>
                        </button>
                        <button type="button" onclick="setType('in_person')"
                            class="cat-btn {{ request('type') === 'in_person' ? 'active' : '' }}">
                            <span>In-person</span>
                        </button>
                        <button type="button" onclick="setType('hybrid')"
                            class="cat-btn {{ request('type') === 'hybrid' ? 'active' : '' }}">
                            <span>Hybrid</span>
                        </button>
                    </div>

                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">Sector</p>
                    <div class="space-y-0.5">
                        <button type="button" onclick="setCategory('')"
                            class="cat-btn {{ !request('category') ? 'active' : '' }}">
                            <span>All sectors</span>
                        </button>
                        @foreach($sectors as $sector)
                        <button type="button" onclick="setCategory({{ $sector->id }})"
                            class="cat-btn {{ request('category') == $sector->id ? 'active' : '' }}">
                            <span>{{ $sector->name }}</span>
                        </button>
                        @endforeach
                    </div>

                    <input type="hidden" name="type"     id="typeInput"     value="{{ request('type') }}">
                    <input type="hidden" name="category" id="categoryInput" value="{{ request('category') }}">
                </form>
            </div>
        </aside>

        {{-- ── MAIN CONTENT ── --}}
        <div class="flex-1 min-w-0">

            @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium" style="background:#E6F7F4;color:#1E8F88;">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-red-50 text-red-600">
                {{ session('error') }}
            </div>
            @endif

            @if($upcoming->isEmpty() && $past->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 p-16 text-center">
                <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#E6F7F4;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="1.5">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <p class="font-semibold text-gray-700">No events found</p>
                <p class="text-sm text-gray-400 mt-1">Be the first to create a networking event!</p>
            </div>

            @else

            {{-- Upcoming --}}
            @if($upcoming->isNotEmpty())
            <div class="mb-8">
                <div class="flex items-center gap-2 mb-4">
                    <h2 class="text-base font-semibold text-gray-900">Upcoming</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold" style="background:#E6F7F4;color:#1E8F88;">{{ $upcoming->count() }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($upcoming as $event)
                        @include('events._card', ['event' => $event, 'attendingEventIds' => $attendingEventIds])
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Past --}}
            @if($past->isNotEmpty())
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <h2 class="text-base font-semibold text-gray-900">Past events</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold" style="background:#F3F4F6;color:#6B7280;">{{ $past->count() }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($past as $event)
                        @include('events._card', ['event' => $event, 'attendingEventIds' => $attendingEventIds])
                    @endforeach
                </div>
            </div>
            @endif

            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function setType(val) {
        document.getElementById('typeInput').value = val;
        document.getElementById('filterForm').submit();
    }

    function setCategory(id) {
        document.getElementById('categoryInput').value = id;
        document.getElementById('filterForm').submit();
    }

    function toggleTypeFields() {
        const type = document.getElementById('eventType').value;
        const loc  = document.getElementById('fieldLocation');
        const link = document.getElementById('fieldMeetingLink');
        loc.classList.toggle('hidden',  type === 'virtual');
        link.classList.toggle('hidden', type === 'in_person');
    }

    function updateSwatches() {
        document.querySelectorAll('.color-swatch').forEach(label => {
            const input = label.querySelector('input');
            const span  = label.querySelector('span');
            span.style.boxShadow = input.checked
                ? `0 0 0 2px white, 0 0 0 4px ${input.value}`
                : 'none';
        });
    }

    document.querySelectorAll('.color-swatch input').forEach(i => i.addEventListener('change', updateSwatches));

    document.getElementById('createEventModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
</script>
@endpush
