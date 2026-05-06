@extends('layouts.dashboard')

@section('title', $user['first_name'] . ' ' . $user['last_name'])

@push('styles')
<style>
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 50;
        display: flex; align-items: center; justify-content: center; padding: 1rem;
    }
    .modal-box {
        background: white; border-radius: 1rem;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        width: 100%; max-width: 32rem; max-height: 90vh; overflow-y: auto;
    }
    .modal-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.5rem; border-bottom: 1px solid #F3F4F6;
    }
    .modal-body { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
    .modal-footer {
        padding: 1rem 1.5rem; border-top: 1px solid #F3F4F6;
        display: flex; justify-content: flex-end; gap: 0.75rem;
    }
    .inp {
        width: 100%; padding: 0.625rem 1rem; border: 1px solid #E5E7EB;
        border-radius: 0.5rem; font-size: 0.875rem; color: #111827;
        outline: none; font-family: inherit; transition: border-color .15s, box-shadow .15s;
        background: white;
    }
    .inp:focus { border-color: #1E8F88; box-shadow: 0 0 0 3px rgba(30,143,136,0.12); }
    select.inp { appearance: none; cursor: pointer; }
    textarea.inp { resize: vertical; }
    .lbl {
        display: block; font-size: 0.7rem; font-weight: 700; color: #6B7280;
        text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.4rem;
    }
    .btn-primary {
        padding: 0.6rem 1.25rem; color: white; font-weight: 600;
        border-radius: 0.5rem; font-size: 0.875rem; cursor: pointer; border: none;
        background: linear-gradient(135deg, #2BB6A3, #1E8F88);
        box-shadow: 0 4px 12px -4px rgba(43,182,163,0.5);
        transition: opacity .15s;
    }
    .btn-primary:hover { opacity: .88; }
    .btn-primary:disabled { opacity: .5; cursor: not-allowed; }
    .btn-ghost {
        padding: 0.6rem 1.25rem; color: #4B5563; background: transparent;
        border: 1px solid #E5E7EB; border-radius: 0.5rem; font-size: 0.875rem;
        cursor: pointer; font-family: inherit; transition: background .12s;
    }
    .btn-ghost:hover { background: #F9FAFB; }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="grid gap-6 lg:grid-cols-[320px_1fr] items-start">

        {{-- ── LEFT SIDEBAR ── --}}
        <aside class="space-y-5 lg:sticky lg:top-6">

            {{-- Profile card --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="h-20" style="background: linear-gradient(135deg, #34d4bf, #1E8F88);"></div>

                <div class="px-5 pb-5 -mt-10">
                    {{-- Avatar --}}
                    <div class="relative w-20 h-20 mb-3.5">
                        @if ($profile?->avatar_url)
                            <img id="avatarImg" src="{{ $profile->avatar_url }}" alt="Avatar"
                                 class="w-20 h-20 rounded-full object-cover border-4 border-white">
                        @else
                            <div id="avatarImg" class="w-20 h-20 rounded-full border-4 border-white flex items-center justify-center text-white font-semibold text-[28px]"
                                 style="background: linear-gradient(135deg, hsl(165 60% 60%), hsl(180 55% 45%));">
                                {{ strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) }}
                            </div>
                        @endif

                        @if ($isOwnProfile)
                        <label for="avatarInput"
                               class="absolute bottom-0.5 right-0.5 w-7 h-7 rounded-full flex items-center justify-center cursor-pointer shadow-md transition"
                               style="background:#1E8F88;"
                               title="Changer la photo">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        </label>
                        <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp" class="hidden">
                        @endif
                    </div>

                    {{-- Name & title --}}
                    <h1 class="text-xl font-semibold text-gray-900 leading-tight tracking-tight">
                        {{ $user['first_name'] }} {{ $user['last_name'] }}
                    </h1>
                    <div class="text-sm text-gray-500 mt-0.5">
                        {{ $profile?->job_title ?? '' }}
                        @if ($profile?->job_title && $profile?->sector) · @endif
                        {{ $profile?->sector ?? '' }}
                    </div>

                    {{-- Meta --}}
                    <div class="mt-2.5 space-y-1.5">
                        @if ($user['city_living'])
                        <div class="flex items-center gap-1.5 text-[13px] text-gray-400">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            {{ $user['city_living'] }}
                        </div>
                        @endif
                        @if ($user['member_since'])
                        <div class="flex items-center gap-1.5 text-[13px] text-gray-400">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Membre depuis {{ $user['member_since'] }}
                        </div>
                        @endif
                        @if ($profile?->open_to_network)
                        <div class="mt-3 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border"
                             style="background:#E6F7F4;color:#1E8F88;border-color:#A8E2D9;">
                            <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background:#10B981;"></span>
                            Open to network
                        </div>
                        @endif
                    </div>

                    {{-- Action button --}}
                    <div id="connectionAction" class="mt-4">
                        @if ($isOwnProfile)
                            <button onclick="openModal('modal-basic')"
                                class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 rounded-[10px] text-white font-semibold text-sm"
                                style="background: linear-gradient(135deg, #2BB6A3, #1E8F88); box-shadow: 0 6px 14px -6px rgba(43,182,163,0.5);">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
                                Modifier le profil
                            </button>
                        @elseif ($user['connection_status'] === 'accepted')
                            <button disabled class="w-full py-2.5 rounded-[10px] bg-gray-100 text-gray-500 font-semibold text-sm cursor-not-allowed flex items-center justify-center gap-2">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 5 5L20 7"/></svg>
                                Connecté
                            </button>
                        @elseif ($user['connection_status'] === 'pending' && $user['i_am_sender'])
                            <button disabled class="w-full py-2.5 rounded-[10px] font-semibold text-sm cursor-not-allowed flex items-center justify-center gap-2" style="background:#FFFBEB;color:#92400E;border:1px solid #FDE68A;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                En attente
                            </button>
                        @elseif ($user['connection_status'] === 'pending' && $user['i_am_receiver'])
                            <div class="flex gap-2">
                                <button onclick="acceptRequest({{ $user['connection_id'] }})"
                                        id="accept-btn-{{ $user['connection_id'] }}"
                                        class="flex-1 py-2.5 text-white font-semibold rounded-[10px] transition text-sm flex items-center justify-center gap-1.5"
                                        style="background: linear-gradient(135deg, #2BB6A3, #1E8F88);">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
                                    Accepter
                                </button>
                                <button onclick="rejectRequest({{ $user['connection_id'] }}, {{ $user['id'] }})"
                                        id="reject-btn-{{ $user['connection_id'] }}"
                                        class="flex-1 py-2.5 font-semibold rounded-[10px] transition text-sm flex items-center justify-center gap-1.5"
                                        style="background:#FEF2F2;color:#DC2626;border:1px solid #FECACA;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    Refuser
                                </button>
                            </div>
                        @else
                            <button onclick="connect({{ $user['id'] }})" id="connect-btn"
                                    class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 rounded-[10px] text-white font-semibold text-sm"
                                    style="background: linear-gradient(135deg, #2BB6A3, #1E8F88); box-shadow: 0 6px 14px -6px rgba(43,182,163,0.5);">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                                Se connecter
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Stats card --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <div class="text-[12px] font-semibold text-gray-400 uppercase tracking-wider mb-3">Statistiques</div>
                <div class="grid grid-cols-2 gap-3">
                    <x-stat value="0" label="Échanges" />
                    <x-stat value="0" label="Leads reçus" />
                    <x-stat value="0" label="Connexions" />
                    <x-stat value="4.8" label="Score" :star="true" />
                </div>
            </div>

            {{-- Completion card (own profile only) --}}
            @if ($isOwnProfile && $completion < 100)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
                <div class="flex justify-between items-baseline mb-2">
                    <span class="text-[13px] font-semibold text-gray-900">Complétion du profil</span>
                    <span class="text-lg font-semibold" style="color:#1E8F88;">{{ $completion }}%</span>
                </div>
                <div class="h-1.5 rounded-full overflow-hidden" style="background:#E5E7EB;">
                    <div class="h-full rounded-full transition-all duration-500"
                         style="width:{{ $completion }}%; background: linear-gradient(90deg, #34d4bf, #1E8F88);"></div>
                </div>
                <ul class="mt-3.5 flex flex-col gap-1.5">
                    @foreach ($missing as $item)
                    <li class="flex items-center gap-2 text-[13px] text-gray-400">
                        <span class="w-4 h-4 rounded-full border border-dashed border-gray-300 flex-shrink-0"></span>
                        {{ $item['label'] }}
                    </li>
                    @endforeach
                </ul>
                <button onclick="openModal('modal-missing')"
                    class="mt-4 w-full py-2 text-white text-sm font-semibold rounded-[10px] transition"
                    style="background: linear-gradient(135deg, #2BB6A3, #1E8F88);">
                    Compléter le profil
                </button>
            </div>
            @endif

        </aside>

        {{-- ── MAIN CONTENT ── --}}
        <div class="space-y-5">

            {{-- À propos --}}
            <x-profile-section title="À propos" :editModal="$isOwnProfile ? 'modal-bio' : null">
                @if ($profile?->bio || $profile?->motto)
                    @if ($profile?->motto)
                    <x-profile-block label="Motto">
                        <span class="italic">"{{ $profile->motto }}"</span>
                    </x-profile-block>
                    @endif
                    @if ($profile?->bio)
                    <x-profile-block label="Bio">{{ $profile->bio }}</x-profile-block>
                    @endif
                @else
                    <div class="text-center py-4 text-gray-400">
                        <p class="text-sm">
                            @if ($isOwnProfile)
                                Partagez votre motto ou votre bio
                            @else
                                Aucune bio
                            @endif
                        </p>
                        @if ($isOwnProfile)
                        <button onclick="openModal('modal-bio')" class="text-teal-600 text-xs font-semibold hover:underline mt-1">+ Ajouter</button>
                        @endif
                    </div>
                @endif
            </x-profile-section>

            {{-- Profil professionnel --}}
            <x-profile-section title="Profil professionnel" :editModal="$isOwnProfile ? 'modal-professional' : null">
                @if ($profile?->job_title || $profile?->sector || $profile?->experience_level || $profile?->looking_for || $profile?->services_offered)
                    @php $expLabels = ['junior' => 'Junior (0-2 ans)', 'mid' => 'Intermédiaire (2-5 ans)', 'senior' => 'Senior (5-10 ans)', 'expert' => 'Expert (10+ ans)']; @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if ($profile?->job_title)
                        <x-profile-kv label="Poste">{{ $profile->job_title }}</x-profile-kv>
                        @endif
                        @if ($profile?->sector)
                        <x-profile-kv label="Secteur">{{ $profile->sector }}</x-profile-kv>
                        @endif
                        @if ($profile?->experience_level)
                        <x-profile-kv label="Expérience">{{ $expLabels[$profile->experience_level] ?? $profile->experience_level }}</x-profile-kv>
                        @endif
                    </div>
                    @if ($profile?->looking_for)
                    <x-profile-block label="Recherche">{{ $profile->looking_for }}</x-profile-block>
                    @endif
                    @if ($profile?->services_offered)
                    <x-profile-block label="Services proposés">{{ $profile->services_offered }}</x-profile-block>
                    @endif
                @else
                    <div class="text-center py-4 text-gray-400">
                        <p class="text-sm">
                            @if ($isOwnProfile)
                                Ajoutez vos informations professionnelles
                            @else
                                Aucune information professionnelle
                            @endif
                        </p>
                        @if ($isOwnProfile)
                        <button onclick="openModal('modal-professional')" class="text-teal-600 text-xs font-semibold hover:underline mt-1">+ Ajouter</button>
                        @endif
                    </div>
                @endif
            </x-profile-section>

            {{-- Centres d'intérêt --}}
            <x-profile-section title="Centres d'intérêt" :editModal="$isOwnProfile ? 'modal-interests' : null">
                @if ($userInterests->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach ($userInterests as $interest)
                    <span class="px-3.5 py-1.5 rounded-full text-[13px] font-medium border"
                          style="background:#E6F7F4;color:#1E8F88;border-color:#A8E2D9;">
                        {{ $interest->icon }} {{ $interest->name }}
                    </span>
                    @endforeach
                    @if ($isOwnProfile)
                    <button onclick="openModal('modal-interests')"
                            class="px-3.5 py-1.5 rounded-full text-gray-400 text-[13px] font-medium border border-dashed border-gray-300 hover:border-gray-400 hover:text-gray-600 transition bg-transparent">
                        + Ajouter
                    </button>
                    @endif
                </div>
                @else
                <div class="flex flex-wrap gap-2">
                    <span class="text-[13px] text-gray-400">Aucun centre d'intérêt.</span>
                    @if ($isOwnProfile)
                    <button onclick="openModal('modal-interests')"
                            class="px-3.5 py-1.5 rounded-full text-gray-400 text-[13px] font-medium border border-dashed border-gray-300 hover:border-gray-400 hover:text-gray-600 transition bg-transparent">
                        + Ajouter un intérêt
                    </button>
                    @endif
                </div>
                @endif
            </x-profile-section>

            {{-- Entreprise --}}
            <x-profile-section title="Entreprise">
                @if ($user['company'])
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center text-2xl font-semibold flex-shrink-0"
                         style="color:#1E8F88;">
                        {{ strtoupper(substr($user['company']['name'], 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-base font-semibold text-gray-900">{{ $user['company']['name'] }}</div>
                        <div class="flex flex-wrap items-center gap-1.5 text-[13px] text-gray-400 mt-1">
                            @if ($user['company']['sector'])
                            <span>{{ $user['company']['sector'] }}</span>
                            @endif
                            @if ($user['company']['sector'] && $user['company']['website'])
                            <span>·</span>
                            @endif
                            @if ($user['company']['website'])
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            <a href="{{ $user['company']['website'] }}" target="_blank" rel="noopener"
                               class="hover:underline truncate" style="color:#1E8F88;">{{ $user['company']['website'] }}</a>
                            @endif
                        </div>
                    </div>
                </div>
                @else
                <div class="text-[13px] text-gray-400">
                    Aucune entreprise liée.
                    @if ($isOwnProfile)
                    <a href="{{ route('company.create') }}" class="font-semibold hover:underline ml-1" style="color:#1E8F88;">+ Ajouter</a>
                    @endif
                </div>
                @endif
            </x-profile-section>

            {{-- Informations personnelles --}}
            <x-profile-section title="Informations" :editModal="$isOwnProfile ? 'modal-basic' : null">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-profile-kv label="Email">{{ $user['email'] }}</x-profile-kv>
                    @if ($user['city_living'])
                    <x-profile-kv label="Ville actuelle">{{ $user['city_living'] }}</x-profile-kv>
                    @endif
                    @if ($user['city_birth'])
                    <x-profile-kv label="Ville de naissance">{{ $user['city_birth'] }}</x-profile-kv>
                    @endif
                    @if ($user['gender'])
                    <x-profile-kv label="Genre"><span class="capitalize">{{ $user['gender'] }}</span></x-profile-kv>
                    @endif
                    @if ($user['birthday'])
                    <x-profile-kv label="Date de naissance">{{ \Carbon\Carbon::parse($user['birthday'])->format('d F Y') }}</x-profile-kv>
                    @endif
                </div>
            </x-profile-section>

        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════
     MODALS (own profile only)
════════════════════════════════════════════════ --}}
@if ($isOwnProfile)

{{-- Missing fields modal --}}
<div id="modal-missing" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Compléter votre profil</h3>
                <p class="text-sm text-gray-500 mt-0.5">{{ $completion }}% complété</p>
            </div>
            <button onclick="closeModal('modal-missing')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="modal-body">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach ($missing as $item)
                @php
                    $modalMap = [
                        'avatar'      => 'avatar-input',
                        'bio'         => 'modal-bio',
                        'job_title'   => 'modal-professional',
                        'sector'      => 'modal-professional',
                        'experience'  => 'modal-professional',
                        'location'    => 'modal-basic',
                        'company'     => null,
                        'interests'   => 'modal-interests',
                        'looking_for' => 'modal-professional',
                        'gender'      => 'modal-basic',
                    ];
                    $target = $modalMap[$item['key']] ?? null;
                @endphp
                <button
                    onclick="{{ $target === 'avatar-input' ? "document.getElementById('avatarInput').click(); closeModal('modal-missing')" : ($target ? "closeModal('modal-missing'); setTimeout(()=>openModal('{$target}'),200)" : "closeModal('modal-missing')") }}"
                    class="flex flex-col items-center gap-2 p-4 border-2 border-dashed border-gray-200 hover:border-teal-400 hover:bg-teal-50 rounded-xl transition text-center">
                    <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                        <i class="fas {{ $item['icon'] }} text-orange-500 text-sm"></i>
                    </div>
                    <span class="text-xs font-semibold text-gray-700">{{ $item['label'] }}</span>
                    <span class="text-xs text-orange-500 font-medium">Manquant</span>
                </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Basic Info modal --}}
<div id="modal-basic" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-gray-900">Informations personnelles</h3>
            <button onclick="closeModal('modal-basic')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="modal-body">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="lbl">Prénom</label>
                    <input id="b_first_name" type="text" class="inp" value="{{ $user['first_name'] }}">
                </div>
                <div>
                    <label class="lbl">Nom</label>
                    <input id="b_last_name" type="text" class="inp" value="{{ $user['last_name'] }}">
                </div>
            </div>
            <div>
                <label class="lbl">Genre</label>
                <select id="b_gender" class="inp">
                    <option value="">— Non renseigné —</option>
                    <option value="male" {{ $user['gender'] === 'male' ? 'selected' : '' }}>Homme</option>
                    <option value="female" {{ $user['gender'] === 'female' ? 'selected' : '' }}>Femme</option>
                    <option value="other" {{ $user['gender'] === 'other' ? 'selected' : '' }}>Autre</option>
                </select>
            </div>
            <div>
                <label class="lbl">Date de naissance</label>
                <input id="b_birthday" type="date" class="inp" value="{{ $user['birthday'] ?? '' }}">
            </div>
            <div>
                <label class="lbl">Ville actuelle</label>
                <input id="b_city_living" type="text" class="inp" placeholder="Paris, Casablanca…" value="{{ $user['city_living'] ?? '' }}">
            </div>
            <div>
                <label class="lbl">Ville de naissance</label>
                <input id="b_city_birth" type="text" class="inp" placeholder="Rabat, Lyon…" value="{{ $user['city_birth'] ?? '' }}">
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeModal('modal-basic')" class="btn-ghost">Annuler</button>
            <button onclick="saveBasic()" id="btn-save-basic" class="btn-primary">Enregistrer</button>
        </div>
    </div>
</div>

{{-- Bio modal --}}
<div id="modal-bio" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-gray-900">À propos</h3>
            <button onclick="closeModal('modal-bio')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="modal-body">
            <div>
                <label class="lbl">Motto <span class="text-gray-400 font-normal normal-case">(phrase courte)</span></label>
                <input id="bio_motto" type="text" maxlength="500" class="inp"
                       placeholder="Summarize yourself in one line…"
                       value="{{ $profile?->motto ?? '' }}">
                <p class="text-xs text-gray-400 mt-1 text-right"><span id="mottoCount">{{ strlen($profile?->motto ?? '') }}</span> / 500</p>
            </div>
            <div>
                <label class="lbl">Bio</label>
                <textarea id="bio_bio" rows="5" maxlength="1000" class="inp resize-none"
                          placeholder="Parlez de vous, de votre parcours, de vos ambitions…">{{ $profile?->bio ?? '' }}</textarea>
            </div>
            <div class="flex items-center gap-3 pt-1">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="bio_open" class="sr-only peer" {{ $profile?->open_to_network ? 'checked' : '' }}>
                    <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:bg-teal-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
                <span class="text-sm text-gray-700">Open to network</span>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeModal('modal-bio')" class="btn-ghost">Annuler</button>
            <button onclick="saveBio()" id="btn-save-bio" class="btn-primary">Enregistrer</button>
        </div>
    </div>
</div>

{{-- Professional modal --}}
<div id="modal-professional" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-gray-900">Profil professionnel</h3>
            <button onclick="closeModal('modal-professional')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="modal-body">
            <div>
                <label class="lbl">Intitulé du poste</label>
                <input id="pro_job_title" type="text" class="inp" placeholder="ex: Chef de projet digital"
                       value="{{ $profile?->job_title ?? '' }}">
            </div>
            <div>
                <label class="lbl">Secteur d'activité</label>
                <select id="pro_sector" class="inp">
                    <option value="">— Sélectionner —</option>
                    @php
                    $sectors = ['Technologie','Marketing','Finance','Ventes','Ressources humaines','Juridique','Santé','Éducation','Immobilier','Consulting','E-commerce','Startup','Logistique','Industrie','Médias','Design','Construction','Agriculture','Tourisme','Intelligence artificielle','Autre'];
                    @endphp
                    @foreach ($sectors as $s)
                    <option value="{{ $s }}" {{ $profile?->sector === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="lbl">Niveau d'expérience</label>
                <select id="pro_experience" class="inp">
                    <option value="">— Sélectionner —</option>
                    <option value="junior"  {{ $profile?->experience_level === 'junior' ? 'selected' : '' }}>Junior (0-2 ans)</option>
                    <option value="mid"     {{ $profile?->experience_level === 'mid' ? 'selected' : '' }}>Intermédiaire (2-5 ans)</option>
                    <option value="senior"  {{ $profile?->experience_level === 'senior' ? 'selected' : '' }}>Senior (5-10 ans)</option>
                    <option value="expert"  {{ $profile?->experience_level === 'expert' ? 'selected' : '' }}>Expert (10+ ans)</option>
                </select>
            </div>
            <div>
                <label class="lbl">Recherche <span class="text-gray-400 font-normal normal-case">(ce que vous cherchez)</span></label>
                <textarea id="pro_looking_for" rows="3" class="inp resize-none"
                          placeholder="ex: Partenaires commerciaux dans le secteur tech…">{{ $profile?->looking_for ?? '' }}</textarea>
            </div>
            <div>
                <label class="lbl">Services proposés</label>
                <textarea id="pro_services" rows="3" class="inp resize-none"
                          placeholder="ex: Consulting en stratégie digitale…">{{ $profile?->services_offered ?? '' }}</textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeModal('modal-professional')" class="btn-ghost">Annuler</button>
            <button onclick="saveProfessional()" id="btn-save-pro" class="btn-primary">Enregistrer</button>
        </div>
    </div>
</div>

{{-- Interests modal --}}
<div id="modal-interests" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-gray-900">Centres d'intérêt</h3>
            <button onclick="closeModal('modal-interests')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="modal-body">
            <p class="text-sm text-gray-500">Sélectionnez vos domaines d'intérêt pour le networking.</p>
            <div class="flex flex-wrap gap-2 pt-1">
                @php $selectedIds = $userInterests->pluck('id')->toArray(); @endphp
                @foreach ($allInterests as $interest)
                <button type="button"
                        data-id="{{ $interest->id }}"
                        onclick="toggleInterest(this)"
                        class="interest-chip px-3 py-2 rounded-full text-sm font-medium border-2 transition
                               {{ in_array($interest->id, $selectedIds) ? 'border-teal-500 bg-teal-50 text-teal-700' : 'border-gray-200 bg-white text-gray-600 hover:border-teal-300' }}">
                    {{ $interest->icon }} {{ $interest->name }}
                </button>
                @endforeach
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeModal('modal-interests')" class="btn-ghost">Annuler</button>
            <button onclick="saveInterests()" id="btn-save-interests" class="btn-primary">Enregistrer</button>
        </div>
    </div>
</div>

@endif {{-- isOwnProfile --}}

@endsection

@push('scripts')
<script>
    // ── Modal helpers ──
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.modal-overlay:not(.hidden)').forEach(m => { m.classList.add('hidden'); document.body.style.overflow = ''; }); });
    document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click', e => { if (e.target === m) { m.classList.add('hidden'); document.body.style.overflow = ''; } }));

    async function apiFetch(url, method, body) {
        const opts = { method, credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } };
        if (body instanceof FormData) { opts.body = body; }
        else { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
        const res = await fetch(url, opts);
        if (!res.ok) { const e = await res.json(); throw new Error(Object.values(e.errors || {}).flat().join('\n') || e.message || 'Erreur'); }
        return res.json();
    }

    function setBtnLoading(id, loading) {
        const btn = document.getElementById(id);
        if (!btn) return;
        btn.disabled = loading;
        btn.innerHTML = loading ? '<i class="fas fa-spinner fa-spin mr-2"></i>Enregistrement…' : 'Enregistrer';
    }

    // ── Avatar upload ──
    @if ($isOwnProfile)
    document.getElementById('avatarInput').addEventListener('change', async function () {
        if (!this.files[0]) return;
        const fd = new FormData();
        fd.append('avatar', this.files[0]);
        try {
            const data = await apiFetch('/api/profile/avatar', 'POST', fd);
            const container = document.getElementById('avatarImg');
            if (container.tagName === 'IMG') {
                container.src = data.avatar_url;
            } else {
                const img = document.createElement('img');
                img.id = 'avatarImg';
                img.src = data.avatar_url;
                img.className = 'w-20 h-20 rounded-full object-cover border-4 border-white';
                container.replaceWith(img);
            }
            toast('Photo mise à jour !', 'success');
        } catch (e) { toast(e.message, 'error'); }
    });

    // ── Save basic info ──
    async function saveBasic() {
        setBtnLoading('btn-save-basic', true);
        try {
            await apiFetch('/api/profile/basic', 'PUT', {
                first_name:  document.getElementById('b_first_name').value,
                last_name:   document.getElementById('b_last_name').value,
                gender:      document.getElementById('b_gender').value || null,
                birthday:    document.getElementById('b_birthday').value || null,
                city_living: document.getElementById('b_city_living').value || null,
                city_birth:  document.getElementById('b_city_birth').value || null,
            });
            closeModal('modal-basic');
            toast('Informations mises à jour !', 'success');
            setTimeout(() => location.reload(), 800);
        } catch (e) { toast(e.message, 'error'); }
        finally { setBtnLoading('btn-save-basic', false); }
    }

    // ── Save bio ──
    async function saveBio() {
        setBtnLoading('btn-save-bio', true);
        try {
            await apiFetch('/api/profile/bio', 'PUT', {
                motto:            document.getElementById('bio_motto').value || null,
                bio:              document.getElementById('bio_bio').value || null,
                open_to_network:  document.getElementById('bio_open').checked,
            });
            closeModal('modal-bio');
            toast('Bio mise à jour !', 'success');
            setTimeout(() => location.reload(), 800);
        } catch (e) { toast(e.message, 'error'); }
        finally { setBtnLoading('btn-save-bio', false); }
    }

    // ── Save professional ──
    async function saveProfessional() {
        setBtnLoading('btn-save-pro', true);
        try {
            await apiFetch('/api/profile/professional', 'PUT', {
                job_title:        document.getElementById('pro_job_title').value || null,
                sector:           document.getElementById('pro_sector').value || null,
                experience_level: document.getElementById('pro_experience').value || null,
                looking_for:      document.getElementById('pro_looking_for').value || null,
                services_offered: document.getElementById('pro_services').value || null,
            });
            closeModal('modal-professional');
            toast('Profil mis à jour !', 'success');
            setTimeout(() => location.reload(), 800);
        } catch (e) { toast(e.message, 'error'); }
        finally { setBtnLoading('btn-save-pro', false); }
    }

    // ── Interests ──
    function toggleInterest(btn) {
        const active = btn.classList.contains('border-teal-500');
        btn.classList.toggle('border-teal-500', !active);
        btn.classList.toggle('bg-teal-50', !active);
        btn.classList.toggle('text-teal-700', !active);
        btn.classList.toggle('border-gray-200', active);
        btn.classList.toggle('bg-white', active);
        btn.classList.toggle('text-gray-600', active);
    }

    async function saveInterests() {
        setBtnLoading('btn-save-interests', true);
        const ids = [...document.querySelectorAll('.interest-chip.border-teal-500')].map(b => parseInt(b.dataset.id));
        try {
            await apiFetch('/api/profile/interests', 'POST', { interests: ids });
            closeModal('modal-interests');
            toast('Intérêts mis à jour !', 'success');
            setTimeout(() => location.reload(), 800);
        } catch (e) { toast(e.message, 'error'); }
        finally { setBtnLoading('btn-save-interests', false); }
    }

    // ── Motto char counter ──
    const mottoInput = document.getElementById('bio_motto');
    const mottoCount = document.getElementById('mottoCount');
    if (mottoInput && mottoCount) {
        mottoInput.addEventListener('input', () => mottoCount.textContent = mottoInput.value.length);
    }
    @endif

    // ── Connection actions (other profiles) ──
    @if (!$isOwnProfile)
    async function connect(userId) {
        const btn = document.getElementById('connect-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi…';
        try {
            await apiFetch('/api/connections', 'POST', { receiver_id: userId });
            btn.outerHTML = '<button disabled class="w-full py-2.5 rounded-xl bg-yellow-50 text-yellow-700 font-semibold text-sm cursor-not-allowed flex items-center justify-center gap-2" style="border:1px solid #fde68a;"><i class="fas fa-clock"></i>En attente</button>';
            toast('Demande envoyée !', 'success');
        } catch (e) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-user-plus"></i>Se connecter'; toast(e.message || 'Erreur', 'error'); }
    }
    async function acceptRequest(id) {
        const a = document.getElementById(`accept-btn-${id}`), r = document.getElementById(`reject-btn-${id}`);
        a.disabled = r.disabled = true; a.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        try {
            await apiFetch(`/api/connections/${id}/accept`, 'POST', {});
            document.getElementById('connectionAction').innerHTML = '<button disabled class="w-full py-2.5 rounded-xl bg-gray-100 text-gray-500 font-semibold text-sm cursor-not-allowed flex items-center justify-center gap-2"><i class="fas fa-check"></i>Connecté</button>';
            toast('Connexion acceptée !', 'success');
        } catch (e) { a.disabled = r.disabled = false; a.innerHTML = '<i class="fas fa-check text-xs"></i>Accepter'; toast('Erreur', 'error'); }
    }
    async function rejectRequest(id, userId) {
        const a = document.getElementById(`accept-btn-${id}`), r = document.getElementById(`reject-btn-${id}`);
        a.disabled = r.disabled = true; r.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        try {
            await apiFetch(`/api/connections/${id}/reject`, 'POST', {});
            document.getElementById('connectionAction').innerHTML = `<button onclick="connect(${userId})" id="connect-btn" class="w-full py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl transition text-sm flex items-center justify-center gap-2 shadow-sm"><i class="fas fa-user-plus"></i>Se connecter</button>`;
            toast('Demande refusée', 'info');
        } catch (e) { a.disabled = r.disabled = false; r.innerHTML = '<i class="fas fa-times text-xs"></i>Refuser'; toast('Erreur', 'error'); }
    }
    @endif

    function toast(msg, type = 'success') {
        const c = { success: 'bg-green-500', error: 'bg-red-500', info: 'bg-blue-500' };
        const i = { success: 'fa-check-circle', error: 'fa-exclamation-circle', info: 'fa-info-circle' };
        const t = document.createElement('div');
        t.className = `${c[type]} text-white px-6 py-3 rounded-lg shadow-2xl flex items-center space-x-3 transform transition-all`;
        t.style.transform = 'translateX(400px)';
        t.innerHTML = `<i class="fas ${i[type]}"></i><span class="font-medium">${msg}</span>`;
        document.getElementById('toastContainer').appendChild(t);
        setTimeout(() => t.style.transform = 'translateX(0)', 10);
        setTimeout(() => t.style.transform = 'translateX(400px)', 3000);
        setTimeout(() => t.remove(), 3300);
    }
</script>
@endpush
