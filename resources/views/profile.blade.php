@extends('layouts.app2')

@section('title', $user['first_name'] . ' ' . $user['last_name'] . ' — LeadXchange')

{{-- Écran « Mon profil » — design lx2 (profil personnel). Les profils des autres membres sont dans profile/member.blade.php. --}}

@php
    $me          = auth()->user();
    $fullName    = trim($user['first_name'] . ' ' . $user['last_name']);
    $initials    = mb_strtoupper(mb_substr($user['first_name'], 0, 1) . mb_substr($user['last_name'], 0, 1));
    $roleLine    = collect([$profile?->job_title, $user['company']['name'] ?? null])->filter()->implode(' · ');
    $vStatus     = $profile?->presentation_video_status;
    $vPath       = $profile?->presentation_video;
    $vUrl        = $profile?->presentation_video_url;
    $planKey     = 'basic';
    if (($user['ambassador_status'] ?? '') === 'approved') {
        $planKey = 'ambassadeur';
    } elseif (($user['consul_status'] ?? '') === 'approved') {
        $planKey = 'consul';
    } elseif (($user['plan']['name'] ?? '') === 'premium') {
        $planKey = 'premium';
    }
    $planLabels  = ['basic' => 'Basic', 'premium' => 'Premium', 'consul' => 'Consul', 'ambassadeur' => 'Ambassadeur'];
    $planLabel   = $planLabels[$planKey] ?? ucfirst($planKey);
    $badgeKey    = $user['badge']['level'] ?? 'neutre';
    $badgeLabel  = $user['badge']['label'] ?? 'Neutre';
    $expLabels   = ['junior' => 'Junior (0-2 ans)', 'mid' => 'Intermédiaire (2-5 ans)', 'senior' => 'Senior (5-10 ans)', 'expert' => 'Expert (10+ ans)'];
    $currentLookingFor      = $profile?->looking_for      ?? [];
    $currentServicesOffered = $profile?->services_offered ?? [];
    $marketAddressed = $profile?->market_addressed_id ? $markets->firstWhere('id', $profile->market_addressed_id) : null;
    $marketTarget    = $profile?->market_target_id    ? $markets->firstWhere('id', $profile->market_target_id)    : null;
    $selectedInterestIds = $userInterests->pluck('id')->toArray();
    $pointsClass = isset($pointsBalance) ? ($pointsBalance < 0 ? 'b-hot' : ($pointsBalance === 0 ? 'b-warm' : 'b-ok')) : 'b-muted';
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
        'phone'       => 'modal-basic',
    ];
@endphp

@section('content')
<x-lx2-header title="Mon profil" sub="Vos informations, visibles par les membres de votre réseau" />

@if (! $me->hasVerifiedEmail())
<div class="lx2-flash b-warm" role="note" style="margin-bottom:18px">
    <span>
        <b>Confirmez votre adresse email pour accéder au site.</b>
        Un lien de confirmation a été envoyé à {{ $me->email }}. Vérifiez aussi vos spams.
        @if (session('success'))<br>✓ {{ session('success') }}@endif
    </span>
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm">Renvoyer l'email</button>
    </form>
</div>
@endif

<div class="two-col" style="margin-top:0">

    {{-- ── Colonne gauche ── --}}
    <aside class="aside-sticky">

        <div class="card pcard">
            <div style="position:relative">
                @if ($profile?->avatar_url)
                    <img id="avatarImg" class="av" src="{{ $profile->avatar_url }}" alt="" style="width:96px;height:96px">
                @else
                    <span id="avatarImg" class="av-fb" style="width:96px;height:96px;font-size:28px">{{ $initials }}</span>
                @endif
                <label for="avatarInput" class="icon-btn" title="Changer la photo" aria-label="Changer la photo"
                       style="position:absolute;right:-4px;bottom:-4px;width:30px;height:30px;cursor:pointer">
                    <x-lx2-icon name="image-plus" />
                </label>
                <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp" class="hidden">
            </div>

            <h2>{{ $fullName }}</h2>
            <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;justify-content:center">
                <span class="badge b-muted">{{ $badgeLabel }}</span>
                @if ($profile?->open_to_network)<span class="badge b-ok">Ouvert au réseau</span>@endif
            </div>
            @if ($roleLine)<div class="line">{{ $roleLine }}</div>@endif

            <div class="contacts">
                @if ($user['email'])<span><x-lx2-icon name="mail" />{{ $user['email'] }}</span>@endif
                @if ($user['city']['name'] ?? null)<span><x-lx2-icon name="map-pin" />{{ $user['city']['name'] }}</span>@endif
                @if ($user['member_since'])<span><x-lx2-icon name="clock" />Membre depuis {{ \Carbon\Carbon::parse('1 ' . $user['member_since'])->locale('fr')->isoFormat('MMMM YYYY') }}</span>@endif
            </div>

            <button type="button" class="btn btn-primary btn-block" style="margin-top:8px" onclick="lx2Dialog('modal-basic')">
                <x-lx2-icon name="settings" />Modifier le profil
            </button>

            @if (isset($pointsBalance))
            <a class="btn btn-outline btn-block" href="{{ route('points.index') }}">
                <x-lx2-icon name="wallet" />Mes points
                <span class="badge {{ $pointsClass }}" style="margin-left:auto">{{ $pointsBalance > 0 ? '+' : '' }}{{ $pointsBalance }} pts</span>
            </a>
            @endif

            {{-- Progression de rôle --}}
            @if ($me->isAmbassador())
                <span class="badge b-warm" style="height:28px"><x-lx2-icon name="star" />Ambassadeur ✓</span>
            @elseif ($me->isConsul())
                <span class="badge b-ok" style="height:28px"><x-lx2-icon name="shield" />Consul ✓</span>
                @if ($me->hasPendingAmbassadorRequest())
                    <span class="badge b-warm" style="height:28px">Demande Ambassadeur en attente…</span>
                @else
                    <form method="POST" action="{{ route('consul.request') }}" style="width:100%">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-block"><x-lx2-icon name="star" />Demander le rôle Ambassadeur</button>
                    </form>
                @endif
            @elseif ($me->hasPendingConsulPromotion())
                <span class="badge b-soft" style="height:28px">Demande Consul en attente…</span>
            @elseif ($me->hasPaidPlan())
                <form method="POST" action="{{ route('consul.request-promote') }}" style="width:100%">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-block"><x-lx2-icon name="shield" />Demander le rôle Consul</button>
                </form>
            @endif
        </div>

        {{-- Complétion (si profil incomplet) --}}
        @if ($completion < 100)
        <div class="card card-pad">
            <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:8px">
                <span style="font-size:13px;font-weight:600">Complétion du profil</span>
                <span style="font-size:15px;font-weight:600;color:var(--primary)">{{ $completion }}%</span>
            </div>
            <div style="height:6px;border-radius:999px;background:var(--muted);overflow:hidden">
                <div style="height:100%;width:{{ $completion }}%;background:var(--primary)"></div>
            </div>
            <ul style="list-style:none;padding:0;margin:12px 0 0;display:flex;flex-direction:column;gap:6px">
                @foreach ($missing as $item)
                <li class="help" style="margin:0">○ {{ $item['label'] }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn btn-primary btn-block" style="margin-top:12px" onclick="lx2Dialog('modal-missing')">Compléter le profil</button>
        </div>
        @endif

        {{-- Statistiques --}}
        <div class="card card-pad">
            <div class="help" style="margin:0 0 10px;text-transform:uppercase;letter-spacing:.04em;font-weight:600">Statistiques</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div><div style="font-size:20px;font-weight:600">0</div><div class="help" style="margin:0">Échanges</div></div>
                <div><div style="font-size:20px;font-weight:600">0</div><div class="help" style="margin:0">Leads reçus</div></div>
                <div><div style="font-size:20px;font-weight:600">0</div><div class="help" style="margin:0">Connexions</div></div>
                <div><div style="font-size:20px;font-weight:600">4.8</div><div class="help" style="margin:0">Score</div></div>
            </div>
        </div>

        {{-- Plan et badge --}}
        <div class="card" style="overflow:hidden">
            <div style="background:var(--muted);position:relative">
                <img src="{{ asset('images/plans/' . $planKey . '.jpg') }}" alt="{{ $planLabel }}"
                     onerror="this.closest('div').style.display='none'"
                     style="width:100%;height:112px;object-fit:contain;padding:8px 16px">
                <span class="help" style="position:absolute;top:8px;left:12px;margin:0;text-transform:uppercase;letter-spacing:.06em">Plan</span>
            </div>
            <div style="border-top:1px solid var(--border);padding:12px 16px;display:flex;align-items:center;gap:12px">
                <img src="{{ asset('images/badges/' . $badgeKey . '.jpg') }}" alt="{{ $badgeLabel }}"
                     onerror="this.style.display='none'" style="width:56px;height:56px;object-fit:contain;flex:none">
                <div>
                    <div class="help" style="margin:0;text-transform:uppercase;letter-spacing:.06em">Badge score</div>
                    <div style="font-size:14px;font-weight:600;margin-top:2px">{{ $badgeLabel }}</div>
                    <div class="help" style="margin:0">{{ $user['balance'] ?? 0 }} points</div>
                </div>
            </div>
        </div>

    </aside>

    {{-- ── Colonne principale ── --}}
    <div style="display:flex;flex-direction:column;gap:18px;min-width:0">

        {{-- À propos --}}
        <div class="card card-pad">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px">
                <h3 style="margin:0;font-size:15px;font-weight:600">À propos de moi</h3>
                <button type="button" class="btn btn-ghost btn-sm" onclick="lx2Dialog('modal-bio')">Modifier</button>
            </div>
            @if ($profile?->motto)<p class="prose" style="margin:0 0 8px;font-style:italic">« {{ $profile->motto }} »</p>@endif
            @if ($profile?->bio)
                <p class="prose" style="margin:0;white-space:pre-line">{{ $profile->bio }}</p>
            @elseif (! $profile?->motto)
                <p class="help" style="margin:0">Partagez votre motto ou votre bio.
                    <a class="link" href="#" onclick="event.preventDefault();lx2Dialog('modal-bio')">+ Ajouter</a></p>
            @endif
        </div>

        {{-- Profil professionnel --}}
        <div class="card card-pad tags-block">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px">
                <h3 style="margin:0;font-size:15px;font-weight:600">Profil professionnel</h3>
                <button type="button" class="btn btn-ghost btn-sm" onclick="lx2Dialog('modal-professional')">Modifier</button>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:22px">
                <div><h4>Poste</h4><div class="val">{{ $profile?->job_title ?: '—' }}</div></div>
                <div><h4>Secteur d'activité</h4><div class="val">{{ $profile?->sector ?: '—' }}</div></div>
                <div><h4>Expérience</h4><div class="val">{{ $expLabels[$profile?->experience_level] ?? '—' }}</div></div>
                <div><h4>Marché que j'adresse</h4><div class="val">{{ $marketAddressed?->name ?? '—' }}</div></div>
                <div><h4>Marché que je souhaite développer</h4><div class="val">{{ $marketTarget?->name ?? '—' }}</div></div>
                <div style="grid-column:1/-1"><h4>Les contacts que je recherche</h4>
                    @php $wanted = $sectors->whereIn('id', $currentLookingFor); @endphp
                    @if ($wanted->isNotEmpty())
                        <div class="row">@foreach ($wanted as $s)<span class="badge b-outline">{{ $s->name }}</span>@endforeach</div>
                    @else <div class="val">—</div> @endif
                </div>
                <div style="grid-column:1/-1"><h4>Les contacts que je peux proposer</h4>
                    @php $offered = $sectors->whereIn('id', $currentServicesOffered); @endphp
                    @if ($offered->isNotEmpty())
                        <div class="row">@foreach ($offered as $s)<span class="badge b-muted">{{ $s->name }}</span>@endforeach</div>
                    @else <div class="val">—</div> @endif
                </div>
            </div>
        </div>

        {{-- Centres d'intérêt --}}
        <div class="card card-pad">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px">
                <h3 style="margin:0;font-size:15px;font-weight:600">Centres d'intérêt</h3>
                <button type="button" class="btn btn-ghost btn-sm" onclick="lx2Dialog('modal-interests')">Modifier</button>
            </div>
            @if ($userInterests->isNotEmpty())
                <div class="row" style="display:flex;flex-wrap:wrap;gap:6px">
                    @foreach ($userInterests as $interest)
                        <span class="badge b-soft">{{ $interest->icon }} {{ $interest->name }}</span>
                    @endforeach
                </div>
            @else
                <p class="help" style="margin:0">Aucun centre d'intérêt.
                    <a class="link" href="#" onclick="event.preventDefault();lx2Dialog('modal-interests')">+ Ajouter</a></p>
            @endif
        </div>

        {{-- Vidéo de présentation --}}
        <div class="card card-pad">
            <h3 style="margin:0 0 12px;font-size:15px;font-weight:600">Vidéo de présentation</h3>

            @if ($vPath)
                <div class="video"><video controls preload="metadata" src="{{ $vUrl }}" style="width:100%;height:100%;object-fit:cover;background:#000"></video></div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:12px">
                    @if ($vStatus === 'pending')
                        <span class="badge b-warm">En attente de validation</span>
                    @elseif ($vStatus === 'approved')
                        <span class="badge b-ok">Approuvée · visible sur votre profil</span>
                    @elseif ($vStatus === 'rejected')
                        <span class="badge b-hot">Rejetée</span>
                        @if ($profile->presentation_video_rejection_reason)
                            <span class="help" style="margin:0">{{ $profile->presentation_video_rejection_reason }}</span>
                        @endif
                    @endif
                    <form method="POST" action="{{ route('profile.video.delete') }}" style="margin-left:auto"
                          onsubmit="return confirm('Supprimer la vidéo de présentation ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger-soft btn-sm"><x-lx2-icon name="trash-2" />Supprimer</button>
                    </form>
                </div>
            @else
                <form method="POST" action="{{ route('profile.video.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <label id="videoDropZone"
                           style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;padding:24px;text-align:center;cursor:pointer;border:1.5px dashed var(--border);border-radius:var(--radius);color:var(--muted-fg)">
                        <input type="file" name="video" id="videoInput" accept="video/mp4,video/webm,video/quicktime,video/avi" class="hidden"
                               onchange="previewVideoFile(this)">
                        <div id="videoPlaceholder" style="display:flex;flex-direction:column;align-items:center;gap:6px">
                            <x-lx2-icon name="video" />
                            <span style="font-size:13.5px;font-weight:500">Cliquez pour uploader votre vidéo de présentation</span>
                            <span class="help" style="margin:0">MP4, WebM, MOV — max 100 Mo</span>
                        </div>
                        <div id="videoPreviewName" class="hidden" style="font-size:13.5px;font-weight:600;color:var(--primary)"></div>
                    </label>
                    @error('video') <p class="help" style="color:var(--destructive)">{{ $message }}</p> @enderror
                    <button type="submit" id="videoSubmitBtn" class="btn btn-primary" style="margin-top:12px" disabled>Envoyer pour validation</button>
                </form>
            @endif
        </div>

        {{-- Entreprise --}}
        <div class="card card-pad">
            <h3 style="margin:0 0 10px;font-size:15px;font-weight:600">Entreprise</h3>
            @if ($user['company'])
                <div class="mrow" style="padding:0">
                    <span class="av-fb" style="width:40px;height:40px;border-radius:10px">{{ mb_strtoupper(mb_substr($user['company']['name'], 0, 1)) }}</span>
                    <div class="who">
                        <div class="nm">{{ $user['company']['name'] }}</div>
                        <div class="role">{{ $user['company']['sector']['name'] ?? '' }}</div>
                    </div>
                    @if ($user['company']['website'])
                        <a class="btn btn-outline btn-sm" href="{{ $user['company']['website'] }}" target="_blank" rel="noopener"><x-lx2-icon name="globe" />Site web</a>
                    @endif
                </div>
            @else
                <p class="help" style="margin:0">Aucune entreprise liée.
                    <a class="link" href="{{ route('company.create') }}">+ Ajouter</a></p>
            @endif
        </div>

        {{-- Informations personnelles --}}
        <div class="card card-pad tags-block">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px">
                <h3 style="margin:0;font-size:15px;font-weight:600">Informations</h3>
                <button type="button" class="btn btn-ghost btn-sm" onclick="lx2Dialog('modal-basic')">Modifier</button>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:22px">
                <div><h4>Email</h4><div class="val">{{ $user['email'] }}</div></div>
                <div><h4>Ville actuelle</h4><div class="val">{{ $user['city']['name'] ?? '—' }}</div></div>
                <div><h4>Genre</h4><div class="val">{{ $user['gender'] ? ['male' => 'Homme', 'female' => 'Femme', 'other' => 'Autre'][$user['gender']] ?? $user['gender'] : '—' }}</div></div>
                <div><h4>Date de naissance</h4><div class="val">{{ $user['birthday'] ? \Carbon\Carbon::parse($user['birthday'])->locale('fr')->isoFormat('D MMMM YYYY') : '—' }}</div></div>
                <div><h4>Téléphone</h4><div class="val">{{ $user['phone']['number'] ?? '—' }}</div></div>
            </div>
        </div>

    </div>
</div>

{{-- ════════════════════════════ MODALES ════════════════════════════ --}}

{{-- Compléter le profil --}}
<div class="overlay hidden" id="modal-missing" data-dialog>
    <div class="dialog">
        <div class="dh">
            <div><h3>Compléter votre profil</h3><p>{{ $completion }}% complété</p></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db">
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                @foreach ($missing as $item)
                    @php $target = $modalMap[$item['key']] ?? null; @endphp
                    <button type="button" class="chip" onclick="missingGo(@js($target))">{{ $item['label'] }}</button>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Informations personnelles --}}
<div class="overlay hidden" id="modal-basic" data-dialog>
    <div class="dialog">
        <div class="dh">
            <div><h3>Informations personnelles</h3></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db" style="display:flex;flex-direction:column;gap:14px">
            <div class="fgrid" style="gap:12px">
                <div class="field"><label class="label" for="b_first_name">Prénom</label><input id="b_first_name" type="text" class="input" value="{{ $user['first_name'] }}"></div>
                <div class="field"><label class="label" for="b_last_name">Nom</label><input id="b_last_name" type="text" class="input" value="{{ $user['last_name'] }}"></div>
            </div>
            <div class="field">
                <label class="label" for="b_gender">Genre</label>
                <select id="b_gender" class="select filled">
                    <option value="">— Non renseigné —</option>
                    <option value="male" {{ $user['gender'] === 'male' ? 'selected' : '' }}>Homme</option>
                    <option value="female" {{ $user['gender'] === 'female' ? 'selected' : '' }}>Femme</option>
                    <option value="other" {{ $user['gender'] === 'other' ? 'selected' : '' }}>Autre</option>
                </select>
            </div>
            <div class="field"><label class="label" for="b_birthday">Date de naissance</label><input id="b_birthday" type="date" class="input" value="{{ $user['birthday'] ?? '' }}"></div>
            <div class="field"><label class="label" for="b_phone">Téléphone</label><input id="b_phone" type="tel" class="input" placeholder="+33 6 00 00 00 00" value="{{ $user['phone']['number'] ?? '' }}"></div>
            <div class="field">
                <label class="label" for="b_city_living_search">Ville actuelle</label>
                <div style="position:relative">
                    <input type="text" id="b_city_living_search" autocomplete="off" placeholder="Rechercher une ville…" class="input"
                           value="{{ $user['city']['name'] ?? '' }}"
                           oninput="filterCityDropdown('living', this.value)"
                           onfocus="showCityDropdown('living')"
                           onblur="hideCityDropdown('living')">
                    <input type="hidden" id="b_city_living_id" value="{{ $user['city']['id'] ?? '' }}">
                    <div id="city_living_dropdown" class="pop menu hidden" style="left:0;right:0;top:calc(100% + 4px);max-height:200px;overflow-y:auto">
                        @foreach ($cities as $city)
                        <button type="button" class="city-opt" data-id="{{ $city->id }}" data-name="{{ $city->name }}"
                                onmousedown="pickCity('living', {{ $city->id }}, @js($city->name))">
                            {{ $city->name }}
                            <span class="help" style="margin:0 0 0 4px">{{ $city->country?->name }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="df">
            <button type="button" class="btn btn-outline" data-close>Annuler</button>
            <button type="button" class="btn btn-primary" id="btn-save-basic" onclick="saveBasic()">Enregistrer</button>
        </div>
    </div>
</div>

{{-- À propos --}}
<div class="overlay hidden" id="modal-bio" data-dialog>
    <div class="dialog">
        <div class="dh">
            <div><h3>À propos</h3></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db" style="display:flex;flex-direction:column;gap:14px">
            <div class="field">
                <label class="label" for="bio_motto">Motto <span class="help" style="margin:0">(phrase courte)</span></label>
                <input id="bio_motto" type="text" maxlength="500" class="input" placeholder="Résumez-vous en une phrase…" value="{{ $profile?->motto ?? '' }}">
                <span class="help" style="text-align:right"><span id="mottoCount">{{ strlen($profile?->motto ?? '') }}</span> / 500</span>
            </div>
            <div class="field">
                <label class="label" for="bio_bio">Bio</label>
                <textarea id="bio_bio" rows="5" maxlength="1000" class="textarea" placeholder="Parlez de vous, de votre parcours, de vos ambitions…">{{ $profile?->bio ?? '' }}</textarea>
            </div>
            <label class="check">
                <input type="checkbox" id="bio_open" {{ $profile?->open_to_network ? 'checked' : '' }}>
                <span class="box"></span>
                <span>Ouvert au réseau</span>
            </label>
        </div>
        <div class="df">
            <button type="button" class="btn btn-outline" data-close>Annuler</button>
            <button type="button" class="btn btn-primary" id="btn-save-bio" onclick="saveBio()">Enregistrer</button>
        </div>
    </div>
</div>

{{-- Profil professionnel --}}
<div class="overlay hidden" id="modal-professional" data-dialog>
    <div class="dialog" style="max-width:560px">
        <div class="dh">
            <div><h3>Profil professionnel</h3></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db" style="display:flex;flex-direction:column;gap:14px">
            <div class="field"><label class="label" for="pro_job_title">Intitulé du poste</label><input id="pro_job_title" type="text" class="input" placeholder="ex : Chef de projet digital" value="{{ $profile?->job_title ?? '' }}"></div>
            <div class="field">
                <label class="label" for="pro_sector">Secteur d'activité</label>
                <select id="pro_sector" class="select filled">
                    <option value="">— Sélectionner —</option>
                    @foreach ($sectors as $sector)
                    <option value="{{ $sector->name }}" {{ $profile?->sector === $sector->name ? 'selected' : '' }}>{{ $sector->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="label" for="pro_experience">Niveau d'expérience</label>
                <select id="pro_experience" class="select filled">
                    <option value="">— Sélectionner —</option>
                    @foreach ($expLabels as $key => $label)
                    <option value="{{ $key }}" {{ $profile?->experience_level === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="label" for="pro_market_addressed">Marché que j'adresse actuellement</label>
                <select id="pro_market_addressed" class="select filled">
                    <option value="">— Sélectionner —</option>
                    @foreach ($markets as $market)
                    <option value="{{ $market->id }}" {{ $profile?->market_addressed_id == $market->id ? 'selected' : '' }}>{{ $market->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="label" for="pro_market_target">Marché que je souhaite développer</label>
                <select id="pro_market_target" class="select filled">
                    <option value="">— Sélectionner —</option>
                    @foreach ($markets as $market)
                    <option value="{{ $market->id }}" {{ $profile?->market_target_id == $market->id ? 'selected' : '' }}>{{ $market->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <span class="label">Les contacts que je recherche <span class="help" style="margin:0">(secteurs, plusieurs choix)</span></span>
                <div style="display:flex;flex-wrap:wrap;gap:8px">
                    @foreach ($sectors as $sector)
                    <button type="button" class="chip lf-chip {{ in_array($sector->id, $currentLookingFor) ? 'on' : '' }}" data-value="{{ $sector->id }}" onclick="toggleChip(this)">{{ $sector->name }}</button>
                    @endforeach
                </div>
            </div>
            <div class="field">
                <span class="label">Les contacts que je peux proposer <span class="help" style="margin:0">(secteurs, plusieurs choix)</span></span>
                <div style="display:flex;flex-wrap:wrap;gap:8px">
                    @foreach ($sectors as $sector)
                    <button type="button" class="chip so-chip {{ in_array($sector->id, $currentServicesOffered) ? 'on' : '' }}" data-value="{{ $sector->id }}" onclick="toggleChip(this)">{{ $sector->name }}</button>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="df">
            <button type="button" class="btn btn-outline" data-close>Annuler</button>
            <button type="button" class="btn btn-primary" id="btn-save-pro" onclick="saveProfessional()">Enregistrer</button>
        </div>
    </div>
</div>

{{-- Centres d'intérêt --}}
<div class="overlay hidden" id="modal-interests" data-dialog>
    <div class="dialog">
        <div class="dh">
            <div><h3>Centres d'intérêt</h3><p>Sélectionnez vos domaines d'intérêt pour le networking.</p></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db">
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                @foreach ($allInterests as $interest)
                <button type="button" class="chip interest-chip {{ in_array($interest->id, $selectedInterestIds) ? 'on' : '' }}" data-id="{{ $interest->id }}" onclick="toggleInterest(this)">
                    {{ $interest->icon }} {{ $interest->name }}
                </button>
                @endforeach
            </div>
        </div>
        <div class="df">
            <button type="button" class="btn btn-outline" data-close>Annuler</button>
            <button type="button" class="btn btn-primary" id="btn-save-interests" onclick="saveInterests()">Enregistrer</button>
        </div>
    </div>
</div>

{{-- Popup points (solde ≤ 0), masquable pour la session --}}
@if (isset($pointsBalance) && $pointsBalance <= 0)
<div class="overlay" id="points-popup-overlay" onclick="if(event.target===this)pointsPopupClose()">
    <div class="dialog" style="max-width:380px">
        <div class="dh">
            <div><h3>{{ $pointsBalance < 0 ? 'Solde de points négatif' : 'Vous n\'avez plus de points' }}</h3></div>
            <button type="button" class="x" onclick="pointsPopupClose()" aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db" style="text-align:center">
            <p class="prose" style="margin:0 auto 14px">
                @if ($pointsBalance < 0)
                    Votre solde est de <b style="color:var(--destructive)">{{ $pointsBalance }} point{{ abs($pointsBalance) > 1 ? 's' : '' }}</b>.
                    Rechargez votre compte pour continuer à recevoir des leads de la communauté.
                @else
                    Votre solde est à <b style="color:var(--warm-fg)">0 point</b>.
                    Achetez des points ou envoyez des leads pour en gagner.
                @endif
            </p>
            <div class="badge {{ $pointsBalance < 0 ? 'b-hot' : 'b-warm' }}" style="height:auto;padding:10px 18px;font-size:26px;font-weight:600">{{ $pointsBalance }} pts</div>
        </div>
        <div class="df" style="flex-direction:column">
            <a class="btn btn-primary btn-block" href="{{ route('points.index') }}"><x-lx2-icon name="wallet" />Acheter des points</a>
            <button type="button" class="btn btn-outline btn-block" onclick="pointsPopupClose()">Fermer</button>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    async function apiFetch(url, method, body) {
        const opts = {
            method,
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + (window.API_TOKEN || ''),
                'X-CSRF-TOKEN': window.CSRF || '',
            },
        };
        if (body instanceof FormData) { opts.body = body; }
        else { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
        const res = await fetch(url, opts);
        if (!res.ok) {
            const e = await res.json().catch(() => ({}));
            throw new Error(Object.values(e.errors || {}).flat().join('\n') || e.message || 'Erreur');
        }
        return res.json();
    }

    function setBtnLoading(id, loading) {
        const btn = document.getElementById(id);
        if (!btn) return;
        btn.disabled = loading;
        btn.textContent = loading ? 'Enregistrement…' : 'Enregistrer';
    }

    function missingGo(target) {
        document.getElementById('modal-missing').classList.add('hidden');
        if (target === 'avatar-input') { document.getElementById('avatarInput').click(); return; }
        if (target) lx2Dialog(target);
    }

    // ── Pop-up points : masquable pour la session ──
    function pointsPopupClose() {
        document.getElementById('points-popup-overlay')?.classList.add('hidden');
        try { sessionStorage.setItem('points_popup_dismissed', '1'); } catch (e) {}
    }
    (function () {
        try {
            if (sessionStorage.getItem('points_popup_dismissed')) {
                document.getElementById('points-popup-overlay')?.classList.add('hidden');
            }
        } catch (e) {}
    })();

    // ── Avatar ──
    document.getElementById('avatarInput').addEventListener('change', async function () {
        if (!this.files[0]) return;
        const fd = new FormData();
        fd.append('avatar', this.files[0]);
        try {
            const data = await apiFetch('/api/profile/avatar', 'POST', fd);
            const el = document.getElementById('avatarImg');
            if (el.tagName === 'IMG') {
                el.src = data.avatar_url;
            } else {
                const img = document.createElement('img');
                img.id = 'avatarImg';
                img.className = 'av';
                img.alt = '';
                img.src = data.avatar_url;
                img.style.width = '96px';
                img.style.height = '96px';
                el.replaceWith(img);
            }
            toast('Photo mise à jour !', 'success');
        } catch (e) { toast(e.message, 'error'); }
    });

    // ── Enregistrements ──
    async function saveBasic() {
        setBtnLoading('btn-save-basic', true);
        try {
            await apiFetch('/api/profile/basic', 'PUT', {
                first_name: document.getElementById('b_first_name').value,
                last_name:  document.getElementById('b_last_name').value,
                gender:     document.getElementById('b_gender').value || null,
                birthday:   document.getElementById('b_birthday').value || null,
                phone:      document.getElementById('b_phone').value.trim() || null,
                city_id:    document.getElementById('b_city_living_id').value || null,
            });
            toast('Informations mises à jour !', 'success');
            setTimeout(() => location.reload(), 800);
        } catch (e) { toast(e.message, 'error'); setBtnLoading('btn-save-basic', false); }
    }

    async function saveBio() {
        setBtnLoading('btn-save-bio', true);
        try {
            await apiFetch('/api/profile/bio', 'PUT', {
                motto:           document.getElementById('bio_motto').value || null,
                bio:             document.getElementById('bio_bio').value || null,
                open_to_network: document.getElementById('bio_open').checked,
            });
            toast('Bio mise à jour !', 'success');
            setTimeout(() => location.reload(), 800);
        } catch (e) { toast(e.message, 'error'); setBtnLoading('btn-save-bio', false); }
    }

    function toggleChip(btn) { btn.classList.toggle('on'); }

    async function saveProfessional() {
        setBtnLoading('btn-save-pro', true);
        const lookingFor = [...document.querySelectorAll('.lf-chip.on')].map(b => parseInt(b.dataset.value));
        const services   = [...document.querySelectorAll('.so-chip.on')].map(b => parseInt(b.dataset.value));
        try {
            await apiFetch('/api/profile/professional', 'PUT', {
                job_title:           document.getElementById('pro_job_title').value || null,
                sector:              document.getElementById('pro_sector').value || null,
                experience_level:    document.getElementById('pro_experience').value || null,
                market_addressed_id: document.getElementById('pro_market_addressed').value || null,
                market_target_id:    document.getElementById('pro_market_target').value || null,
                looking_for:         lookingFor,
                services_offered:    services,
            });
            toast('Profil mis à jour !', 'success');
            setTimeout(() => location.reload(), 800);
        } catch (e) { toast(e.message, 'error'); setBtnLoading('btn-save-pro', false); }
    }

    function toggleInterest(btn) { btn.classList.toggle('on'); }

    async function saveInterests() {
        setBtnLoading('btn-save-interests', true);
        const ids = [...document.querySelectorAll('.interest-chip.on')].map(b => parseInt(b.dataset.id));
        try {
            await apiFetch('/api/profile/interests', 'POST', { interests: ids });
            toast('Intérêts mis à jour !', 'success');
            setTimeout(() => location.reload(), 800);
        } catch (e) { toast(e.message, 'error'); setBtnLoading('btn-save-interests', false); }
    }

    // ── Compteur du motto ──
    const mottoInput = document.getElementById('bio_motto');
    const mottoCount = document.getElementById('mottoCount');
    if (mottoInput && mottoCount) {
        mottoInput.addEventListener('input', () => mottoCount.textContent = mottoInput.value.length);
    }

    // ── Autocomplétion des villes ──
    function filterCityDropdown(type, q) {
        const dd = document.getElementById(`city_${type}_dropdown`);
        const lq = q.toLowerCase();
        let any = false;
        dd.querySelectorAll('.city-opt').forEach(b => {
            const show = b.dataset.name.toLowerCase().includes(lq);
            b.style.display = show ? '' : 'none';
            if (show) any = true;
        });
        dd.classList.toggle('hidden', !any);
        if (!q) document.getElementById(`b_city_${type}_id`).value = '';
    }
    function showCityDropdown(type) { document.getElementById(`city_${type}_dropdown`).classList.remove('hidden'); }
    function hideCityDropdown(type) { setTimeout(() => document.getElementById(`city_${type}_dropdown`).classList.add('hidden'), 150); }
    function pickCity(type, id, name) {
        document.getElementById(`b_city_${type}_search`).value = name;
        document.getElementById(`b_city_${type}_id`).value = id;
        document.getElementById(`city_${type}_dropdown`).classList.add('hidden');
    }

    // ── Aperçu de la vidéo ──
    function previewVideoFile(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        document.getElementById('videoPlaceholder').classList.add('hidden');
        const nameEl = document.getElementById('videoPreviewName');
        nameEl.textContent = file.name + ' (' + (file.size / 1048576).toFixed(1) + ' Mo)';
        nameEl.classList.remove('hidden');
        const btn = document.getElementById('videoSubmitBtn');
        if (btn) btn.disabled = false;
    }
</script>
@endpush
