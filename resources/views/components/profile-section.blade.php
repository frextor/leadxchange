{{-- resources/views/components/profile-section.blade.php --}}
@props(['title', 'editModal' => null])

<section class="bg-white rounded-2xl border border-gray-200 shadow-sm">
    <header class="flex justify-between items-center px-5 py-4 border-b border-gray-100">
        <div class="text-[15px] font-semibold text-gray-900">{{ $title }}</div>
        @if($editModal)
            <button onclick="openModal('{{ $editModal }}')"
                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[12.5px] font-medium border border-gray-200 hover:bg-gray-50 transition-colors"
                style="color:#1E8F88;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/>
                </svg>
                Edit
            </button>
        @endif
    </header>
    <div class="p-5 flex flex-col gap-4">
        {{ $slot }}
    </div>
</section>
