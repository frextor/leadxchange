@extends('layouts.dashboard')

@section('title', 'Groups — LeadXchange')

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
            <h1 class="text-2xl font-semibold text-gray-900 tracking-tight">Groups</h1>
            <p class="text-sm text-gray-500 mt-1">Discover groups recommended based on your professional interests</p>
        </div>
        <button onclick="document.getElementById('createGroupModal').classList.remove('hidden')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition"
            style="background:#1E8F88;" onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            Create a group
        </button>
    </div>

    {{-- ── CREATE GROUP MODAL ── --}}
    <div id="createGroupModal" class="{{ $errors->any() ? '' : 'hidden' }} fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Create a group</h2>
                <button type="button" onclick="document.getElementById('createGroupModal').classList.add('hidden')"
                    class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 transition">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('groups.store') }}" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Group name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Ex: Sales & Business Dev Morocco"
                        required maxlength="100"
                        class="gr-input @error('name') border-red-400 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="3" placeholder="What is this group about?" maxlength="500"
                        class="gr-input" style="height:auto;padding-top:10px;padding-bottom:10px;resize:none;">{{ old('description') }}</textarea>
                    @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Category (interest)</label>
                    <select name="sector_id" class="gr-input" style="appearance:none;">
                        <option value="">— No category —</option>
                        @foreach($sectors as $sector)
                            <option value="{{ $sector->id }}" {{ old('sector_id') == $sector->id ? 'selected' : '' }}>
                                {{ $sector->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('sector_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                    <select name="city_id" class="gr-input" style="appearance:none;">
                        <option value="">— No city —</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}"
                                {{ old('city_id', auth()->user()->city_id) == $city->id ? 'selected' : '' }}>
                                {{ $city->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('city_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Cover photo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Photo de couverture
                        <span class="text-gray-400 font-normal text-xs ml-1">(optionnel — prioritaire sur la couleur)</span>
                    </label>
                    <label id="photoDropzone"
                        class="flex flex-col items-center justify-center gap-2 w-full h-28 rounded-xl border-2 border-dashed border-gray-200 cursor-pointer transition hover:border-teal-400 hover:bg-teal-50 relative overflow-hidden">
                        <input type="file" name="cover_photo" id="coverPhotoInput" accept="image/*" class="sr-only">
                        {{-- Preview overlay --}}
                        <img id="coverPhotoPreview" src="" alt="" class="absolute inset-0 w-full h-full object-cover hidden">
                        <div id="photoPlaceholder" class="flex flex-col items-center gap-1 text-gray-400 pointer-events-none">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                            <span class="text-xs font-medium">Glissez une image ou cliquez</span>
                            <span class="text-xs">JPG, PNG, WebP — max 2 Mo</span>
                        </div>
                        <button type="button" id="removePhotoBtn"
                            class="hidden absolute top-2 right-2 w-6 h-6 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/70"
                            onclick="event.preventDefault();removePhoto()">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </label>
                    @error('cover_photo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Cover color swatches --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Couleur de couverture</label>
                    <div class="flex gap-2 flex-wrap" id="swatchContainer">
                        @php $colors = ['#1E8F88','#6366F1','#F59E0B','#EF4444','#8B5CF6','#EC4899','#10B981','#3B82F6']; @endphp
                        @foreach($colors as $color)
                        <label class="cursor-pointer color-swatch relative">
                            <input type="radio" name="cover_color" value="{{ $color }}" class="sr-only"
                                {{ (old('cover_color', '#1E8F88') === $color) ? 'checked' : '' }}>
                            <span class="swatch-dot block w-7 h-7 rounded-full transition-all" data-color="{{ $color }}"></span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('createGroupModal').classList.add('hidden')"
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

                <form method="GET" action="{{ route('groups.index') }}" id="filterForm">
                    <div class="relative mb-4">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search groups…" class="gr-input pl-9"
                            oninput="document.getElementById('filterForm').submit()">
                    </div>

                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">Categories</p>
                    <div class="space-y-0.5">
                        <button type="button" data-cat="" class="cat-btn {{ !request('category') ? 'active' : '' }}">
                            <span>All groups</span>
                            <span class="text-xs font-normal">{{ $groups->count() }}</span>
                        </button>
                        @foreach($sectors as $sector)
                            @php $count = $groups->where('sector_id', $sector->id)->count(); @endphp
                            @if($count > 0)
                            <button type="button" data-cat="{{ $sector->id }}"
                                class="cat-btn {{ request('category') == $sector->id ? 'active' : '' }}">
                                <span>{{ $sector->name }}</span>
                                <span class="text-xs font-normal">{{ $count }}</span>
                            </button>
                            @endif
                        @endforeach
                    </div>
                    <input type="hidden" name="category" id="categoryInput" value="{{ request('category') }}">
                </form>

                @if($userSectorIds)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="flex items-center gap-2 px-1">
                        <div class="w-2 h-2 rounded-full" style="background:#1E8F88;"></div>
                        <span class="text-xs text-gray-500">{{ $recommended->count() }} recommended for you</span>
                    </div>
                </div>
                @endif
            </div>
        </aside>

        {{-- ── MAIN CONTENT ── --}}
        <div class="flex-1 min-w-0">

            @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium" style="background:#E6F7F4;color:#1E8F88;">
                {{ session('success') }}
            </div>
            @endif

            @if($groups->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 p-16 text-center">
                <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#E6F7F4;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <p class="font-semibold text-gray-700">No groups found</p>
                <p class="text-sm text-gray-400 mt-1">Try a different search or be the first to create one!</p>
            </div>
            @else

            {{-- Recommended --}}
            @if($recommended->isNotEmpty() && !request('category'))
            <div class="mb-8">
                <div class="flex items-center gap-2 mb-4">
                    <h2 class="text-base font-semibold text-gray-900">Recommended for you</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold" style="background:#E6F7F4;color:#1E8F88;">{{ $recommended->count() }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($recommended as $group)
                        @include('groups._card', ['group' => $group, 'memberGroupIds' => $memberGroupIds])
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Others / All --}}
            @php $list = (request('category') || $recommended->isEmpty()) ? $groups : $others; @endphp
            @if($list->isNotEmpty())
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <h2 class="text-base font-semibold text-gray-900">
                        {{ request('category') ? $sectors->find(request('category'))?->name : ($recommended->isEmpty() ? 'All groups' : 'Other groups') }}
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold" style="background:#F3F4F6;color:#6B7280;">{{ $list->count() }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($list as $group)
                        @include('groups._card', ['group' => $group, 'memberGroupIds' => $memberGroupIds])
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
    // ── Category filter buttons ──
    document.querySelectorAll('[data-cat]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('categoryInput').value = btn.dataset.cat;
            document.getElementById('filterForm').submit();
        });
    });

    // ── Color swatches ──
    function updateSwatches() {
        document.querySelectorAll('.color-swatch').forEach(label => {
            const input = label.querySelector('input');
            const dot   = label.querySelector('.swatch-dot');
            dot.style.background  = input.value;
            dot.style.boxShadow   = input.checked
                ? `0 0 0 2px white, 0 0 0 4px ${input.value}`
                : 'none';
        });
    }

    document.querySelectorAll('.color-swatch input').forEach(i => i.addEventListener('change', updateSwatches));
    updateSwatches(); // init on load

    // ── Modal close on backdrop ──
    document.getElementById('createGroupModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });

    // ── Cover photo preview ──
    const photoInput   = document.getElementById('coverPhotoInput');
    const preview      = document.getElementById('coverPhotoPreview');
    const placeholder  = document.getElementById('photoPlaceholder');
    const removeBtn    = document.getElementById('removePhotoBtn');

    photoInput.addEventListener('change', () => {
        const file = photoInput.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        preview.src = url;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
        removeBtn.classList.remove('hidden');
    });

    function removePhoto() {
        photoInput.value = '';
        preview.src = '';
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
        removeBtn.classList.add('hidden');
    }
</script>
@endpush
