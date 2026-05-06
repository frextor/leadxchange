{{-- resources/views/components/profile-kv.blade.php --}}
@props(['label'])

<div>
    <div class="text-xs text-gray-400 uppercase tracking-wider font-medium mb-1">{{ $label }}</div>
    <div class="text-sm text-gray-900 font-medium">{{ $slot }}</div>
</div>
