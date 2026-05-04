@extends('layouts.dashboard')

@section('title', $user['first_name'] . ' ' . $user['last_name'])

@push('styles')
<style>
    .avatar-ring { box-shadow: 0 0 0 4px white, 0 4px 20px rgba(0,0,0,.15); }
    .section-card { @apply bg-white rounded-xl shadow-sm p-6 mb-5; }
    .edit-btn { @apply ml-2 text-gray-300 hover:text-teal-500 transition cursor-pointer text-sm; }
    .field-row { @apply flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0; }
    .field-icon { @apply w-8 h-8 bg-teal-50 rounded-lg flex items-center justify-center flex-shrink-0; }
    .chip { @apply inline-flex items-center gap-1.5 px-3 py-1.5 bg-teal-50 text-teal-700 rounded-full text-sm font-medium; }
    .modal-overlay { @apply fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4; }
    .modal-box { @apply bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto; }
    .modal-header { @apply flex items-center justify-between px-6 py-4 border-b border-gray-100; }
    .modal-body { @apply px-6 py-5 space-y-4; }
    .modal-footer { @apply px-6 py-4 border-t border-gray-100 flex justify-end gap-3; }
    .inp { @apply w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent; }
    .lbl { @apply block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5; }
    .btn-primary { @apply px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg transition text-sm disabled:opacity-50; }
    .btn-ghost { @apply px-5 py-2.5 text-gray-600 hover:bg-gray-100 font-medium rounded-lg transition text-sm; }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- ── COMPLETION BANNER (own profile only) ── --}}
    @if ($isOwnProfile && $completion < 100)
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="relative w-14 h-14 flex-shrink-0">
                <svg class="w-14 h-14 -rotate-90" viewBox="0 0 56 56">
                    <circle cx="28" cy="28" r="23" fill="none" stroke="#fed7aa" stroke-width="5"/>
                    <circle cx="28" cy="28" r="23" fill="none" stroke="#f97316" stroke-width="5"
                        stroke-dasharray="{{ round(2 * pi() * 23, 2) }}"
                        stroke-dashoffset="{{ round(2 * pi() * 23 * (1 - $completion / 100), 2) }}"
                        stroke-linecap="round"/>
                </svg>
                <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-orange-600">{{ $completion }}%</span>
            </div>
            <div>
                <p class="font-semibold text-gray-900">Complétez votre profil</p>
                <p class="text-sm text-gray-500">Plus votre profil est complet, plus vous attirez des connexions.</p>
            </div>
        </div>
        <button onclick="openModal('modal-missing')" class="flex-shrink-0 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-lg transition">
            Compléter
        </button>
    </div>
    @endif

    {{-- ── PROFILE HEADER ── --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-5">
        <div class="h-36 bg-gradient-to-r from-teal-500 via-teal-600 to-teal-700 relative">
            @if ($isOwnProfile)
            <div class="absolute bottom-3 right-4 text-white/70 text-xs">
                <i class="fas fa-image mr-1"></i>Cover photo bientôt
            </div>
            @endif
        </div>

        <div class="px-6 pb-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 -mt-14 mb-4">

                {{-- Avatar --}}
                <div class="relative w-28 h-28 flex-shrink-0">
                    @if ($profile?->avatar_url)
                        <img id="avatarImg" src="{{ $profile->avatar_url }}" alt="Avatar"
                             class="w-28 h-28 rounded-full object-cover avatar-ring">
                    @else
                        <div id="avatarImg" class="w-28 h-28 bg-gradient-to-br from-teal-500 to-teal-600 rounded-full flex items-center justify-center avatar-ring">
                            <span class="text-white font-bold text-3xl">
                                {{ strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) }}
                            </span>
                        </div>
                    @endif

                    @if ($isOwnProfile)
                    <label for="avatarInput"
                           class="absolute bottom-1 right-1 w-8 h-8 bg-teal-600 hover:bg-teal-700 rounded-full flex items-center justify-center cursor-pointer shadow-lg transition"
                           title="Changer la photo">
                        <i class="fas fa-camera text-white text-xs"></i>
                    </label>
                    <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp" class="hidden">
                    @endif
                </div>

                {{-- Actions --}}
                <div id="connectionAction" class="sm:mb-2">
                    @if ($isOwnProfile)
                        <span class="inline-flex items-center px-5 py-2.5 bg-teal-50 text-teal-700 font-semibold rounded-lg border border-teal-200 text-sm">
                            <i class="fas fa-user mr-2"></i>Votre profil
                        </span>
                    @elseif ($user['connection_status'] === 'accepted')
                        <button disabled class="px-6 py-2.5 bg-gray-100 text-gray-500 font-semibold rounded-lg cursor-not-allowed text-sm">
                            <i class="fas fa-check mr-2"></i>Connecté
                        </button>
                    @elseif ($user['connection_status'] === 'pending' && $user['i_am_sender'])
                        <button disabled class="px-6 py-2.5 bg-yellow-100 text-yellow-700 font-semibold rounded-lg cursor-not-allowed text-sm">
                            <i class="fas fa-clock mr-2"></i>En attente
                        </button>
                    @elseif ($user['connection_status'] === 'pending' && $user['i_am_receiver'])
                        <div class="flex gap-2">
                            <button onclick="acceptRequest({{ $user['connection_id'] }})"
                                    id="accept-btn-{{ $user['connection_id'] }}"
                                    class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg transition text-sm">
                                <i class="fas fa-check mr-1"></i>Accepter
                            </button>
                            <button onclick="rejectRequest({{ $user['connection_id'] }}, {{ $user['id'] }})"
                                    id="reject-btn-{{ $user['connection_id'] }}"
                                    class="px-5 py-2.5 bg-red-100 hover:bg-red-200 text-red-700 font-semibold rounded-lg transition text-sm">
                                <i class="fas fa-times mr-1"></i>Refuser
                            </button>
                        </div>
                    @else
                        <button onclick="connect({{ $user['id'] }})" id="connect-btn"
                                class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg transition shadow-sm text-sm">
                            <i class="fas fa-user-plus mr-2"></i>Se connecter
                        </button>
                    @endif
                </div>
            </div>

            {{-- Name & headline --}}
            <div class="flex items-start gap-2">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ $user['first_name'] }} {{ $user['last_name'] }}
                    </h1>
                    @if ($profile?->job_title || $profile?->sector)
                    <p class="text-gray-600 mt-0.5 text-sm font-medium">
                        {{ $profile?->job_title }}
                        @if ($profile?->job_title && $profile?->sector) · @endif
                        {{ $profile?->sector }}
                    </p>
                    @endif
                </div>
                @if ($isOwnProfile)
                <button onclick="openModal('modal-basic')" class="edit-btn mt-1"><i class="fas fa-pen"></i></button>
                @endif
            </div>

            {{-- Meta row --}}
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2">
                @if ($user['city_living'])
                <span class="text-sm text-gray-500"><i class="fas fa-map-marker-alt mr-1.5 text-teal-500"></i>{{ $user['city_living'] }}</span>
                @endif
                @if ($user['member_since'])
                <span class="text-sm text-gray-400"><i class="fas fa-calendar mr-1.5"></i>Membre depuis {{ $user['member_since'] }}</span>
                @endif
                @if ($profile?->open_to_network)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>Open to network
                </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── CONTENT GRID ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── LEFT COLUMN ── --}}
        <div class="lg:col-span-1 space-y-5">

            {{-- Personal Info --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-user text-teal-500 text-sm"></i>Informations
                    </h2>
                    @if ($isOwnProfile)
                    <button onclick="openModal('modal-basic')" class="text-gray-300 hover:text-teal-500 transition text-sm">
                        <i class="fas fa-pen"></i>
                    </button>
                    @endif
                </div>

                <div class="space-y-0">
                    <div class="field-row">
                        <div class="field-icon"><i class="fas fa-envelope text-teal-500 text-xs"></i></div>
                        <div><p class="text-xs text-gray-400">Email</p><p class="text-sm font-medium text-gray-700">{{ $user['email'] }}</p></div>
                    </div>
                    @if ($user['city_living'])
                    <div class="field-row">
                        <div class="field-icon"><i class="fas fa-map-marker-alt text-teal-500 text-xs"></i></div>
                        <div><p class="text-xs text-gray-400">Ville</p><p class="text-sm font-medium text-gray-700">{{ $user['city_living'] }}</p></div>
                    </div>
                    @elseif ($isOwnProfile)
                    <div class="field-row opacity-50">
                        <div class="field-icon"><i class="fas fa-map-marker-alt text-gray-400 text-xs"></i></div>
                        <div><p class="text-xs text-gray-400">Ville</p><p class="text-sm italic text-gray-400">Non renseigné</p></div>
                    </div>
                    @endif
                    @if ($user['city_birth'])
                    <div class="field-row">
                        <div class="field-icon"><i class="fas fa-baby text-teal-500 text-xs"></i></div>
                        <div><p class="text-xs text-gray-400">Né(e) à</p><p class="text-sm font-medium text-gray-700">{{ $user['city_birth'] }}</p></div>
                    </div>
                    @endif
                    @if ($user['gender'])
                    <div class="field-row">
                        <div class="field-icon"><i class="fas fa-venus-mars text-teal-500 text-xs"></i></div>
                        <div><p class="text-xs text-gray-400">Genre</p><p class="text-sm font-medium text-gray-700 capitalize">{{ $user['gender'] }}</p></div>
                    </div>
                    @endif
                    @if ($user['birthday'])
                    <div class="field-row">
                        <div class="field-icon"><i class="fas fa-birthday-cake text-teal-500 text-xs"></i></div>
                        <div><p class="text-xs text-gray-400">Date de naissance</p><p class="text-sm font-medium text-gray-700">{{ \Carbon\Carbon::parse($user['birthday'])->format('d F Y') }}</p></div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Company --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2 mb-4">
                    <i class="fas fa-building text-teal-500 text-sm"></i>Entreprise
                </h2>
                @if ($user['company'])
                <div class="space-y-0">
                    <div class="field-row">
                        <div class="field-icon"><i class="fas fa-tag text-teal-500 text-xs"></i></div>
                        <div><p class="text-xs text-gray-400">Nom</p><p class="text-sm font-medium text-gray-700">{{ $user['company']['name'] }}</p></div>
                    </div>
                    @if ($user['company']['sector'])
                    <div class="field-row">
                        <div class="field-icon"><i class="fas fa-industry text-teal-500 text-xs"></i></div>
                        <div><p class="text-xs text-gray-400">Secteur</p><p class="text-sm font-medium text-gray-700">{{ $user['company']['sector'] }}</p></div>
                    </div>
                    @endif
                    @if ($user['company']['website'])
                    <div class="field-row">
                        <div class="field-icon"><i class="fas fa-globe text-teal-500 text-xs"></i></div>
                        <div><p class="text-xs text-gray-400">Site web</p>
                            <a href="{{ $user['company']['website'] }}" target="_blank" rel="noopener"
                               class="text-sm font-medium text-teal-600 hover:underline truncate block max-w-[160px]">
                                {{ $user['company']['website'] }}
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
                @else
                <div class="text-center py-6">
                    <i class="fas fa-building text-gray-200 text-3xl mb-2"></i>
                    <p class="text-sm text-gray-400">Aucune entreprise</p>
                    @if ($isOwnProfile)
                    <a href="{{ route('company.create') }}" class="text-teal-600 text-xs font-semibold hover:underline mt-1 inline-block">
                        + Ajouter une entreprise
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- ── RIGHT COLUMN ── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Bio / Motto --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-quote-left text-teal-500 text-sm"></i>À propos
                    </h2>
                    @if ($isOwnProfile)
                    <button onclick="openModal('modal-bio')" class="text-gray-300 hover:text-teal-500 transition text-sm">
                        <i class="fas fa-pen"></i>
                    </button>
                    @endif
                </div>
                @if ($profile?->bio || $profile?->motto)
                    @if ($profile?->motto)
                    <p class="text-gray-700 italic text-base border-l-4 border-teal-500 pl-4 mb-3">"{{ $profile->motto }}"</p>
                    @endif
                    @if ($profile?->bio)
                    <p class="text-gray-600 text-sm leading-relaxed">{{ $profile->bio }}</p>
                    @endif
                @else
                    <div class="text-center py-6 text-gray-400">
                        <i class="fas fa-pen text-2xl text-gray-200 mb-2"></i>
                        @if ($isOwnProfile)
                        <p class="text-sm">Partagez votre motto ou votre bio</p>
                        <button onclick="openModal('modal-bio')" class="text-teal-600 text-xs font-semibold hover:underline mt-1">+ Ajouter</button>
                        @else
                        <p class="text-sm">Aucune bio</p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Professional --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-briefcase text-teal-500 text-sm"></i>Profil professionnel
                    </h2>
                    @if ($isOwnProfile)
                    <button onclick="openModal('modal-professional')" class="text-gray-300 hover:text-teal-500 transition text-sm">
                        <i class="fas fa-pen"></i>
                    </button>
                    @endif
                </div>

                @if ($profile?->job_title || $profile?->sector || $profile?->experience_level || $profile?->looking_for)
                <div class="space-y-0">
                    @if ($profile?->job_title)
                    <div class="field-row">
                        <div class="field-icon"><i class="fas fa-user-tie text-teal-500 text-xs"></i></div>
                        <div><p class="text-xs text-gray-400">Poste</p><p class="text-sm font-medium text-gray-700">{{ $profile->job_title }}</p></div>
                    </div>
                    @endif
                    @if ($profile?->sector)
                    <div class="field-row">
                        <div class="field-icon"><i class="fas fa-industry text-teal-500 text-xs"></i></div>
                        <div><p class="text-xs text-gray-400">Secteur</p><p class="text-sm font-medium text-gray-700">{{ $profile->sector }}</p></div>
                    </div>
                    @endif
                    @if ($profile?->experience_level)
                    @php $expLabels = ['junior' => 'Junior (0-2 ans)', 'mid' => 'Intermédiaire (2-5 ans)', 'senior' => 'Senior (5-10 ans)', 'expert' => 'Expert (10+ ans)']; @endphp
                    <div class="field-row">
                        <div class="field-icon"><i class="fas fa-chart-line text-teal-500 text-xs"></i></div>
                        <div><p class="text-xs text-gray-400">Expérience</p><p class="text-sm font-medium text-gray-700">{{ $expLabels[$profile->experience_level] ?? $profile->experience_level }}</p></div>
                    </div>
                    @endif
                    @if ($profile?->looking_for)
                    <div class="pt-3">
                        <p class="text-xs text-gray-400 mb-1.5">Recherche</p>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $profile->looking_for }}</p>
                    </div>
                    @endif
                    @if ($profile?->services_offered)
                    <div class="pt-3">
                        <p class="text-xs text-gray-400 mb-1.5">Services proposés</p>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $profile->services_offered }}</p>
                    </div>
                    @endif
                </div>
                @else
                <div class="text-center py-6 text-gray-400">
                    <i class="fas fa-briefcase text-2xl text-gray-200 mb-2"></i>
                    @if ($isOwnProfile)
                    <p class="text-sm">Ajoutez vos informations professionnelles</p>
                    <button onclick="openModal('modal-professional')" class="text-teal-600 text-xs font-semibold hover:underline mt-1">+ Ajouter</button>
                    @else
                    <p class="text-sm">Aucune information professionnelle</p>
                    @endif
                </div>
                @endif
            </div>

            {{-- Interests --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-star text-teal-500 text-sm"></i>Centres d'intérêt
                    </h2>
                    @if ($isOwnProfile)
                    <button onclick="openModal('modal-interests')" class="text-gray-300 hover:text-teal-500 transition text-sm">
                        <i class="fas fa-pen"></i>
                    </button>
                    @endif
                </div>

                @if ($userInterests->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach ($userInterests as $interest)
                    <span class="chip">{{ $interest->icon }} {{ $interest->name }}</span>
                    @endforeach
                </div>
                @else
                <div class="text-center py-6 text-gray-400">
                    <i class="fas fa-star text-2xl text-gray-200 mb-2"></i>
                    @if ($isOwnProfile)
                    <p class="text-sm">Ajoutez vos centres d'intérêt</p>
                    <button onclick="openModal('modal-interests')" class="text-teal-600 text-xs font-semibold hover:underline mt-1">+ Ajouter</button>
                    @else
                    <p class="text-sm">Aucun centre d'intérêt</p>
                    @endif
                </div>
                @endif
            </div>

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

    // ── Completion badge ──
    function updateCompletion(pct) {
        const circle = document.querySelector('circle:last-child');
        const label  = document.querySelector('svg + span, svg ~ span');
        if (!circle) return;
        const r = 23, circ = 2 * Math.PI * r;
        circle.style.strokeDashoffset = circ * (1 - pct / 100);
        document.querySelectorAll('[data-completion]').forEach(el => el.textContent = pct + '%');
    }

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
                img.className = 'w-28 h-28 rounded-full object-cover avatar-ring';
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
            btn.outerHTML = '<button disabled class="px-6 py-2.5 bg-yellow-100 text-yellow-700 font-semibold rounded-lg cursor-not-allowed text-sm"><i class="fas fa-clock mr-2"></i>En attente</button>';
            toast('Demande envoyée !', 'success');
        } catch (e) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-user-plus mr-2"></i>Se connecter'; toast(e.message || 'Erreur', 'error'); }
    }
    async function acceptRequest(id) {
        const a = document.getElementById(`accept-btn-${id}`), r = document.getElementById(`reject-btn-${id}`);
        a.disabled = r.disabled = true; a.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>';
        try {
            await apiFetch(`/api/connections/${id}/accept`, 'POST', {});
            document.getElementById('connectionAction').innerHTML = '<button disabled class="px-6 py-2.5 bg-gray-100 text-gray-500 font-semibold rounded-lg cursor-not-allowed text-sm"><i class="fas fa-check mr-2"></i>Connecté</button>';
            toast('Connexion acceptée !', 'success');
        } catch (e) { a.disabled = r.disabled = false; a.innerHTML = '<i class="fas fa-check mr-1"></i>Accepter'; toast('Erreur', 'error'); }
    }
    async function rejectRequest(id, userId) {
        const a = document.getElementById(`accept-btn-${id}`), r = document.getElementById(`reject-btn-${id}`);
        a.disabled = r.disabled = true; r.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>';
        try {
            await apiFetch(`/api/connections/${id}/reject`, 'POST', {});
            document.getElementById('connectionAction').innerHTML = `<button onclick="connect(${userId})" id="connect-btn" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg transition text-sm"><i class="fas fa-user-plus mr-2"></i>Se connecter</button>`;
            toast('Demande refusée', 'info');
        } catch (e) { a.disabled = r.disabled = false; r.innerHTML = '<i class="fas fa-times mr-1"></i>Refuser'; toast('Erreur', 'error'); }
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
