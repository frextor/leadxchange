@extends('admin.layouts.admin')
@section('title', 'Permissions des plans')
@section('page-title', 'Plans')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin › Plans</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Permissions des plans</h1>
        <p class="text-sm text-gray-400 mt-1">Définissez ce que chaque plan autorise ou limite.</p>
    </div>
    <a href="{{ route('admin.super.plans.index') }}"
       class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
        ← Retour aux plans
    </a>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('admin.super.plans.permissions.update') }}">
@csrf

<div class="overflow-x-auto">
<table class="w-full text-sm border-separate border-spacing-0">

    {{-- En-tête plans --}}
    <thead>
        <tr>
            <th class="sticky left-0 z-10 bg-white border-b border-gray-200 px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider min-w-[240px]">
                Permission
            </th>
            @foreach($plans as $plan)
            <th class="border-b border-gray-200 px-4 py-3 text-center min-w-[130px]">
                <div class="flex flex-col items-center gap-1">
                    <span class="text-sm font-bold text-gray-900">{{ $plan->label }}</span>
                    <span class="text-[10px] text-gray-400">{{ $plan->price > 0 ? currency_format($plan->price).'/mois' : 'Gratuit' }}</span>
                </div>
            </th>
            @endforeach
        </tr>
    </thead>

    <tbody>
    @foreach($permissions as $group => $perms)

        {{-- Séparateur de groupe --}}
        <tr>
            <td colspan="{{ $plans->count() + 1 }}"
                class="sticky left-0 px-5 py-2.5 bg-gradient-to-r from-gray-50 to-white border-y border-gray-100">
                <span class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">{{ $group }}</span>
            </td>
        </tr>

        @foreach($perms as $key => $def)
        <tr class="hover:bg-gray-50/50 transition group border-b border-gray-50">

            {{-- Label --}}
            <td class="sticky left-0 bg-white group-hover:bg-gray-50/50 px-5 py-2.5 text-sm font-medium text-gray-700 border-b border-gray-50 transition">
                {{ $def['label'] }}
            </td>

            @foreach($plans as $plan)
            @php $val = ($plan->permissions ?? [])[$key] ?? null; @endphp
            <td class="px-4 py-2.5 text-center border-b border-gray-50">

                @if($def['type'] === 'bool')
                    <label class="inline-flex items-center justify-center cursor-pointer">
                        <input type="checkbox"
                               name="perm_{{ $plan->id }}_{{ $key }}"
                               value="1"
                               {{ $val ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-8 h-4.5 rounded-full transition-colors peer-checked:bg-teal-500 bg-gray-200 relative">
                            <div class="absolute top-0.5 left-0.5 w-3.5 h-3.5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-3.5"></div>
                        </div>
                    </label>

                @elseif($def['type'] === 'number')
                    <div class="flex flex-col items-center gap-1">
                        <input type="number"
                               name="perm_{{ $plan->id }}_{{ $key }}"
                               value="{{ $val }}"
                               min="0"
                               placeholder="—"
                               class="w-20 border border-gray-200 rounded-lg px-2 py-1 text-xs text-center focus:outline-none focus:border-indigo-400 transition {{ $val === null ? 'bg-gray-50 text-gray-400' : '' }}"
                               {{ $val === null ? 'disabled' : '' }}>
                        <label class="flex items-center gap-1 text-[10px] text-gray-400 cursor-pointer">
                            <input type="checkbox"
                                   name="unlimited_{{ $plan->id }}_{{ $key }}"
                                   class="w-3 h-3 rounded"
                                   {{ $val === null ? 'checked' : '' }}
                                   onchange="toggleUnlimited(this, 'perm_{{ $plan->id }}_{{ $key }}')">
                            {{ $def['null_label'] ?? 'Illimité' }}
                        </label>
                    </div>
                @endif

            </td>
            @endforeach
        </tr>
        @endforeach

    @endforeach
    </tbody>

</table>
</div>

{{-- Submit --}}
<div class="sticky bottom-0 bg-white border-t border-gray-100 px-6 py-4 mt-0 flex items-center gap-3 shadow-lg">
    <button type="submit"
            class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
            style="background:#4338CA;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 11 3 3L22 4"/></svg>
        Enregistrer toutes les permissions
    </button>
    <p class="text-xs text-gray-400">Les modifications s'appliquent immédiatement à tous les utilisateurs.</p>
</div>

</form>

@endsection

@push('scripts')
<script>
// Toggle number input when "Illimité" is checked
function toggleUnlimited(checkbox, inputName) {
    const input = document.querySelector(`[name="${inputName}"]`);
    if (!input) return;
    input.disabled = checkbox.checked;
    input.classList.toggle('bg-gray-50', checkbox.checked);
    input.classList.toggle('text-gray-400', checkbox.checked);
    if (checkbox.checked) input.value = '';
}

// Fix toggle visual — CSS peer doesn't work through JS change
document.querySelectorAll('input[type="checkbox"].sr-only').forEach(cb => {
    cb.addEventListener('change', function() {
        const dot = this.nextElementSibling?.querySelector('div');
        if (!dot) return;
        const track = this.nextElementSibling;
        if (this.checked) {
            track.classList.replace('bg-gray-200', 'bg-teal-500');
            dot.classList.add('translate-x-3.5');
        } else {
            track.classList.replace('bg-teal-500', 'bg-gray-200');
            dot.classList.remove('translate-x-3.5');
        }
    });
});
</script>
@endpush
