@extends('ambassador.layouts.ambassador')
@section('title', 'Mon Profil Ambassadeur')
@section('page-title', 'Mon Profil')
@section('page-subtitle', 'Badge · Accomplissements · Statistiques')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Profile card ─────────────────────────────────────────────────────── --}}
    <div class="space-y-4">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            {{-- Header gradient --}}
            <div class="h-24" style="background:linear-gradient(135deg,#0F1629,#14B8A6);"></div>
            <div class="px-5 pb-5 -mt-10">
                @if($ambassador->profile?->avatar)
                    <img src="{{ $ambassador->profile->avatar_url }}"
                         class="w-20 h-20 rounded-2xl object-cover border-4 border-white shadow-md mb-3">
                @else
                    <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-white font-bold text-2xl border-4 border-white shadow-md mb-3"
                         style="background:linear-gradient(135deg,#2DD4BF,#14A98C);">
                        {{ strtoupper(substr($ambassador->first_name,0,1)) }}{{ strtoupper(substr($ambassador->last_name,0,1)) }}
                    </div>
                @endif

                <p class="text-lg font-bold text-slate-800">{{ $ambassador->first_name }} {{ $ambassador->last_name }}</p>
                <p class="text-sm text-slate-500">{{ $ambassador->company?->name }}</p>

                <div class="flex flex-wrap gap-2 mt-3">
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-full text-white"
                          style="background:linear-gradient(135deg,#14B8A6,#0F766E);">
                        🏅 Ambassadeur
                    </span>
                    @if($ambassador->region || $ambassador->city)
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                        📍 {{ $ambassador->region?->name ?? $ambassador->city?->name }}
                    </span>
                    @endif
                </div>

                @if($profile?->biography)
                <p class="text-sm text-slate-600 mt-4 leading-relaxed">{{ $profile->biography }}</p>
                @endif

                @if($profile?->linkedin_url)
                <a href="{{ $profile->linkedin_url }}" target="_blank"
                   class="mt-3 flex items-center gap-2 text-xs font-semibold text-blue-600 hover:underline">
                    <svg viewBox="0 0 24 24" class="w-4 h-4 fill-current"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    LinkedIn
                </a>
                @endif
            </div>
        </div>

        {{-- Stats --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wide mb-4">Statistiques</h3>
            <div class="space-y-3">
                @php $statItems = [
                    ['label' => 'Membres dans la région', 'value' => $kpis['members_total'], 'color' => 'text-teal-600'],
                    ['label' => 'Événements organisés',   'value' => $kpis['events_total'],  'color' => 'text-indigo-600'],
                    ['label' => 'Leads générés',          'value' => $kpis['leads_generated'],'color' => 'text-amber-600'],
                    ['label' => 'Score ambassadeur',       'value' => number_format($kpis['score']) . ' pts', 'color' => 'text-slate-800'],
                    ['label' => 'Rang national',           'value' => '#' . $kpis['national_rank'], 'color' => 'text-slate-800'],
                ]; @endphp
                @foreach($statItems as $stat)
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">{{ $stat['label'] }}</span>
                    <span class="text-sm font-bold {{ $stat['color'] }}">{{ $stat['value'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Edit + Achievements ─────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Edit form --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-5">Modifier mon profil ambassadeur</h3>
            <form method="POST" action="{{ route('ambassador.profile.update') }}" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Biographie</label>
                    <textarea name="biography" rows="5"
                              placeholder="Décrivez votre rôle d'ambassadeur, votre région, votre vision du networking..."
                              class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300 resize-none">{{ old('biography', $profile?->biography) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">URL LinkedIn</label>
                    <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $profile?->linkedin_url) }}"
                           placeholder="https://linkedin.com/in/..."
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white"
                        style="background:linear-gradient(135deg,#14B8A6,#0F766E);">
                    Enregistrer
                </button>
            </form>
        </div>

        {{-- Achievements --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-5">Badges & Accomplissements</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach(\App\Models\AmbassadorProfile::ALL_ACHIEVEMENTS as $achievement)
                @php $earned = in_array($achievement['key'], $profile?->achievements ?? []); @endphp
                <div class="p-4 rounded-xl border text-center transition
                    {{ $earned ? 'border-teal-200 bg-teal-50' : 'border-gray-100 bg-gray-50 opacity-40' }}">
                    <span class="text-3xl">{{ $achievement['emoji'] }}</span>
                    <p class="text-xs font-bold mt-2 {{ $earned ? 'text-teal-700' : 'text-slate-400' }}">
                        {{ $achievement['label'] }}
                    </p>
                    <p class="text-[10px] text-slate-400 mt-1">
                        {{ $earned ? '✅ Obtenu' : number_format($achievement['threshold']) . ' pts requis' }}
                    </p>
                </div>
                @endforeach
            </div>

            <div class="mt-5 p-4 rounded-xl bg-slate-50 border border-slate-200">
                <p class="text-xs text-slate-600">
                    <span class="font-bold">Score actuel :</span>
                    <span class="text-teal-700 font-bold text-sm ml-1">{{ number_format($kpis['score']) }} pts</span>
                </p>
                <p class="text-[11px] text-slate-400 mt-1">
                    Le score est calculé automatiquement en fonction de vos membres, événements, leads et invitations acceptées.
                </p>
            </div>
        </div>
    </div>
</div>

@endsection
