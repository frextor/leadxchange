@extends('layouts.app2')

@php
    $displayName = $canViewFull && $user['last_name'] ? $user['first_name'] . ' ' . $user['last_name'] : $user['first_name'];
    $rankLabels  = ['ambassador' => 'Ambassadeur', 'consul' => 'Consul', 'premium' => 'Membre ' . ($user['plan']['label'] ?? 'Premium'), 'basic' => 'Membre Basic'];
    $rankLabel   = $rankLabels[$user['rank']['level'] ?? 'basic'] ?? 'Membre';
    $stars       = (int) ($user['rating']['stars'] ?? 0);
    $status      = $user['connection_status'];
    $isConnected = $status === 'accepted';
    $roleLine    = collect([$profile?->job_title, $user['company']['name'] ?? null])->filter()->implode(' · ');
    $phone       = trim(($user['phone']['code'] ?? '') . ' ' . ($user['phone']['number'] ?? ''));
    $video       = $profile?->presentation_video && $profile?->presentation_video_status === 'approved' ? $profile->presentation_video_url : null;
    $names       = fn($ids) => collect($ids ?? [])->map(fn($id) => $sectors[$id]->name ?? null)->filter()->values();
    $sectorNames = $names($profile?->sector_ids)->whenEmpty(fn($c) => collect([$user['company']['sector']['name'] ?? null])->filter());
    $offered     = $names($profile?->services_offered);
    $wanted      = $names($profile?->looking_for);
    $marketA     = $profile?->market_addressed_id ? ($markets[$profile->market_addressed_id]->name ?? null) : null;
    $marketT     = $profile?->market_target_id ? ($markets[$profile->market_target_id]->name ?? null) : null;
@endphp

@section('title', $displayName . ' — LeadXchange')

{{-- Écran « Profil d'un membre » — maquette LeadXchange WEB › pageMember(id) --}}

@section('content')
<x-lx2-header title="" :back="route('connections.index')" />

@if(! $canViewFull)
<div class="card card-pad" style="max-width:520px;margin:40px auto;text-align:center;display:flex;flex-direction:column;align-items:center;gap:10px">
    <span class="av-fb" style="width:56px;height:56px"><x-lx2-icon name="lock" /></span>
    <h2 style="margin:0;font-size:18px;font-weight:600">Profil masqué</h2>
    <p class="prose" style="margin:0">Passez à une offre supérieure pour voir le profil complet de {{ $user['first_name'] }}. Avec votre offre, les profils sont affichés de façon anonyme.</p>
    <button type="button" class="btn btn-primary" onclick="openUpgradeModal('can_view_member_name')">Voir les offres</button>
</div>
@else
<div class="two-col member-layout" style="margin-top:0">
    <aside class="aside-sticky">
        <div class="card pcard">
            <x-lx2-avatar :user="$target" :size="96" />
            <h2>{{ $displayName }}</h2>
            <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;justify-content:center">
                <span class="badge b-muted">{{ $rankLabel }}</span>
                @if($stars > 0)<span class="stars"><x-lx2-icon name="star" />{{ $stars }}</span>@endif
                @if($profile?->open_to_network)<span class="badge b-ok">Ouvert au réseau</span>@endif
            </div>
            @if($roleLine)<div class="line">{{ $roleLine }}</div>@endif
            <div class="contacts">
                @if($user['email'])<span><x-lx2-icon name="mail" /><a href="mailto:{{ $user['email'] }}">{{ $user['email'] }}</a></span>@endif
                @if($user['phone']['number'] ?? null)<span class="num"><x-lx2-icon name="phone" /><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></span>@endif
                @if($user['city']['name'] ?? null)<span><x-lx2-icon name="map-pin" />{{ $user['city']['name'] }}</span>@endif
            </div>

            <div style="display:flex;gap:8px;width:100%;margin-top:10px;position:relative" id="connBox">
                <div style="flex:1;display:flex;gap:8px" id="connAction">
                    @if($isConnected)
                        <a class="btn btn-primary" style="flex:1" href="{{ route('chat.index', ['with' => $user['id']]) }}"><x-lx2-icon name="message-circle" />Message</a>
                    @elseif($status === 'pending' && $user['i_am_sender'])
                        <span class="btn btn-soft" style="flex:1">Invitation envoyée</span>
                    @elseif($status === 'pending' && $user['i_am_receiver'])
                        <button type="button" class="btn btn-primary" style="flex:1" data-accept="{{ $user['connection_id'] }}"><x-lx2-icon name="check" />Accepter</button>
                        <button type="button" class="btn btn-outline" style="flex:1" data-reject="{{ $user['connection_id'] }}">Refuser</button>
                    @elseif($canInvite)
                        <button type="button" class="btn btn-primary" style="flex:1" data-connect="{{ $user['id'] }}"><x-lx2-icon name="user-plus" />Connecter</button>
                    @else
                        <button type="button" class="btn btn-primary" style="flex:1" onclick="openUpgradeModal('can_send_invitations')"><x-lx2-icon name="user-plus" />Connecter</button>
                    @endif
                </div>
                <button type="button" class="btn btn-outline" style="width:36px;padding:0" aria-label="Plus d'actions" id="kebabBtn"><x-lx2-icon name="ellipsis-vertical" /></button>
                <div class="pop menu hidden" id="kebabPop" style="right:0;top:calc(100% + 6px)">
                    <button type="button" id="shareProfile"><x-lx2-icon name="share-2" />Partager le profil</button>
                    <button type="button" onclick="lx2Dialog('reportUserDialog')" style="color:var(--destructive)"><x-lx2-icon name="shield" />Signaler</button>
                </div>
            </div>

            @if($isConnected && $canSendLead)
                <a class="btn btn-outline btn-block" href="{{ route('leads.create', ['to' => $user['id']]) }}"><x-lx2-icon name="lx-send" />Envoyer un lead</a>
            @elseif(! $canSendLead)
                <button type="button" class="btn btn-outline btn-block" onclick="openUpgradeModal('can_send_leads')"><x-lx2-icon name="lx-send" />Envoyer un lead</button>
            @else
                <span class="btn btn-outline btn-block" style="opacity:.55;cursor:not-allowed" title="Connectez-vous d'abord avec ce membre"><x-lx2-icon name="lx-send" />Envoyer un lead</span>
            @endif

            @if($user['member_since'])
                <div class="help" style="margin-top:4px">Membre depuis {{ \Carbon\Carbon::parse('1 ' . $user['member_since'])->locale('fr')->isoFormat('MMMM YYYY') }}</div>
            @endif
        </div>
    </aside>

    <div style="display:flex;flex-direction:column;gap:18px;min-width:0">
        <div class="card card-pad">
            <h3 style="margin:0 0 8px;font-size:15px;font-weight:600">À propos</h3>
            @if($profile?->motto)<p class="prose" style="margin:0 0 8px;font-style:italic">« {{ $profile->motto }} »</p>@endif
            <p class="prose" style="margin:0;white-space:pre-line">{{ $profile?->bio ?: $user['first_name'] . " n'a pas encore rédigé de présentation." }}</p>
            @if($video)
            <div class="video"><video controls preload="metadata" src="{{ $video }}" style="width:100%;height:100%;object-fit:cover;background:#000"></video></div>
            @endif
        </div>

        <div class="card card-pad tags-block" style="display:grid;grid-template-columns:1fr 1fr;gap:22px">
            <div><h4>Marché adressé</h4><div class="val">{{ $marketA ?? '—' }}</div></div>
            <div><h4>Marché cible</h4><div class="val">{{ $marketT ?? '—' }}</div></div>
            <div><h4>Secteur</h4>
                @if($sectorNames->isNotEmpty())<div class="row">@foreach($sectorNames as $n)<span class="badge b-outline">{{ $n }}</span>@endforeach</div>@else<div class="val">—</div>@endif
            </div>
            <div><h4>Services proposés</h4>
                @if($offered->isNotEmpty())<div class="row">@foreach($offered as $n)<span class="badge b-muted">{{ $n }}</span>@endforeach</div>@else<div class="val">—</div>@endif
            </div>
            <div style="grid-column:1/-1"><h4>Recherche</h4>
                @if($wanted->isNotEmpty())<div class="row">@foreach($wanted as $n)<span class="badge b-muted">{{ $n }}</span>@endforeach</div>@else<div class="val">—</div>@endif
            </div>
            @if($interests->isNotEmpty())
            <div style="grid-column:1/-1"><h4>Centres d'intérêt</h4>
                <div class="row">@foreach($interests as $i)<span class="badge b-soft">{{ $i->icon }} {{ $i->name }}</span>@endforeach</div>
            </div>
            @endif
        </div>

        @if($user['company'])
        <div class="card card-pad">
            <h3 style="margin:0 0 10px;font-size:15px;font-weight:600">Entreprise</h3>
            <div class="mrow" style="padding:0">
                <span class="av-fb" style="width:40px;height:40px;border-radius:10px">{{ mb_strtoupper(mb_substr($user['company']['name'], 0, 1)) }}</span>
                <div class="who">
                    <div class="nm">{{ $user['company']['name'] }}</div>
                    <div class="role">{{ $user['company']['sector']['name'] ?? '' }}</div>
                </div>
                @if($user['company']['website'])
                <a class="btn btn-outline btn-sm" href="{{ $user['company']['website'] }}" target="_blank" rel="noopener"><x-lx2-icon name="globe" />Site web</a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Signaler un membre (CGU §8.2) --}}
<div class="overlay hidden" id="reportUserDialog" data-dialog>
    <form class="dialog" method="POST" action="{{ route('users.report', ['user' => $user['id']]) }}">
        @csrf
        <div class="dh">
            <div><h3>Signaler un comportement</h3><p>Harcèlement, fausses informations, usurpation, spam, pratiques déloyales… (CGU §8.2)</p></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db" style="display:flex;flex-direction:column;gap:10px">
            @foreach($reasons as $k => $l)
            <label class="radio"><input type="radio" name="reason" value="{{ $k }}" required><span class="dot"></span>{{ $l }}</label>
            @endforeach
            <textarea class="textarea" name="details" maxlength="500" placeholder="Détails (facultatif)"></textarea>
            <p class="help" style="margin:0">Notre équipe traite les signalements sous 10 jours ouvrés (CGU §6.7.2).</p>
        </div>
        <div class="df">
            <button type="button" class="btn btn-outline" data-close>Annuler</button>
            <button type="submit" class="btn btn-danger">Envoyer le signalement</button>
        </div>
    </form>
</div>
@endif
@endsection

@if($canViewFull)
@push('scripts')
<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const pop = $('kebabPop');
    $('kebabBtn').addEventListener('click', (e) => { e.stopPropagation(); pop.classList.toggle('hidden'); });
    document.addEventListener('click', (e) => { if (!$('connBox').contains(e.target)) pop.classList.add('hidden'); });
    $('shareProfile').addEventListener('click', () => {
        pop.classList.add('hidden');
        const url = location.href;
        if (navigator.share) { navigator.share({ title: @json($displayName . ' — LeadXchange'), url }).catch(() => {}); return; }
        navigator.clipboard?.writeText(url).then(() => toast('Lien du profil copié')).catch(() => toast(url));
    });

    async function api(url, body) {
        const res = await fetch(url, { method: 'POST', credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json',
                       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                       'Authorization': 'Bearer ' + (window.API_TOKEN || '') },
            body: JSON.stringify(body || {}) });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || data.success === false) throw new Error(data.message || 'Une erreur est survenue.');
        return data;
    }
    const ICON_MSG = @json(\App\Support\Lx2Icons::svg('message-circle'));
    const ICON_ADD = @json(\App\Support\Lx2Icons::svg('user-plus'));
    const USER_ID  = {{ $user['id'] }};

    $('connAction').addEventListener('click', async (e) => {
        const b = e.target.closest('[data-connect],[data-accept],[data-reject]');
        if (!b) return;
        const box = $('connAction');
        box.querySelectorAll('button').forEach(x => x.disabled = true);
        try {
            if (b.dataset.connect) {
                await api('/api/connections', { receiver_id: USER_ID });
                box.innerHTML = '<span class="btn btn-soft" style="flex:1">Invitation envoyée</span>';
                toast('Demande de connexion envoyée');
            } else if (b.dataset.accept) {
                await api(`/api/connections/${b.dataset.accept}/accept`);
                box.innerHTML = `<a class="btn btn-primary" style="flex:1" href="/chat?with=${USER_ID}">${ICON_MSG}Message</a>`;
                toast('Connexion acceptée 🎉');
                setTimeout(() => location.reload(), 900);
            } else {
                await api(`/api/connections/${b.dataset.reject}/reject`);
                box.innerHTML = `<button type="button" class="btn btn-primary" style="flex:1" data-connect="1">${ICON_ADD}Connecter</button>`;
                toast('Demande refusée');
            }
        } catch (err) {
            box.querySelectorAll('button').forEach(x => x.disabled = false);
            toast(err.message, 'error');
        }
    });
})();
</script>
@endpush
@endif
