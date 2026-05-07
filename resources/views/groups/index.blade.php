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
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 tracking-tight">Groups</h1>
        <p class="text-sm text-gray-500 mt-1">Discover groups recommended based on your professional interests</p>
    </div>

    <div class="flex gap-6 items-start">

        {{-- ── LEFT SIDEBAR ── --}}
        <aside class="w-64 flex-shrink-0 sticky top-20">
            <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">

                {{-- Search --}}
                <form method="GET" action="{{ route('groups.index') }}" id="filterForm">
                    <div class="relative mb-4">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search groups…"
                            class="gr-input pl-9"
                            oninput="document.getElementById('filterForm').submit()">
                    </div>

                    {{-- Categories --}}
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">Categories</p>
                    <div class="space-y-0.5">
                        <button type="button" onclick="setCategory('')"
                            class="cat-btn {{ !request('category') ? 'active' : '' }}">
                            <span>All groups</span>
                            <span class="text-xs font-normal">{{ $groups->count() }}</span>
                        </button>
                        @foreach($sectors as $sector)
                            @php $count = $groups->where('sector_id', $sector->id)->count(); @endphp
                            @if($count > 0)
                            <button type="button" onclick="setCategory({{ $sector->id }})"
                                class="cat-btn {{ request('category') == $sector->id ? 'active' : '' }}">
                                <span>{{ $sector->name }}</span>
                                <span class="text-xs font-normal">{{ $count }}</span>
                            </button>
                            @endif
                        @endforeach
                    </div>
                    <input type="hidden" name="category" id="categoryInput" value="{{ request('category') }}">
                </form>

                {{-- Recommended badge --}}
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
                <p class="text-sm text-gray-400 mt-1">Try a different search or category</p>
            </div>
            @else

            {{-- ── RECOMMENDED ── --}}
            @if($recommended->isNotEmpty() && !request('category'))
            <div class="mb-8">
                <div class="flex items-center gap-2 mb-4">
                    <h2 class="text-base font-semibold text-gray-900">Recommended for you</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold" style="background:#E6F7F4;color:#1E8F88;">
                        {{ $recommended->count() }}
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($recommended as $group)
                        @include('groups._card', ['group' => $group, 'memberGroupIds' => $memberGroupIds])
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ── ALL / OTHERS ── --}}
            @php $list = (request('category') || $recommended->isEmpty()) ? $groups : $others; @endphp
            @if($list->isNotEmpty())
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <h2 class="text-base font-semibold text-gray-900">
                        {{ request('category') ? $sectors->find(request('category'))?->name : ($recommended->isEmpty() ? 'All groups' : 'Other groups') }}
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold" style="background:#F3F4F6;color:#6B7280;">
                        {{ $list->count() }}
                    </span>
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
    function setCategory(id) {
        document.getElementById('categoryInput').value = id;
        document.getElementById('filterForm').submit();
    }
</script>
@endpush
