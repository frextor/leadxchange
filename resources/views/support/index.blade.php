@extends('layouts.app')
@section('title', 'Support — LeadXchange')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">

    {{-- Header --}}
    <div class="mb-7">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Mon compte</p>
        <h1 class="text-2xl font-bold text-gray-900">Support</h1>
        <p class="text-sm text-gray-400 mt-1">Exercez vos droits RGPD ou signalez un comportement abusif.</p>
    </div>

    @if(session('support_success'))
    <div class="mb-6 flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5"><path d="m9 11 3 3L22 4"/></svg>
        <div>
            <p class="font-semibold">Demande envoyée</p>
            <p class="mt-0.5">{{ session('support_success') }}</p>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-2xl px-5 py-4 text-sm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <ul class="space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    {{-- Tabs --}}
    <div class="flex items-center gap-1 bg-gray-100 rounded-2xl p-1 mb-6">
        @foreach([
            ['rgpd',       'RGPD',          'M3 11 L21 11 M3 11 L3 21 L21 21 L21 11 M7 11 L7 7 A5 5 0 0 1 17 7 L17 11'],
            ['signalement','Signalement',    'M10.29 3.86 L1.82 18 A2 2 0 0 0 3.54 21 L20.46 21 A2 2 0 0 0 22.18 18 L13.71 3.86 A2 2 0 0 0 10.29 3.86Z M12 9 L12 13 M12 17 L12.01 17'],
            ['history',    'Mes demandes',   'M12 22 C17.5228 22 22 17.5228 22 12 22 6.4772 17.5228 2 12 2 6.4772 2 6.4772 2 12 2 12 6.4772 12 22 17.5228 22 M12 6 L12 12 L16 14'],
        ] as [$key, $label, $icon])
        <a href="{{ route('support.index', ['tab' => $key]) }}"
           class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold transition
                  {{ $tab === $key ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0">
                @foreach(explode(' M', $icon) as $i => $segment)
                    @if($i === 0)<path d="{{ $segment }}"/>@else<path d="M{{ $segment }}"/>@endif
                @endforeach
            </svg>
            {{ $label }}
            @if($key === 'history' && $historyCount > 0)
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $tab === 'history' ? 'bg-gray-100 text-gray-600' : 'bg-gray-200 text-gray-600' }}">
                {{ $historyCount }}
            </span>
            @endif
        </a>
        @endforeach
    </div>

    {{-- ── TAB RGPD ��────────────────────────────────────────────────────────── --}}
    @if($tab === 'rgpd')
    <div class="space-y-5">
        <div class="bg-teal-50 border border-teal-100 rounded-2xl px-5 py-4 text-sm text-teal-800">
            Conformément au RGPD (Art. 15 à 21), vous pouvez exercer vos droits à tout moment.
            Nous vous répondrons <strong>sous 1 mois</strong> à l'adresse email de votre compte.
        </div>

        {{-- Right type cards --}}
        <div class="grid grid-cols-2 gap-3" id="right-cards">
            @foreach([
                ['access',         '🔍', 'Droit d\'accès',        'Obtenir une copie de vos données (Art. 15)'],
                ['rectification',  '✏️', 'Rectification',         'Corriger des données inexactes (Art. 16)'],
                ['erasure',        '🗑️', 'Droit à l\'effacement', 'Supprimer votre compte et données (Art. 17)'],
                ['portability',    '📦', 'Portabilité',           'Recevoir vos données en CSV (Art. 20)'],
                ['opposition',     '🚫', 'Droit d\'opposition',   'S\'opposer à certains traitements (Art. 21)'],
                ['limitation',     '⏸️', 'Limitation',            'Suspendre temporairement un traitement (Art. 18)'],
            ] as [$key, $emoji, $title, $desc])
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 cursor-pointer hover:border-teal-300 hover:bg-teal-50/40 transition"
                 onclick="selectRight('{{ $key }}')">
                <div id="card-{{ $key }}" class="flex items-start gap-3 rounded-xl transition p-1">
                    <span class="text-xl leading-none flex-shrink-0 mt-0.5">{{ $emoji }}</span>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $title }}</p>
                        <p class="text-xs text-gray-500 mt-0.5 leading-snug">{{ $desc }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('support.rgpd') }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
            @csrf
            <input type="hidden" name="right_type" id="right_type" value="access">

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Droit sélectionné</label>
                <select name="right_type" id="right_type_select"
                        onchange="document.getElementById('right_type').value=this.value; selectRight(this.value)"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 bg-white">
                    <option value="access">🔍 Droit d'accès (Art. 15)</option>
                    <option value="rectification">✏️ Droit de rectification (Art. 16)</option>
                    <option value="erasure">🗑️ Droit à l'effacement (Art. 17)</option>
                    <option value="portability">📦 Droit à la portabilité (Art. 20)</option>
                    <option value="opposition">🚫 Droit d'opposition (Art. 21)</option>
                    <option value="limitation">⏸️ Droit à la limitation (Art. 18)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    Détails <span class="text-gray-300 font-normal normal-case">(optionnel)</span>
                </label>
                <textarea name="details" rows="4" maxlength="1000"
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 transition resize-none"
                          placeholder="Précisez votre demande si nécessaire…"></textarea>
            </div>

            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 text-xs text-gray-500 leading-relaxed">
                <strong class="text-gray-700">Votre identité :</strong>
                {{ auth()->user()->first_name }} {{ auth()->user()->last_name }} — {{ auth()->user()->email }}
            </div>

            <button type="submit"
                    class="w-full py-3 rounded-xl text-sm font-bold text-white transition hover:opacity-90"
                    style="background:linear-gradient(135deg,#2DD4B0,#14A98C);">
                Envoyer ma demande RGPD
            </button>
        </form>

        <p class="text-center text-xs text-gray-400">
            Vous pouvez aussi écrire directement à
            <a href="mailto:contact@leadxchange.com" class="text-teal-600 underline">contact@leadxchange.com</a>
        </p>
    </div>

    {{-- ── TAB SIGNALEMENT ──────────────────────────────────────────────────── --}}
    @elseif($tab === 'signalement')
    <div class="space-y-5">
        <div class="bg-amber-50 border border-amber-100 rounded-2xl px-5 py-4 text-sm text-amber-800">
            Signalez un membre dont le comportement enfreint les
            <a href="{{ url('/legal/cgu') }}" target="_blank" class="underline">CGU §8.2</a>.
            Notre équipe traitera votre signalement sous <strong>10 jours ouvrés</strong>.
        </div>

        {{-- Category cards --}}
        <div class="grid grid-cols-2 gap-3">
            @foreach([
                ['harcelement',       '😤', 'Harcèlement',          'Menace, intimidation, abus'],
                ['fausses_infos',     '🚩', 'Fausses informations', 'Données trompeuses ou mensongères'],
                ['spam',              '📢', 'Spam',                 'Sollicitation non liée à la plateforme'],
                ['lead_fictif',       '🎭', 'Lead fictif',          'Lead de mauvaise qualité délibérée'],
                ['usurpation',        '🎭', 'Usurpation',           'Usurpation d\'identité'],
                ['autre',             '⚠️', 'Autre',                'Tout autre comportement interdit'],
            ] as [$key, $emoji, $title, $desc])
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 cursor-pointer hover:border-amber-300 hover:bg-amber-50/40 transition"
                 onclick="selectReport('{{ $key }}')">
                <div id="rcard-{{ $key }}" class="flex items-start gap-3 rounded-xl transition p-1">
                    <span class="text-xl leading-none flex-shrink-0 mt-0.5">{{ $emoji }}</span>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $title }}</p>
                        <p class="text-xs text-gray-500 mt-0.5 leading-snug">{{ $desc }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('support.report') }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    Email du membre concerné <span class="text-gray-300 font-normal normal-case">(optionnel)</span>
                </label>
                <input type="email" name="reported_email" value="{{ old('reported_email') }}"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-50 transition"
                       placeholder="email@exemple.com">
                <p class="text-xs text-gray-400 mt-1.5">Laissez vide si votre signalement concerne un problème général.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Motif du signalement</label>
                <select name="reason" id="report_reason"
                        onchange="selectReport(this.value)"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-50 bg-white">
                    <option value="harcelement">😤 Harcèlement / Menace / Intimidation</option>
                    <option value="fausses_infos">🚩 Informations fausses ou trompeuses</option>
                    <option value="spam">📢 Spam / Sollicitation non liée</option>
                    <option value="lead_fictif">🎭 Lead fictif ou de mauvaise qualité</option>
                    <option value="usurpation">🎭 Usurpation d'identité</option>
                    <option value="concurrence_deloy">⚖️ Pratiques commerciales déloyales</option>
                    <option value="autre">⚠️ Autre comportement interdit</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    Description <span class="text-gray-300 font-normal normal-case">(optionnel)</span>
                </label>
                <textarea name="details" rows="4" maxlength="500"
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-50 transition resize-none"
                          placeholder="Décrivez les faits de manière précise…">{{ old('details') }}</textarea>
            </div>

            <button type="submit"
                    class="w-full py-3 rounded-xl text-sm font-bold text-white transition hover:opacity-90"
                    style="background:linear-gradient(135deg,#F59E0B,#D97706);">
                Envoyer le signalement
            </button>
        </form>
    </div>

    {{-- ── TAB MES DEMANDES ─────────────────────────────────────────────────── --}}
    @else
    @php
    $rgpdStatuses = \App\Models\RgpdRequest::STATUSES;
    $reportStatuses = [
        'pending'   => ['label' => 'En attente', 'bg' => 'bg-amber-100',  'text' => 'text-amber-700'],
        'reviewed'  => ['label' => 'Examiné',    'bg' => 'bg-blue-100',   'text' => 'text-blue-700'],
        'actioned'  => ['label' => 'Traité',      'bg' => 'bg-emerald-100','text' => 'text-emerald-700'],
        'dismissed' => ['label' => 'Clôturé',     'bg' => 'bg-gray-100',   'text' => 'text-gray-500'],
    ];
    $rgpdColors = [
        'pending'    => ['bg' => 'bg-amber-100',  'text' => 'text-amber-700'],
        'processing' => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700'],
        'completed'  => ['bg' => 'bg-emerald-100','text' => 'text-emerald-700'],
        'rejected'   => ['bg' => 'bg-red-100',    'text' => 'text-red-700'],
    ];
    @endphp

    @if($rgpdRequests->isEmpty() && $reports->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-16 text-center">
        <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-4">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.5"><path d="M9 12h.01M15 12h.01M12 12h.01"/><circle cx="12" cy="12" r="10"/></svg>
        </div>
        <p class="text-sm font-semibold text-gray-500">Aucune demande pour le moment</p>
        <p class="text-xs text-gray-400 mt-1">Vos demandes RGPD et signalements apparaîtront ici.</p>
    </div>
    @else

    {{-- Demandes RGPD --}}
    @if($rgpdRequests->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-5">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#14A98C" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900">Demandes RGPD</p>
                <p class="text-xs text-gray-400">{{ $rgpdRequests->count() }} demande{{ $rgpdRequests->count() > 1 ? 's' : '' }}</p>
            </div>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($rgpdRequests as $req)
            @php
                $sc = $rgpdColors[$req->status] ?? $rgpdColors['pending'];
                $sl = $rgpdStatuses[$req->status]['label'] ?? $req->status;
            @endphp
            <div class="px-5 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="text-sm font-semibold text-gray-900">
                                {{ \App\Models\RgpdRequest::RIGHTS[$req->right_type] ?? $req->right_type }}
                            </p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $sc['bg'] }} {{ $sc['text'] }}">
                                {{ $sl }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400">{{ $req->created_at->format('d/m/Y à H:i') }}</p>
                        @if($req->details)
                        <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">{{ Str::limit($req->details, 120) }}</p>
                        @endif
                    </div>
                </div>
                @if($req->admin_notes)
                <div class="mt-3 p-3 bg-teal-50 border border-teal-100 rounded-xl">
                    <p class="text-[10px] font-bold text-teal-600 uppercase tracking-widest mb-1">Réponse de l'équipe</p>
                    <p class="text-xs text-teal-800 leading-relaxed">{{ $req->admin_notes }}</p>
                </div>
                @elseif($req->status === 'pending')
                <div class="mt-3 p-3 bg-gray-50 border border-gray-100 rounded-xl">
                    <p class="text-xs text-gray-400">En attente de traitement — réponse sous 1 mois (Art. 12 RGPD).</p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Signalements --}}
    @if($reports->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2"><path d="M10.29 3.86 L1.82 18 A2 2 0 0 0 3.54 21 L20.46 21 A2 2 0 0 0 22.18 18 L13.71 3.86 A2 2 0 0 0 10.29 3.86Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900">Signalements envoyés</p>
                <p class="text-xs text-gray-400">{{ $reports->count() }} signalement{{ $reports->count() > 1 ? 's' : '' }}</p>
            </div>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($reports as $report)
            @php
                $rs = $reportStatuses[$report->status] ?? $reportStatuses['pending'];
                $reasons = \App\Models\UserReport::REASONS;
            @endphp
            <div class="px-5 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $reasons[$report->reason] ?? $report->reason }}
                            </p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $rs['bg'] }} {{ $rs['text'] }}">
                                {{ $rs['label'] }}
                            </span>
                        </div>
                        @if($report->reported)
                        <p class="text-xs text-gray-500">
                            Membre concerné : <strong>{{ $report->reported->first_name }} {{ $report->reported->last_name }}</strong>
                        </p>
                        @else
                        <p class="text-xs text-gray-400">Signalement général (sans membre spécifique)</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-0.5">{{ $report->created_at->format('d/m/Y à H:i') }}</p>
                        @if($report->details)
                        <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">{{ Str::limit($report->details, 120) }}</p>
                        @endif
                    </div>
                </div>
                @if($report->admin_note)
                <div class="mt-3 p-3 bg-amber-50 border border-amber-100 rounded-xl">
                    <p class="text-[10px] font-bold text-amber-600 uppercase tracking-widest mb-1">D��cision de l'équipe</p>
                    <p class="text-xs text-amber-800 leading-relaxed">{{ $report->admin_note }}</p>
                </div>
                @elseif($report->status === 'pending')
                <div class="mt-3 p-3 bg-gray-50 border border-gray-100 rounded-xl">
                    <p class="text-xs text-gray-400">En cours d'examen — traitement sous 10 jours ouvrés.</p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif
    @endif

</div>
@endsection

@push('scripts')
<script>
function selectRight(key) {
    document.querySelectorAll('[id^="card-"]').forEach(el => el.classList.remove('ring-2','ring-teal-400','bg-teal-50'));
    const card = document.getElementById('card-' + key);
    if (card) card.classList.add('ring-2','ring-teal-400','bg-teal-50');
    const hidden = document.getElementById('right_type');
    if (hidden) hidden.value = key;
    const sel = document.getElementById('right_type_select');
    if (sel) sel.value = key;
}

function selectReport(key) {
    document.querySelectorAll('[id^="rcard-"]').forEach(el => el.classList.remove('ring-2','ring-amber-400','bg-amber-50'));
    const card = document.getElementById('rcard-' + key);
    if (card) card.classList.add('ring-2','ring-amber-400','bg-amber-50');
    const sel = document.getElementById('report_reason');
    if (sel) sel.value = key;
}

// Highlight first card on load
document.addEventListener('DOMContentLoaded', () => {
    const firstRight = document.getElementById('card-access');
    if (firstRight) firstRight.classList.add('ring-2','ring-teal-400','bg-teal-50');
    const firstReport = document.getElementById('rcard-harcelement');
    if (firstReport) firstReport.classList.add('ring-2','ring-amber-400','bg-amber-50');
});
</script>
@endpush
