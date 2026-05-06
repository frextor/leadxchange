{{-- resources/views/components/profile-block.blade.php --}}
@props(['label'])

<div>
    <div class="text-xs text-gray-400 uppercase tracking-wider font-medium mb-1.5">{{ $label }}</div>
    <div class="text-[14.5px] text-gray-700 leading-relaxed">{{ $slot }}</div>
</div>
