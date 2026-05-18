<div class="group-card cursor-pointer" onclick="window.location='{{ route('groups.show', $group->id) }}'" style="position:relative;">
    {{-- Cover --}}
    <div class="h-24 relative overflow-hidden"
         @unless($group->cover_photo) style="background:linear-gradient(135deg,{{ $group->cover_color }},{{ $group->cover_color }}cc);" @endunless>

        @if($group->cover_photo)
            <img src="{{ Storage::url($group->cover_photo) }}"
                 alt="" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-black/30"></div>
        @else
            <div class="absolute inset-0 flex items-center px-5">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>
        @endif

        @if($group->sector)
        <span class="absolute top-3 right-3 px-2 py-0.5 rounded-full text-xs font-medium text-white bg-black/30 backdrop-blur-sm">
            {{ $group->sector->name }}
        </span>
        @endif
    </div>

    {{-- Body --}}
    <div class="p-4">
        <h3 class="font-semibold text-gray-900 text-sm leading-snug mb-1 truncate">{{ $group->name }}</h3>
        @if($group->description)
        <p class="text-xs text-gray-500 line-clamp-2 mb-3">{{ $group->description }}</p>
        @endif

        <div class="flex items-center justify-between mt-3">
            <div class="flex items-center gap-1 text-xs text-gray-400">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                {{ number_format($group->members_count) }} members
            </div>

            @if(in_array($group->id, $memberGroupIds))
            <form method="POST" action="{{ route('groups.leave', $group->id) }}">
                @csrf @method('DELETE')
                <button type="submit"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition"
                    style="border-color:#1E8F88;color:#1E8F88;"
                    onmouseover="this.style.background='#E6F7F4'" onmouseout="this.style.background='transparent'">
                    Leave
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('groups.join', $group->id) }}">
                @csrf
                <button type="submit"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition"
                    style="background:#1E8F88;"
                    onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                    Join
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
