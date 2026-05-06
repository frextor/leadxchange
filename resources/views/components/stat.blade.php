{{-- resources/views/components/stat.blade.php --}}
@props(['label', 'value', 'star' => false])

<div class="p-3.5 rounded-xl border border-gray-100" style="background:#F9FAFB;">
    <div class="text-[22px] font-semibold tracking-tight inline-flex items-center gap-1 text-gray-900">
        {{ $value }}
        @if($star)
            <svg width="14" height="14" viewBox="0 0 24 24" fill="#FFC65C">
                <path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z"/>
            </svg>
        @endif
    </div>
    <div class="text-xs text-gray-400 mt-0.5">{{ $label }}</div>
</div>
