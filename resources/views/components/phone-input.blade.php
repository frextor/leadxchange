{{--
    Reusable phone input component with country-code picker + flag.

    Props:
        $codeId      — id of the <select> (default: "phone_country_code")
        $codeValue   — current/old value for country code (e.g. "+212")
        $codeName    — name attribute of the <select> (default: "phone_country_code")
        $phoneId     — id of the <input>  (default: "phone")
        $phoneValue  — current/old value for the number
        $phoneName   — name attribute of the <input> (default: "phone")
        $inputClass  — extra classes on the <input>
        $dark        — true if inside a dark/colored background
--}}
@props([
    'codeId'     => 'phone_country_code',
    'codeValue'  => '',
    'codeName'   => 'phone_country_code',
    'phoneId'    => 'phone',
    'phoneValue' => '',
    'phoneName'  => 'phone',
    'inputClass' => '',
    'dark'       => false,
])

@php
$dialCodes = [
    // ── Popular ────────────────────────────────────
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
    ['+20',  '🇪🇬', 'Égypte'],
    ['+221', '🇸🇳', 'Sénégal'],
    ['+225', '🇨🇮', 'Côte d\'Ivoire'],
    ['+237', '🇨🇲', 'Cameroun'],
    ['+243', '🇨🇩', 'Congo RDC'],
    ['+27',  '🇿🇦', 'Afrique du Sud'],
    ['+971', '🇦🇪', 'Émirats arabes unis'],
    ['+966', '🇸🇦', 'Arabie Saoudite'],
    ['+90',  '🇹🇷', 'Turquie'],
    ['+86',  '🇨🇳', 'Chine'],
    ['+91',  '🇮🇳', 'Inde'],
    ['+55',  '🇧🇷', 'Brésil'],
    ['+52',  '🇲🇽', 'Mexique'],
    ['+61',  '🇦🇺', 'Australie'],
    ['+81',  '🇯🇵', 'Japon'],
    ['+82',  '🇰🇷', 'Corée du Sud'],
    ['+7',   '🇷🇺', 'Russie'],
    ['+48',  '🇵🇱', 'Pologne'],
    ['+46',  '🇸🇪', 'Suède'],
    ['+47',  '🇳🇴', 'Norvège'],
    ['+45',  '🇩🇰', 'Danemark'],
    ['+358', '🇫🇮', 'Finlande'],
    ['+36',  '🇭🇺', 'Hongrie'],
    ['+420', '🇨🇿', 'Tchéquie'],
    ['+40',  '🇷🇴', 'Roumanie'],
    ['+380', '🇺🇦', 'Ukraine'],
    ['+30',  '🇬🇷', 'Grèce'],
    ['+351', '🇵🇹', 'Portugal'],
    ['+353', '🇮🇪', 'Irlande'],
];
@endphp

<div class="flex gap-2">
    {{-- Country code picker --}}
    <div class="relative flex-shrink-0">
        <select id="{{ $codeId }}" name="{{ $codeName }}"
            class="appearance-none h-full pl-3 pr-8 rounded-xl border text-sm font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-teal-400 transition-all"
            style="border-color:#E5E7EB;background:#F9FAFB;color:#374151;min-width:90px;"
            onchange="updatePhoneFlag('{{ $codeId }}')">
            @foreach($dialCodes as [$code, $flag, $label])
                <option value="{{ $code }}" {{ $codeValue === $code ? 'selected' : '' }}>
                    {{ $flag }} {{ $code }}
                </option>
            @endforeach
        </select>
        <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
        </span>
    </div>

    {{-- Number input --}}
    <input type="tel" id="{{ $phoneId }}" name="{{ $phoneName }}"
        placeholder="6 12 34 56 78"
        value="{{ $phoneValue }}"
        inputmode="numeric"
        class="flex-1 px-4 py-3.5 rounded-xl border text-sm transition-all focus:outline-none focus:ring-2 focus:ring-teal-400 {{ $inputClass }}"
        style="border-color:#E5E7EB;background:#F9FAFB;color:#111827;">
</div>
