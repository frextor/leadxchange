{{-- resources/views/components/form-field.blade.php --}}
@props(['label'])

<label class="flex flex-col gap-1.5">
    <span class="text-[12.5px] font-semibold text-gray-700">{{ $label }}</span>
    {{ $slot }}
</label>
