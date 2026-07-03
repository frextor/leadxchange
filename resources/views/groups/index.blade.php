@extends('layouts.app')

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
    .section-title {
        font-size: 15px; font-weight: 600; color: #111827;
    }
    .section-badge {
        font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px;
    }
    .swatch-label { cursor: pointer; position: relative; }
    .swatch-label input { position: absolute; opacity: 0; pointer-events: none; }
    .swatch-circle { width: 28px; height: 28px; border-radius: 50%; display: block; transition: all .15s; }
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
        @if(auth()->user()->canFeature('can_create_pole'))
        <button onclick="document.getElementById('createGroupModal').classList.remove('hidden')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition"
            style="background:#1E8F88;" onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            Create a group
        </button>
        @else
        <button type="button" onclick="openUpgradeModal('can_create_pole')"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-dashed transition cursor-pointer"
                style="border-color:#6366F1;color:#6366F1;background:transparent;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Upgrade pour créer
        </button>
        @endif
    </div>

    {{-- ── CREATE GROUP MODAL (only rendered when user can create groups) ── --}}
    @if(auth()->user()->canFeature('can_create_pole'))
    <div id="createGroupModal" class="{{ $errors->any() ? '' : 'hidden' }} fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[92vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
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
                        required maxlength="100" class="gr-input @error('name') border-red-400 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="3" placeholder="What is this group about?" maxlength="500"
                        class="gr-input" style="height:auto;padding-top:10px;padding-bottom:10px;resize:none;">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                    <select name="sector_id" class="gr-input" style="appearance:none;">
                        <option value="">— No category —</option>
                        @foreach($sectors as $sector)
                            <option value="{{ $sector->id }}" {{ old('sector_id') == $sector->id ? 'selected' : '' }}>{{ $sector->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                    <select name="city_id" class="gr-input" style="appearance:none;">
                        <option value="">— No city —</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ old('city_id', auth()->user()->city_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Cover photo
                        <span class="text-gray-400 font-normal text-xs ml-1">(optional)</span>
                    </label>
                    <label id="photoDropzone"
                        class="flex flex-col items-center justify-center gap-2 w-full h-28 rounded-xl border-2 border-dashed border-gray-200 cursor-pointer transition hover:border-teal-400 hover:bg-teal-50 relative overflow-hidden">
                        <input type="file" name="cover_photo" id="coverPhotoInput" accept="image/*" class="sr-only">
                        <img id="coverPhotoPreview" src="" alt="" class="absolute inset-0 w-full h-full object-cover hidden">
                        <div id="photoPlaceholder" class="flex flex-col items-center gap-1 text-gray-400 pointer-events-none">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            <span class="text-xs font-medium">Click or drag image</span>
                            <span class="text-xs">JPG, PNG, WebP — max 2MB</span>
                        </div>
                        <button type="button" id="removePhotoBtn"
                            class="hidden absolute top-2 right-2 w-6 h-6 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/70"
                            onclick="event.preventDefault();removePhoto()">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cover color</label>
                    <div class="flex gap-2 flex-wrap">
                        @foreach(['#1E8F88','#6366F1','#F59E0B','#EF4444','#8B5CF6','#EC4899','#10B981','#3B82F6'] as $color)
                        <label class="swatch-label">
                            <input type="radio" name="cover_color" value="{{ $color }}" {{ (old('cover_color','#1E8F88') === $color) ? 'checked' : '' }} onchange="updateSwatches()">
                            <span class="swatch-circle" style="background:{{ $color }};box-shadow:{{ (old('cover_color','#1E8F88') === $color) ? '0 0 0 2px white,0 0 0 4px '.$color : 'none' }};"></span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('createGroupModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                        style="background:#1E8F88;" onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">Create</button>
                </div>
            </form>
        </div>
    </div>
    @endif {{-- create_groups --}}

    {{-- ── INVITE MODAL (for my groups where user is admin/owner) ── --}}
    <div id="inviteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900" id="inviteModalTitle">Invite a member</h2>
                <button type="button" onclick="closeInviteModal()"
                    class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 transition">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="inviteForm" method="POST" class="px-6 py-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Search a connection</label>
                    <input type="text" id="inviteSearch" placeholder="Name…"
                        class="gr-input" oninput="filterInviteUsers(this.value)">
                </div>
                <div id="inviteUserList" class="space-y-1 max-h-52 overflow-y-auto"></div>
                <input type="hidden" name="user_id" id="inviteUserId">
                <div id="inviteSelectedUser" class="hidden px-3 py-2 rounded-xl text-sm font-medium" style="background:#E6F7F4;color:#1E8F88;"></div>
                <div class="flex gap-3 pt-1">
                    <button type="button" onclick="closeInviteModal()"
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

                @if($myGroups->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-gray-100 space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-1 mb-2">My groups</p>
                    @foreach($myGroups->take(5) as $g)
                    <a href="{{ route('groups.show', $g->id) }}"
                       class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 transition text-sm text-gray-700 truncate">
                        <span class="w-6 h-6 rounded-md flex-shrink-0 flex items-center justify-center text-white text-[10px] font-bold"
                              style="background:{{ $g->cover_color }};">{{ strtoupper(substr($g->name,0,1)) }}</span>
                        <span class="truncate">{{ $g->name }}</span>
                        @php $r = $userRoles[$g->id] ?? null; @endphp
                        @if($r === 'owner')
                            <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded-full flex-shrink-0" style="background:#FEF3C7;color:#92400E;">Owner</span>
                        @elseif($r === 'admin')
                            <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded-full flex-shrink-0" style="background:#E6F7F4;color:#1E8F88;">Admin</span>
                        @endif
                    </a>
                    @endforeach
                    @if($myGroups->count() > 5)
                    <p class="text-xs text-gray-400 px-2 pt-1">+ {{ $myGroups->count() - 5 }} more</p>
                    @endif
                </div>
                @endif
            </div>
        </aside>

        {{-- ── MAIN CONTENT ── --}}
        <div class="flex-1 min-w-0 space-y-8">

            {{-- Flash messages --}}
            @foreach(['success' => 'E6F7F4|1E8F88', 'error' => 'FEF2F2|EF4444', 'info' => 'EFF6FF|3B82F6'] as $type => $colors)
            @if(session($type))
            @php [$bg, $fg] = explode('|', $colors); @endphp
            <div class="px-4 py-3 rounded-xl text-sm font-medium" style="background:#{{ $bg }};color:#{{ $fg }};">
                {{ session($type) }}
            </div>
            @endif
            @endforeach

            {{-- ══════════════════════════════════════════
                 1. PENDING INVITATIONS
            ══════════════════════════════════════════ --}}
            @if($pendingInvitations->isNotEmpty())
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <h2 class="section-title">Invitations</h2>
                    <span class="section-badge" style="background:#FEF3C7;color:#92400E;">
                        {{ $pendingInvitations->count() }} en attente
                    </span>
                </div>
                <div class="bg-white rounded-2xl border border-amber-200 overflow-hidden shadow-sm">
                    <div class="divide-y divide-gray-100">
                        @foreach($pendingInvitations as $inv)
                        <div class="flex items-center gap-4 px-5 py-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                                 style="background:{{ $inv->group->cover_color ?? '#1E8F88' }};">
                                {{ strtoupper(substr($inv->group->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $inv->group->name }}</p>
                                <p class="text-xs text-gray-400">
                                    Invited by <strong class="text-gray-600">{{ $inv->inviter?->first_name }} {{ $inv->inviter?->last_name }}</strong>
                                    @if($inv->group->sector) · {{ $inv->group->sector->name }} @endif
                                </p>
                            </div>
                            <div class="flex gap-2 flex-shrink-0">
                                <form method="POST" action="{{ route('groups.invitations.accept', $inv->id) }}">
                                    @csrf
                                    <button type="submit"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition"
                                        style="background:#1E8F88;" onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                                        Accept
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('groups.invitations.decline', $inv->id) }}">
                                    @csrf
                                    <button type="submit"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-500 border border-gray-200 hover:bg-gray-50 transition">
                                        Decline
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- ══════════════════════════════════════════
                 2. MY GROUPS
            ══════════════════════════════════════════ --}}
            @if($myGroups->isNotEmpty())
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <h2 class="section-title">My groups</h2>
                    <span class="section-badge" style="background:#E6F7F4;color:#1E8F88;">{{ $myGroups->count() }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($myGroups as $group)
                    @php $userRole = $userRoles[$group->id] ?? null; @endphp
                    @include('groups._card', ['group' => $group, 'memberGroupIds' => $memberGroupIds, 'userRole' => $userRole])
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ══════════════════════════════════════════
                 3. NEARBY
            ══════════════════════════════════════════ --}}
            @if($nearby->isNotEmpty())
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <h2 class="section-title">Nearby</h2>
                    <span class="section-badge" style="background:#EFF6FF;color:#3B82F6;">{{ $nearby->count() }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($nearby as $group)
                        @include('groups._card', ['group' => $group, 'memberGroupIds' => $memberGroupIds, 'userRole' => null])
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ══════════════════════════════════════════
                 4. RECOMMENDED
            ══════════════════════════════════════════ --}}
            @if($recommended->isNotEmpty() && !request('category'))
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <h2 class="section-title">Recommended for you</h2>
                    <span class="section-badge" style="background:#E6F7F4;color:#1E8F88;">{{ $recommended->count() }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($recommended as $group)
                        @include('groups._card', ['group' => $group, 'memberGroupIds' => $memberGroupIds, 'userRole' => null])
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ══════════════════════════════════════════
                 5. OTHERS / ALL
            ══════════════════════════════════════════ --}}
            @if($others->isNotEmpty())
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <h2 class="section-title">
                        {{ request('category') ? ($sectors->find(request('category'))?->name ?? 'Groups') : 'Other groups' }}
                    </h2>
                    <span class="section-badge" style="background:#F3F4F6;color:#6B7280;">{{ $others->count() }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($others as $group)
                        @include('groups._card', ['group' => $group, 'memberGroupIds' => $memberGroupIds, 'userRole' => null])
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Empty state --}}
            @if($myGroups->isEmpty() && $nearby->isEmpty() && $recommended->isEmpty() && $others->isEmpty() && $pendingInvitations->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 p-16 text-center">
                <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#E6F7F4;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <p class="font-semibold text-gray-700">No groups found</p>
                <p class="text-sm text-gray-400 mt-1">Try a different search or be the first to create one!</p>
            </div>
            @endif

        </div>{{-- /main --}}
    </div>
</div>

@endsection

@push('scripts')
<script>
const groupConnections = @json($groupConnections);
    // ── Category filter buttons ──
    document.querySelectorAll('[data-cat]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('categoryInput').value = btn.dataset.cat;
            document.getElementById('filterForm').submit();
        });
    });

    // ── Color swatches ──
    function updateSwatches() {
        document.querySelectorAll('.swatch-label').forEach(label => {
            const input = label.querySelector('input');
            const span  = label.querySelector('.swatch-circle');
            span.style.background  = input.value;
            span.style.boxShadow   = input.checked
                ? `0 0 0 2px white, 0 0 0 4px ${input.value}`
                : 'none';
        });
    }
    document.querySelectorAll('.swatch-label input').forEach(i => i.addEventListener('change', updateSwatches));
    updateSwatches();

    // ── Modal close on backdrop ──
    document.getElementById('createGroupModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
    document.getElementById('inviteModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });

    // ── Cover photo preview ──
    const photoInput  = document.getElementById('coverPhotoInput');
    const preview     = document.getElementById('coverPhotoPreview');
    const placeholder = document.getElementById('photoPlaceholder');
    const removeBtn   = document.getElementById('removePhotoBtn');

    photoInput.addEventListener('change', () => {
        const file = photoInput.files[0];
        if (!file) return;
        preview.src = URL.createObjectURL(file);
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

    // ── Invite modal ──
    let inviteUsers = [];

    function openInviteModal(groupId, groupName) {
        document.getElementById('inviteModalTitle').textContent = 'Invite to ' + groupName;
        document.getElementById('inviteForm').action = `/groups/${groupId}/invite`;
        document.getElementById('inviteSearch').value = '';
        document.getElementById('inviteUserId').value = '';
        document.getElementById('inviteSubmitBtn').disabled = true;
        document.getElementById('inviteSelectedUser').classList.add('hidden');
        document.getElementById('inviteSelectedUser').textContent = '';
        renderInviteUsers(groupConnections);
        document.getElementById('inviteModal').classList.remove('hidden');
        setTimeout(() => document.getElementById('inviteSearch').focus(), 50);
    }

    function closeInviteModal() {
        document.getElementById('inviteModal').classList.add('hidden');
    }

    function renderInviteUsers(list) {
        const container = document.getElementById('inviteUserList');
        if (list.length === 0) {
            container.innerHTML = '<p class="text-xs text-gray-400 text-center py-4">No connections found</p>';
            return;
        }
        container.innerHTML = list.map(u => `
            <button type="button" onclick="selectInviteUser(${u.id}, '${u.name.replace(/'/g,"\\'")}', '${(u.job_title||'').replace(/'/g,"\\'")}')"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 transition text-left">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#34d4bf,#1E8F88);">
                    ${u.name.charAt(0).toUpperCase()}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">${u.name}</p>
                    ${u.job_title ? `<p class="text-xs text-gray-400 truncate">${u.job_title}</p>` : ''}
                </div>
            </button>
        `).join('');
    }

    function filterInviteUsers(query) {
        const q = query.toLowerCase();
        renderInviteUsers(q ? groupConnections.filter(u => u.name.toLowerCase().includes(q)) : groupConnections);
    }

    function selectInviteUser(id, name, jobTitle) {
        document.getElementById('inviteUserId').value = id;
        document.getElementById('inviteSubmitBtn').disabled = false;
        const sel = document.getElementById('inviteSelectedUser');
        sel.textContent = '✓ ' + name + (jobTitle ? ' — ' + jobTitle : '');
        sel.classList.remove('hidden');
        document.getElementById('inviteUserList').innerHTML = '';
        document.getElementById('inviteSearch').value = name;
    }
</script>
@endpush
