@extends('layouts.app2')

@section('title', $user['first_name'] . ' ' . $user['last_name'] . ' — LeadXchange')

{{-- Écran « Mon profil » — design lx2 (profil personnel). Les profils des autres membres sont dans profile/member.blade.php. --}}

@php
    $me          = auth()->user();
    $fullName    = trim($user['first_name'] . ' ' . $user['last_name']);
    $initials    = mb_strtoupper(mb_substr($user['first_name'], 0, 1) . mb_substr($user['last_name'], 0, 1));
    $headline    = collect([$profile?->job_title, $user['company']['name'] ?? null])->filter()->implode(' · ');
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
    // Visuels des badges « Statut » et « Score » : icône + couleur plutôt qu'une image
    // (pas de maquette graphique fournie pour l'instant — robuste même sans assets).
    $planVisuals = [
        'basic'       => ['icon' => 'user',   'color' => 'var(--muted-fg)', 'background' => 'var(--muted)'],
        'premium'     => ['icon' => 'gem',    'color' => 'var(--primary)',  'background' => 'var(--primary-soft)'],
        'consul'      => ['icon' => 'shield', 'color' => 'var(--ok-fg)',    'background' => 'var(--ok-soft)'],
        'ambassadeur' => ['icon' => 'star',   'color' => 'var(--warm-fg)',  'background' => 'var(--warm-soft)'],
    ];
    $planVisual  = $planVisuals[$planKey] ?? $planVisuals['basic'];
    $badgeKey    = $user['badge']['level'] ?? 'neutre';
    $badgeLabel  = $user['badge']['label'] ?? 'Neutre';
    $expLabels   = ['junior' => 'Junior (0-2 ans)', 'mid' => 'Intermédiaire (2-5 ans)', 'senior' => 'Senior (5-10 ans)', 'expert' => 'Expert (10+ ans)'];
    $currentLookingFor      = $profile?->looking_for      ?? [];
    $currentServicesOffered = $profile?->services_offered ?? [];
    $marketAddressed = $profile?->market_addressed_id ? $markets->firstWhere('id', $profile->market_addressed_id) : null;
    $marketTarget    = $profile?->market_target_id    ? $markets->firstWhere('id', $profile->market_target_id)    : null;
    $wantedSectors   = $sectors->whereIn('id', $currentLookingFor);
    $offeredSectors  = $sectors->whereIn('id', $currentServicesOffered);
    $selectedInterestIds = $userInterests->pluck('id')->toArray();
    $balance     = $pointsBalance ?? null;
    $balanceColor = $balance === null ? 'var(--muted-fg)' : ($balance < 0 ? 'var(--destructive)' : ($balance === 0 ? 'var(--warm-fg)' : 'var(--ok-fg)'));
    $balanceHelp  = $balance === null ? '' : ($balance < 0
        ? 'Solde négatif : rechargez pour recevoir des leads.'
        : ($balance === 0
            ? 'Solde nul : envoyez des leads ou achetez des points.'
            : 'Vous pouvez envoyer et recevoir des leads.'));
    $showRoleCard = $me->isAmbassador() || $me->isConsul() || $me->hasPendingConsulPromotion() || $me->hasPaidPlan();
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

@push('styles')
<style>
    /* Cartes et titres */
    .pf-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}
    .pf-h{margin:0;font-size:15px;font-weight:600;letter-spacing:-.01em}
    .pf-cap{font-size:11.5px;font-weight:500;letter-spacing:.04em;text-transform:uppercase;color:var(--muted-fg);margin-bottom:4px}
    .pf-val{font-size:14px;color:var(--fg);line-height:1.5}
    .pf-val.pf-none{color:var(--muted-fg)}
    .pf-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px 28px}
    .pf-grid .full{grid-column:1/-1}
    .pf-empty{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;border:1.5px dashed var(--border);border-radius:var(--radius-lg);padding:16px 18px;color:var(--muted-fg);font-size:13.5px}

    /* En-tête : bannière, avatar qui la chevauche, actions alignées sur l'avatar, infos sous la bannière */
    .pf-hero{padding:0;overflow:hidden}
    .pf-cover{height:156px;position:relative;overflow:hidden;background:radial-gradient(90% 120% at 100% 0%,rgba(158,110,245,.6) 0%,rgba(158,110,245,0) 60%),var(--lx-accent-grad)}
    .pf-cover::before,.pf-cover::after{content:"";position:absolute;border-radius:999px;background:rgba(255,255,255,.1)}
    .pf-cover::before{width:340px;height:340px;right:-110px;top:-190px}
    .pf-cover::after{width:180px;height:180px;right:260px;bottom:-120px}
    .pf-topline{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;padding:0 24px;margin-top:-56px;position:relative}
    .pf-avatar-wrap{position:relative;flex:none}
    .pf-avatar{width:112px;height:112px;border-radius:999px;border:4px solid #fff;box-shadow:var(--shadow-md);object-fit:cover;display:grid;place-items:center;font-size:36px;font-weight:600;background:var(--primary-soft);color:var(--primary)}
    .pf-camera{position:absolute;right:0;bottom:4px;width:34px;height:34px;cursor:pointer;background:#fff;box-shadow:var(--shadow-md);border-color:var(--border)}
    .pf-info{padding:14px 24px 24px}
    .pf-name{margin:0;font-size:24px;font-weight:600;letter-spacing:-.02em;line-height:1.2}
    .pf-headline{margin-top:4px;font-size:15px;color:var(--fg-2)}
    .pf-meta{display:flex;flex-wrap:wrap;align-items:center;gap:8px 16px;font-size:13px;color:var(--muted-fg);margin-top:14px}
    .pf-meta span{display:inline-flex;align-items:center;gap:6px;min-width:0;overflow-wrap:anywhere}
    .pf-meta svg{width:14px;height:14px;flex:none}
    .pf-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center;padding-bottom:6px}

    /* Corps */
    .pf-body{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:20px;align-items:start;margin-top:20px}
    .pf-col{display:flex;flex-direction:column;gap:18px;min-width:0}
    .pf-motto{margin:0 0 10px;font-size:17px;font-weight:500;color:var(--fg);letter-spacing:-.01em;line-height:1.45}
    .pf-bio{margin:0;font-size:14px;color:var(--fg-2);line-height:1.7;white-space:pre-line;max-width:72ch}

    /* Vidéo */
    .pf-drop{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;padding:30px 20px;text-align:center;cursor:pointer;border:1.5px dashed var(--border);border-radius:var(--radius-lg);background:var(--bg);color:var(--muted-fg);transition:border-color .15s,background .15s}
    .pf-drop:hover{border-color:var(--primary);background:var(--primary-soft)}
    .pf-drop-icon{width:44px;height:44px;border-radius:999px;background:var(--primary-soft);color:var(--primary);display:grid;place-items:center}

    /* Colonne droite */
    .pf-todo{display:flex;align-items:center;justify-content:space-between;gap:10px;width:100%;border:1px solid var(--border);background:#fff;border-radius:var(--radius);padding:9px 12px;font-size:13.5px;color:var(--fg-2);text-align:left;cursor:pointer;transition:border-color .15s,background .15s}
    .pf-todo:hover{border-color:var(--primary);background:var(--primary-soft);color:var(--primary)}
    .pf-todo svg{width:14px;height:14px;flex:none;color:var(--muted-fg)}
    .pf-todo:hover svg{color:var(--primary)}
    .pf-progress{height:8px;border-radius:999px;background:var(--muted);overflow:hidden}
    .pf-progress > span{display:block;height:100%;border-radius:999px;background:var(--lx-accent-grad)}
    .pf-balance{font-size:32px;font-weight:600;letter-spacing:-.02em;line-height:1}
    .pf-stats{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .pf-stat-n{font-size:22px;font-weight:600;letter-spacing:-.02em;line-height:1.1}
    .pf-badge-ico{width:52px;height:52px;border-radius:14px;display:grid;place-items:center;flex:none}
    .pf-badge-ico svg{width:24px;height:24px}

    @media (max-width:1020px){
        .pf-body{grid-template-columns:minmax(0,1fr)}
    }
    #videoSubmitBtn:disabled{opacity:.45;cursor:not-allowed;box-shadow:none}
    .pf-meta span{overflow-wrap:anywhere;min-width:0}

    @media (max-width:640px){
        .pf-grid{grid-template-columns:1fr}
        .pf-topline{padding:0 18px;margin-top:-48px}
        .pf-avatar{width:96px;height:96px}
        .pf-info{padding:14px 18px 20px}
        .pf-name{font-size:21px}
        .pf-actions .btn{padding:0 12px}
    }
</style>
@endpush

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

{{-- ── En-tête : couverture, identité, actions ── --}}
<section class="card pf-hero">
    <div class="pf-cover"></div>

    <div class="pf-topline">
        <div class="pf-avatar-wrap">
            @if ($profile?->avatar_url)
                <img id="avatarImg" class="pf-avatar" src="{{ $profile->avatar_url }}" alt="">
            @else
                <span id="avatarImg" class="pf-avatar av-fb">{{ $initials }}</span>
            @endif
            <label for="avatarInput" class="icon-btn pf-camera" title="Changer la photo" aria-label="Changer la photo">
                <x-lx2-icon name="image-plus" />
            </label>
            <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp" class="hidden">
        </div>

        <div class="pf-actions">
            <button type="button" class="btn btn-primary" onclick="lx2Dialog('modal-basic')">
                <x-lx2-icon name="settings" />Modifier le profil
            </button>
        </div>
    </div>

    <div class="pf-info">
        <h1 class="pf-name">{{ $fullName }}</h1>
        @if ($headline)<div class="pf-headline">{{ $headline }}</div>@endif
        <div class="pf-meta">
            <span class="badge b-muted">{{ $badgeLabel }}</span>
            @if ($profile?->open_to_network)<span class="badge b-ok">Ouvert au réseau</span>@endif
            @if ($user['city']['name'] ?? null)<span><x-lx2-icon name="map-pin" />{{ $user['city']['name'] }}</span>@endif
            @if ($user['member_since'])<span><x-lx2-icon name="clock" />Membre depuis {{ \Carbon\Carbon::parse('1 ' . $user['member_since'])->locale('fr')->isoFormat('MMMM YYYY') }}</span>@endif
        </div>
    </div>
</section>

<div class="pf-body">

    {{-- ── Colonne principale ── --}}
    <div class="pf-col">

        {{-- À propos --}}
        <section class="card card-pad">
            <div class="pf-head">
                <h2 class="pf-h">À propos</h2>
                <button type="button" class="btn btn-ghost btn-sm" onclick="lx2Dialog('modal-bio')">Modifier</button>
            </div>
            @if ($profile?->motto)<p class="pf-motto">« {{ $profile->motto }} »</p>@endif
            @if ($profile?->bio)
                <p class="pf-bio">{{ $profile->bio }}</p>
            @elseif (! $profile?->motto)
                <div class="pf-empty">
                    <span>Présentez-vous en quelques lignes : les membres lisent votre bio avant de se connecter.</span>
                    <button type="button" class="btn btn-outline btn-sm" onclick="lx2Dialog('modal-bio')">Ajouter une bio</button>
                </div>
            @endif
        </section>

        {{-- Vidéo de présentation --}}
        <section class="card card-pad">
            <div class="pf-head">
                <h2 class="pf-h">Vidéo de présentation</h2>
                @if ($vPath)
                    @if ($vStatus === 'pending')
                        <span class="badge b-warm">En attente de validation</span>
                    @elseif ($vStatus === 'approved')
                        <span class="badge b-ok">Approuvée · visible</span>
                    @elseif ($vStatus === 'rejected')
                        <span class="badge b-hot">Rejetée</span>
                    @endif
                @endif
            </div>

            @if ($vPath)
                <div class="video" style="margin-top:0"><video controls preload="metadata" src="{{ $vUrl }}" style="width:100%;height:100%;object-fit:cover;background:#000"></video></div>
                @if ($vStatus === 'rejected' && $profile->presentation_video_rejection_reason)
                    <p class="help" style="color:var(--hot-fg)">Motif : {{ $profile->presentation_video_rejection_reason }}</p>
                @endif
                <form method="POST" action="{{ route('profile.video.delete') }}" style="margin-top:12px"
                      onsubmit="return confirm('Supprimer la vidéo de présentation ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger-soft btn-sm"><x-lx2-icon name="trash-2" />Supprimer la vidéo</button>
                </form>
            @else
                <form method="POST" action="{{ route('profile.video.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <label id="videoDropZone" class="pf-drop">
                        <input type="file" name="video" id="videoInput" accept="video/mp4,video/webm,video/quicktime,video/avi" class="hidden"
                               onchange="previewVideoFile(this)">
                        <span class="pf-drop-icon"><x-lx2-icon name="video" /></span>
                        <div id="videoPlaceholder" style="display:flex;flex-direction:column;align-items:center;gap:2px">
                            <span style="font-size:14px;font-weight:500;color:var(--fg-2)">Ajoutez une courte vidéo de présentation</span>
                            <span class="help" style="margin:0">MP4, WebM, MOV — 100 Mo maximum · validée par notre équipe</span>
                        </div>
                        <div id="videoPreviewName" class="hidden" style="font-size:13.5px;font-weight:600;color:var(--primary)"></div>
                    </label>
                    @error('video') <p class="help" style="color:var(--destructive)">{{ $message }}</p> @enderror
                    <button type="submit" id="videoSubmitBtn" class="btn btn-primary" style="margin-top:12px" disabled>Envoyer pour validation</button>
                </form>
            @endif
        </section>

        {{-- Profil professionnel --}}
        <section class="card card-pad">
            <div class="pf-head">
                <h2 class="pf-h">Profil professionnel</h2>
                <button type="button" class="btn btn-ghost btn-sm" onclick="lx2Dialog('modal-professional')">Modifier</button>
            </div>
            <div class="pf-grid">
                <div><div class="pf-cap">Poste</div><div class="pf-val {{ $profile?->job_title ? '' : 'pf-none' }}">{{ $profile?->job_title ?: 'Non renseigné' }}</div></div>
                <div><div class="pf-cap">Secteur d'activité</div><div class="pf-val {{ $profile?->sector ? '' : 'pf-none' }}">{{ $profile?->sector ?: 'Non renseigné' }}</div></div>
                <div><div class="pf-cap">Expérience</div><div class="pf-val {{ $profile?->experience_level ? '' : 'pf-none' }}">{{ $expLabels[$profile?->experience_level] ?? 'Non renseignée' }}</div></div>
                <div><div class="pf-cap">Marché adressé aujourd'hui</div><div class="pf-val {{ $marketAddressed ? '' : 'pf-none' }}">{{ $marketAddressed?->name ?? 'Non renseigné' }}</div></div>
                <div class="full"><div class="pf-cap">Marché à développer</div><div class="pf-val {{ $marketTarget ? '' : 'pf-none' }}">{{ $marketTarget?->name ?? 'Non renseigné' }}</div></div>
                <div class="full">
                    <div class="pf-cap">Contacts recherchés</div>
                    @if ($wantedSectors->isNotEmpty())
                        <div style="display:flex;flex-wrap:wrap;gap:6px">@foreach ($wantedSectors as $s)<span class="badge b-outline">{{ $s->name }}</span>@endforeach</div>
                    @else <div class="pf-val pf-none">Aucun secteur choisi</div> @endif
                </div>
                <div class="full">
                    <div class="pf-cap">Contacts que je peux proposer</div>
                    @if ($offeredSectors->isNotEmpty())
                        <div style="display:flex;flex-wrap:wrap;gap:6px">@foreach ($offeredSectors as $s)<span class="badge b-muted">{{ $s->name }}</span>@endforeach</div>
                    @else <div class="pf-val pf-none">Aucun secteur choisi</div> @endif
                </div>
            </div>
        </section>

        {{-- Centres d'intérêt --}}
        <section class="card card-pad">
            <div class="pf-head">
                <h2 class="pf-h">Centres d'intérêt</h2>
                <button type="button" class="btn btn-ghost btn-sm" onclick="lx2Dialog('modal-interests')">Modifier</button>
            </div>
            @if ($userInterests->isNotEmpty())
                <div style="display:flex;flex-wrap:wrap;gap:8px">
                    @foreach ($userInterests as $interest)
                        <span class="badge b-soft" style="height:28px;padding:0 12px;font-size:13px">{{ $interest->icon }} {{ $interest->name }}</span>
                    @endforeach
                </div>
            @else
                <div class="pf-empty">
                    <span>Ajoutez vos centres d'intérêt pour être suggéré aux bons membres.</span>
                    <button type="button" class="btn btn-outline btn-sm" onclick="lx2Dialog('modal-interests')">Ajouter</button>
                </div>
            @endif
        </section>

        {{-- Entreprise --}}
        <section class="card card-pad">
            <div class="pf-head"><h2 class="pf-h">Entreprise</h2></div>
            @if ($user['company'])
                <div class="mrow" style="padding:0">
                    <span class="av-fb" style="width:48px;height:48px;border-radius:12px;font-size:16px">{{ mb_strtoupper(mb_substr($user['company']['name'], 0, 1)) }}</span>
                    <div class="who">
                        <div class="nm" style="font-size:15px">{{ $user['company']['name'] }}</div>
                        <div class="role">{{ $user['company']['sector']['name'] ?? 'Secteur non renseigné' }}</div>
                    </div>
                    @if ($user['company']['website'])
                        <a class="btn btn-outline btn-sm" href="{{ $user['company']['website'] }}" target="_blank" rel="noopener"><x-lx2-icon name="globe" />Site web</a>
                    @endif
                </div>
            @else
                <div class="pf-empty">
                    <span>Aucune entreprise liée à votre profil.</span>
                    <a class="btn btn-outline btn-sm" href="{{ route('company.create') }}">Ajouter mon entreprise</a>
                </div>
            @endif
        </section>

        {{-- Informations personnelles --}}
        <section class="card card-pad">
            <div class="pf-head">
                <h2 class="pf-h">Informations</h2>
                <button type="button" class="btn btn-ghost btn-sm" onclick="lx2Dialog('modal-basic')">Modifier</button>
            </div>
            <div class="pf-grid">
                <div><div class="pf-cap">Email</div><div class="pf-val">{{ $user['email'] }}</div></div>
                <div><div class="pf-cap">Ville actuelle</div><div class="pf-val {{ $user['city']['name'] ?? null ? '' : 'pf-none' }}">{{ $user['city']['name'] ?? 'Non renseignée' }}</div></div>
                <div><div class="pf-cap">Genre</div><div class="pf-val {{ $user['gender'] ? '' : 'pf-none' }}">{{ $user['gender'] ? ['male' => 'Homme', 'female' => 'Femme', 'other' => 'Autre'][$user['gender']] ?? $user['gender'] : 'Non renseigné' }}</div></div>
                <div><div class="pf-cap">Date de naissance</div><div class="pf-val {{ $user['birthday'] ? '' : 'pf-none' }}">{{ $user['birthday'] ? \Carbon\Carbon::parse($user['birthday'])->locale('fr')->isoFormat('D MMMM YYYY') : 'Non renseignée' }}</div></div>
                <div class="full"><div class="pf-cap">Téléphone</div><div class="pf-val {{ $user['phone']['number'] ?? null ? '' : 'pf-none' }}">{{ $user['phone']['number'] ?? 'Non renseigné' }}</div></div>
            </div>
        </section>

    </div>

    {{-- ── Colonne latérale ── --}}
    <aside class="pf-col">

        {{-- Complétion : chaque élément manquant ouvre directement son éditeur --}}
        @if ($completion < 100)
        <section class="card card-pad">
            <div class="pf-head" style="margin-bottom:10px">
                <h2 class="pf-h">Complétez votre profil</h2>
                <span style="font-size:18px;font-weight:600;color:var(--primary)">{{ $completion }}%</span>
            </div>
            <div class="pf-progress"><span style="width:{{ $completion }}%"></span></div>
            @if ($missing)
            <div style="display:flex;flex-direction:column;gap:6px;margin-top:14px">
                @foreach ($missing as $item)
                    @php $target = $modalMap[$item['key']] ?? null; @endphp
                    <button type="button" class="pf-todo" onclick="missingGo(@js($target))">
                        <span>{{ $item['label'] }}</span>
                        <x-lx2-icon name="chevron-right" />
                    </button>
                @endforeach
            </div>
            @endif
        </section>
        @endif

        {{-- Points --}}
        @if ($balance !== null)
        <section class="card card-pad">
            <div class="pf-head" style="margin-bottom:12px">
                <h2 class="pf-h">Mes points</h2>
                <a class="link" href="{{ route('points.index') }}" style="font-size:13px">Historique</a>
            </div>
            <div class="pf-balance" style="color:{{ $balanceColor }}">{{ $balance > 0 ? '+' : '' }}{{ $balance }}<span style="font-size:14px;font-weight:500;color:var(--muted-fg);margin-left:6px">pts</span></div>
            <p class="help" style="margin:10px 0 14px">{{ $balanceHelp }}</p>
            <a class="btn btn-outline btn-block" href="{{ route('points.index') }}"><x-lx2-icon name="wallet" />Acheter des points</a>
        </section>
        @endif

        {{-- Rôle --}}
        @if ($showRoleCard)
        <section class="card card-pad">
            <h2 class="pf-h" style="margin-bottom:12px">Statut</h2>
            <div style="display:flex;flex-direction:column;gap:8px">
                @if ($me->isAmbassador())
                    <span class="badge b-warm" style="height:28px;align-self:flex-start"><x-lx2-icon name="star" />Ambassadeur</span>
                @elseif ($me->isConsul())
                    <span class="badge b-ok" style="height:28px;align-self:flex-start"><x-lx2-icon name="shield" />Consul</span>
                    @if ($me->hasPendingAmbassadorRequest())
                        <span class="help" style="margin:0">Demande Ambassadeur en attente de validation.</span>
                    @else
                        <form method="POST" action="{{ route('consul.request') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline btn-block"><x-lx2-icon name="star" />Demander le rôle Ambassadeur</button>
                        </form>
                    @endif
                @elseif ($me->hasPendingConsulPromotion())
                    <span class="help" style="margin:0">Demande Consul en attente de validation.</span>
                @elseif ($me->hasPaidPlan())
                    <form method="POST" action="{{ route('consul.request-promote') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-block"><x-lx2-icon name="shield" />Demander le rôle Consul</button>
                    </form>
                @endif
            </div>
        </section>
        @endif

        {{-- Plan et badge — image envoyée par un admin (/admin/notation/icons) si disponible,
             sinon une icône de repli colorée (jamais vide, même sans visuel importé). --}}
        <section class="card card-pad">
            <div style="display:flex;align-items:center;gap:12px">
                <span class="pf-badge-ico" id="planIcoFallback" style="background:{{ $planVisual['background'] }};color:{{ $planVisual['color'] }};display:none"><x-lx2-icon :name="$planVisual['icon']" /></span>
                <img src="{{ asset('images/plans/' . $planKey . '.jpg') }}" alt="{{ $planLabel }}"
                     style="width:52px;height:52px;object-fit:contain;flex:none;border-radius:14px"
                     onerror="this.style.display='none';document.getElementById('planIcoFallback').style.display='grid'">
                <div>
                    <div class="pf-cap" style="margin:0">Statut</div>
                    <div style="font-size:14px;font-weight:600">Plan {{ $planLabel }}</div>
                </div>
            </div>
            <hr class="sep" style="margin:14px 0">
            <div style="display:flex;align-items:center;gap:12px">
                <span class="pf-badge-ico" id="scoreIcoFallback" style="background:{{ $user['badge']['background'] ?? 'var(--muted)' }};color:{{ $user['badge']['color'] ?? 'var(--muted-fg)' }};display:none"><x-lx2-icon name="gem" /></span>
                <img src="{{ asset('images/badges/' . $badgeKey . '.jpg') }}" alt="{{ $badgeLabel }}"
                     style="width:52px;height:52px;object-fit:contain;flex:none;border-radius:14px"
                     onerror="this.style.display='none';document.getElementById('scoreIcoFallback').style.display='grid'">
                <div>
                    <div class="pf-cap" style="margin:0">Badge score</div>
                    <div style="font-size:14px;font-weight:600">{{ $badgeLabel }}</div>
                    <div class="help" style="margin:2px 0 0">{{ $user['balance'] ?? 0 }} points</div>
                </div>
            </div>
        </section>

        {{-- Statistiques (valeurs d'affichage, non calculées) --}}
        <section class="card card-pad">
            <h2 class="pf-h" style="margin-bottom:14px">Activité</h2>
            <div class="pf-stats">
                <div><div class="pf-stat-n">{{ $activityStats['exchanges'] }}</div><div class="help" style="margin:2px 0 0">Échanges</div></div>
                <div><div class="pf-stat-n">{{ $activityStats['leads'] }}</div><div class="help" style="margin:2px 0 0">Leads reçus</div></div>
                <div><div class="pf-stat-n">{{ $activityStats['connections'] }}</div><div class="help" style="margin:2px 0 0">Connexions</div></div>
                <div><div class="pf-stat-n">{{ $activityStats['score'] }}</div><div class="help" style="margin:2px 0 0">Score</div></div>
            </div>
        </section>

    </aside>
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
            <div style="display:flex;flex-direction:column;gap:6px">
                @foreach ($missing as $item)
                    @php $target = $modalMap[$item['key']] ?? null; @endphp
                    <button type="button" class="pf-todo" onclick="missingGo(@js($target))">
                        <span>{{ $item['label'] }}</span>
                        <x-lx2-icon name="chevron-right" />
                    </button>
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
                img.className = 'pf-avatar';
                img.alt = '';
                img.src = data.avatar_url;
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
