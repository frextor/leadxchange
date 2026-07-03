{{-- Reusable phone input with custom flag dropdown (emoji-safe on Windows) --}}
@props([
    'codeId'     => 'phone_country_code',
    'codeValue'  => '+33',
    'codeName'   => 'phone_country_code',
    'phoneId'    => 'phone',
    'phoneValue' => '',
    'phoneName'  => 'phone',
    'inputClass' => '',
    'dark'       => false,
])

@php
$dialCodes = [
    ['+212', '🇲🇦', 'Maroc'],
    ['+33',  '🇫🇷', 'France'],
    ['+32',  '🇧🇪', 'Belgique'],
    ['+41',  '🇨🇭', 'Suisse'],
    ['+1',   '🇺🇸', 'États-Unis / Canada'],
    ['+44',  '🇬🇧', 'Royaume-Uni'],
    ['+49',  '🇩🇪', 'Allemagne'],
    ['+34',  '🇪🇸', 'Espagne'],
    ['+39',  '🇮🇹', 'Italie'],
    ['+31',  '🇳🇱', 'Pays-Bas'],
    ['+351', '🇵🇹', 'Portugal'],
    ['+216', '🇹🇳', 'Tunisie'],
    ['+213', '🇩🇿', 'Algérie'],
    ['+221', '🇸🇳', 'Sénégal'],
    ['+225', '🇨🇮', 'Côte d\'Ivoire'],
    ['+237', '🇨🇲', 'Cameroun'],
    ['+243', '🇨🇩', 'RD Congo'],
    ['+20',  '🇪🇬', 'Égypte'],
    ['+971', '🇦🇪', 'Émirats Arabes Unis'],
    ['+966', '🇸🇦', 'Arabie Saoudite'],
    ['+52',  '🇲🇽', 'Mexique'],
    ['+55',  '🇧🇷', 'Brésil'],
    ['+91',  '🇮🇳', 'Inde'],
    ['+86',  '🇨🇳', 'Chine'],
    ['+81',  '🇯🇵', 'Japon'],
    ['+82',  '🇰🇷', 'Corée du Sud'],
    ['+7',   '🇷🇺', 'Russie'],
    ['+48',  '🇵🇱', 'Pologne'],
    ['+46',  '🇸🇪', 'Suède'],
    ['+47',  '🇳🇴', 'Norvège'],
    ['+45',  '🇩🇰', 'Danemark'],
    ['+358', '🇫🇮', 'Finlande'],
    ['+30',  '🇬🇷', 'Grèce'],
    ['+380', '🇺🇦', 'Ukraine'],
];

$initialFlag  = collect($dialCodes)->firstWhere(0, $codeValue)[1] ?? '🌍';
$initialLabel = $codeValue;
@endphp

<div class="flex gap-2"
     x-data="{
        open: false,
        code: '{{ $codeValue }}',
        flag: '{{ $initialFlag }}',
        search: '',
        codes: {{ json_encode(array_map(fn($d) => ['code'=>$d[0],'flag'=>$d[1],'label'=>$d[2]], $dialCodes)) }},
        get filtered() {
            if (!this.search) return this.codes;
            const s = this.search.toLowerCase();
            return this.codes.filter(c => c.label.toLowerCase().includes(s) || c.code.includes(s));
        },
        select(c) {
            this.code = c.code;
            this.flag = c.flag;
            this.open = false;
            this.search = '';
        }
     }"
     @click.outside="open = false">

    {{-- Hidden input for form submission --}}
    <input type="hidden" id="{{ $codeId }}" name="{{ $codeName }}" :value="code">

    {{-- Custom trigger button --}}
    <div class="relative flex-shrink-0">
        <button type="button"
                @click="open = !open"
                class="flex items-center gap-1.5 h-full px-3 rounded-xl border text-sm font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-teal-400 transition-all whitespace-nowrap"
                style="border-color:#E5E7EB;background:#F9FAFB;color:#374151;min-width:90px;">
            <span x-text="flag" class="text-lg leading-none"></span>
            <span x-text="code" class="text-xs font-semibold"></span>
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="ml-auto"><polyline points="6 9 12 15 18 9"/></svg>
        </button>

        {{-- Dropdown panel --}}
        <div x-show="open" x-cloak
             class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden"
             style="min-width:220px;">
            {{-- Search --}}
            <div class="p-2 border-b border-gray-100">
                <input type="text" x-model="search" placeholder="Rechercher…"
                       class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-teal-400"
                       @click.stop>
            </div>
            {{-- Options --}}
            <ul class="max-h-52 overflow-y-auto py-1">
                <template x-for="c in filtered" :key="c.code">
                    <li @click="select(c)"
                        class="flex items-center gap-2.5 px-3 py-2 cursor-pointer hover:bg-teal-50 transition-colors"
                        :class="{ 'bg-teal-50': c.code === code }">
                        <span x-text="c.flag" class="text-lg leading-none flex-shrink-0"></span>
                        <span x-text="c.label" class="text-sm text-gray-700 flex-1 truncate"></span>
                        <span x-text="c.code" class="text-xs text-gray-400 font-mono flex-shrink-0"></span>
                    </li>
                </template>
                <li x-show="filtered.length === 0" class="px-3 py-3 text-sm text-gray-400 text-center">Aucun résultat</li>
            </ul>
        </div>
    </div>

    {{-- Number input --}}
    <input type="tel" id="{{ $phoneId }}" name="{{ $phoneName }}"
        placeholder="6 12 34 56 78"
        value="{{ $phoneValue }}"
        inputmode="numeric"
        class="flex-1 px-4 py-3.5 rounded-xl border text-sm transition-all focus:outline-none focus:ring-2 focus:ring-teal-400 {{ $inputClass }}"
        style="border-color:#E5E7EB;background:#F9FAFB;color:#111827;">
</div>
