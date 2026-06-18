@extends('admin.layouts.admin')
@section('title', $plan->exists ? 'Modifier ' . $plan->label : 'Nouveau plan')
@section('page-title', $plan->exists ? 'Modifier ' . $plan->label : 'Nouveau plan')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm">
    <a href="{{ route('admin.super.plans.index') }}" class="text-gray-400 hover:text-gray-600 transition">Plans</a>
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-300"><path d="m9 18 6-6-6-6"/></svg>
    <span class="text-gray-700 font-semibold">{{ $plan->exists ? $plan->label : 'Nouveau plan' }}</span>
</div>

<div class="max-w-2xl">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    {{-- Form header --}}
    <div class="px-6 py-4 border-b border-gray-100" style="background:linear-gradient(135deg,#EEF2FF,#E0E7FF);">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-white shadow-sm flex items-center justify-center">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2">
                    <rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-indigo-900">{{ $plan->exists ? 'Modifier le plan' : 'Créer un nouveau plan' }}</p>
                <p class="text-[10px] text-indigo-400 font-medium">Tarification et fonctionnalités</p>
            </div>
        </div>
    </div>

    <form method="POST"
          action="{{ $plan->exists ? route('admin.super.plans.update', $plan) : route('admin.super.plans.store') }}">
        @csrf
        @if($plan->exists) @method('PUT') @endif

        <div class="px-6 py-5 space-y-5">

            @if($errors->any())
            <div class="flex items-start gap-2.5 px-4 py-3 rounded-xl text-sm bg-red-50 text-red-600 border border-red-100">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                {{ $errors->first() }}
            </div>
            @endif

            {{-- ── Identité ─────────────────────────────────────────── --}}
            <div class="space-y-4">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Identité</p>

                @unless($plan->exists)
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Identifiant technique <span class="text-red-400">*</span>
                        <span class="font-normal text-gray-400 ml-1">— non modifiable après création</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="ex: enterprise"
                           class="w-full h-10 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition font-mono placeholder-gray-300">
                </div>
                @endunless

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom affiché <span class="text-red-400">*</span></label>
                    <input type="text" name="label" value="{{ old('label', $plan->label) }}" required
                           placeholder="ex: Premium Gold"
                           class="w-full h-10 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition placeholder-gray-300">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Description</label>
                    <textarea name="description" rows="2" placeholder="Courte description affichée aux utilisateurs..."
                              class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition resize-none placeholder-gray-300">{{ old('description', $plan->description) }}</textarea>
                </div>
            </div>

            <hr class="border-gray-100">

            {{-- ── Tarification ─────────────────────────────────────── --}}
            <div class="space-y-4">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tarification</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Prix mensuel ({{ currency_symbol() }}) <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input type="number" name="price" value="{{ old('price', $plan->price) }}"
                                   required min="0" step="0.01" placeholder="0"
                                   class="w-full h-10 pl-3 pr-12 rounded-xl border border-gray-200 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium">{{ currency_symbol() }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Prix annuel ({{ currency_symbol() }})</label>
                        <div class="relative">
                            <input type="number" name="annual_price" value="{{ old('annual_price', $plan->annual_price) }}"
                                   min="0" step="0.01" placeholder="Optionnel"
                                   class="w-full h-10 pl-3 pr-12 rounded-xl border border-gray-200 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition placeholder-gray-300">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium">{{ currency_symbol() }}</span>
                        </div>
                    </div>
                </div>
                <p class="text-[10px] text-gray-400 -mt-2">Mettez 0 pour un plan gratuit. Le prix annuel permet d'afficher une réduction.</p>
            </div>

            <hr class="border-gray-100">

            {{-- ── Limites ──────────────────────────────────────────── --}}
            <div class="space-y-4">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Limites & quotas</p>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Leads / mois</label>
                        <input type="number" name="max_leads" value="{{ old('max_leads', $plan->max_leads) }}"
                               min="0" placeholder="Illimité"
                               class="w-full h-10 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition placeholder-gray-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Groupes max</label>
                        <input type="number" name="max_groups" value="{{ old('max_groups', $plan->max_groups) }}"
                               min="0" placeholder="Illimité"
                               class="w-full h-10 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition placeholder-gray-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Points initiaux</label>
                        <input type="number" name="initial_points" value="{{ old('initial_points', $plan->initial_points) }}"
                               min="0" placeholder="0"
                               class="w-full h-10 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition placeholder-gray-300">
                    </div>
                </div>
                <p class="text-[10px] text-gray-400 -mt-2">Laissez vide pour illimité. Leads = 0 signifie aucun lead autorisé.</p>
            </div>

            <hr class="border-gray-100">

            {{-- ── Fonctionnalités ──────────────────────────────────── --}}
            <div class="space-y-3">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Fonctionnalités</p>
                @php
                    $featureDefs = \App\Http\Controllers\Admin\SuperAdmin\PlanController::FEATURES;
                    $currentFeatures = old('features')
                        ? (json_decode(old('features'), true) ?? [])
                        : ($plan->features ?? []);
                @endphp

                <div class="grid grid-cols-2 gap-2">
                    @foreach($featureDefs as $fKey => $fDef)
                    @php
                        $fVal = $currentFeatures[$fKey] ?? ($fDef['type'] === 'number' ? null : false);
                    @endphp

                    @if($fDef['type'] === 'number')
                    <div class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl border border-gray-100 bg-gray-50/60">
                        <span class="text-xs font-semibold text-gray-700 leading-tight">{{ $fDef['label'] }}</span>
                        <input type="number" min="0"
                               data-feature-key="{{ $fKey }}" data-feature-type="number"
                               value="{{ $fVal !== null ? $fVal : '' }}"
                               placeholder="∞"
                               class="feature-field w-16 text-center border border-gray-200 rounded-lg px-2 py-1 text-xs outline-none focus:border-indigo-400 transition placeholder-indigo-300">
                    </div>
                    @else
                    <label class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl border border-gray-100 bg-gray-50/60 cursor-pointer hover:bg-indigo-50/40 transition group">
                        <span class="text-xs font-semibold text-gray-700 leading-tight group-hover:text-indigo-700 transition">{{ $fDef['label'] }}</span>
                        <div class="relative flex-shrink-0">
                            <input type="checkbox"
                                   data-feature-key="{{ $fKey }}" data-feature-type="bool"
                                   class="peer sr-only feature-field"
                                   {{ $fVal ? 'checked' : '' }}>
                            <div class="w-9 h-5 rounded-full transition-colors duration-200 peer-checked:bg-indigo-500 bg-gray-200 relative">
                                <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow-sm transition-transform duration-200"
                                     style="transform:{{ $fVal ? 'translateX(16px)' : 'translateX(0)' }}"></div>
                            </div>
                        </div>
                    </label>
                    @endif
                    @endforeach
                </div>

                {{-- Hidden field with JSON sent to controller --}}
                <input type="hidden" name="features" id="featuresJsonHidden">

                {{-- Live JSON preview --}}
                <details class="mt-1">
                    <summary class="text-[10px] text-gray-400 cursor-pointer select-none hover:text-gray-600 transition">Aperçu JSON</summary>
                    <pre id="featuresJsonPreview" class="mt-2 text-[10px] font-mono bg-gray-50 border border-gray-100 rounded-xl p-3 overflow-x-auto text-gray-500"></pre>
                </details>
            </div>

            <hr class="border-gray-100">

            {{-- ── Paramètres ───────────────────────────────────────── --}}
            <div class="space-y-3">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Paramètres</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Ordre d'affichage <span class="text-red-400">*</span></label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 1) }}"
                               required min="0"
                               class="w-full h-10 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition">
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                                       {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
                                <div class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:bg-indigo-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-700">Plan actif</span>
                        </label>
                    </div>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <a href="{{ route('admin.super.plans.index') }}"
               class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-white transition">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                Annuler
            </a>
            <button type="submit"
                    class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 active:scale-[.98]"
                    style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    @if($plan->exists)
                    <path d="m5 12 5 5L20 7"/>
                    @else
                    <path d="M12 5v14M5 12h14"/>
                    @endif
                </svg>
                {{ $plan->exists ? 'Mettre à jour' : 'Créer le plan' }}
            </button>
        </div>
    </form>
</div>
</div>

@push('scripts')
<script>
function buildFeaturesJson() {
    const obj = {};
    document.querySelectorAll('.feature-field').forEach(function(el) {
        const key  = el.dataset.featureKey;
        const type = el.dataset.featureType;
        if (type === 'bool') {
            obj[key] = el.checked;
        } else {
            const v = el.value.trim();
            obj[key] = (v === '') ? null : parseInt(v, 10);
        }
    });
    const json = JSON.stringify(obj);
    document.getElementById('featuresJsonHidden').value = json;
    const preview = document.getElementById('featuresJsonPreview');
    if (preview) preview.textContent = JSON.stringify(obj, null, 2);
}
}

// Animate toggles on change + rebuild JSON
document.querySelectorAll('.feature-field').forEach(function(el) {
    el.addEventListener('change', function() {
        if (el.dataset.featureType === 'bool') {
            const thumb = el.closest('label').querySelector('div > div');
            if (thumb) thumb.style.transform = el.checked ? 'translateX(16px)' : 'translateX(0)';
        }
        buildFeaturesJson();
    });
    el.addEventListener('input', buildFeaturesJson);
});

// Build on submit + on load (prepopulate hidden field)
document.querySelector('form').addEventListener('submit', buildFeaturesJson);
buildFeaturesJson();
</script>
@endpush
@endsection
