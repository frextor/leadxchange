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
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="ex: premium_gold"
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
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Prix mensuel (MAD) <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input type="number" name="price" value="{{ old('price', $plan->price) }}"
                                   required min="0" step="0.01" placeholder="0"
                                   class="w-full h-10 pl-3 pr-12 rounded-xl border border-gray-200 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium">MAD</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Prix annuel (MAD)</label>
                        <div class="relative">
                            <input type="number" name="annual_price" value="{{ old('annual_price', $plan->annual_price) }}"
                                   min="0" step="0.01" placeholder="Optionnel"
                                   class="w-full h-10 pl-3 pr-12 rounded-xl border border-gray-200 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition placeholder-gray-300">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium">MAD</span>
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
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Fonctionnalités (JSON)</p>
                    <button type="button" onclick="insertFeaturesTemplate()"
                            class="text-[10px] font-semibold text-indigo-500 hover:text-indigo-700 transition">
                        Insérer template
                    </button>
                </div>
                <textarea id="featuresJson" name="features" rows="7"
                          class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition resize-none font-mono text-xs placeholder-gray-300"
                          placeholder='{"send_leads": true, "receive_leads": true, "chat": false}'>{{ old('features', $plan->exists ? json_encode($plan->features, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                <div class="flex items-start gap-2 px-3 py-2.5 rounded-xl bg-gray-50 border border-gray-100">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2" class="flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    <p class="text-[10px] text-gray-400 leading-relaxed">
                        Clés supportées : <code class="font-mono bg-gray-100 px-1 rounded">send_leads</code>,
                        <code class="font-mono bg-gray-100 px-1 rounded">receive_leads</code>,
                        <code class="font-mono bg-gray-100 px-1 rounded">chat</code>,
                        <code class="font-mono bg-gray-100 px-1 rounded">groups</code>,
                        <code class="font-mono bg-gray-100 px-1 rounded">events</code>,
                        <code class="font-mono bg-gray-100 px-1 rounded">profile_video</code>,
                        <code class="font-mono bg-gray-100 px-1 rounded">priority_support</code>…
                        Valeur <code class="font-mono bg-gray-100 px-1 rounded">true</code> / <code class="font-mono bg-gray-100 px-1 rounded">false</code>.
                    </p>
                </div>
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
function insertFeaturesTemplate() {
    const template = {
        "send_leads":       true,
        "receive_leads":    true,
        "chat":             true,
        "groups":           true,
        "events":           true,
        "profile_video":    false,
        "priority_support": false,
        "ambassador_badge": false
    };
    const textarea = document.getElementById('featuresJson');
    if (!textarea.value.trim()) {
        textarea.value = JSON.stringify(template, null, 2);
    }
}
</script>
@endpush
@endsection
