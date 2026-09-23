@extends('layouts.app2')

@section('title', 'Envoyer un lead — LeadXchange')

{{-- Écran « Envoyer un lead » — maquette LeadXchange WEB › pageSendLead() --}}

@php
    $oldReceiver = (int) old('receiver_id', $preselectedId);
    $selectedDest = $connections->firstWhere('id', $oldReceiver);
    if ($selectedDest && $selectedDest->points_balance < 1) { $selectedDest = null; }
    $qual = old('qualification', 'tiede');
    $qualLabels = ['chaud' => 'Chaud', 'tiede' => 'Tiède', 'froid' => 'Froid'];
    $prefixes = ['+33' => '🇫🇷 +33', '+32' => '🇧🇪 +32', '+41' => '🇨🇭 +41', '+352' => '🇱🇺 +352', '+212' => '🇲🇦 +212', '+216' => '🇹🇳 +216', '+213' => '🇩🇿 +213', '+1' => '🇨🇦 +1'];
    $err = fn($k) => $errors->has($k) ? 'is-err' : '';
@endphp

@section('content')
<x-lx2-header title="Envoyer un lead" sub="Partagez une opportunité qualifiée avec un membre de votre réseau" :back="route('leads.index')" />

@if($errors->has('error'))
<div class="lx2-flash b-hot" role="alert"><span>{{ $errors->first('error') }}</span></div>
@endif

<div class="form-layout">
    <form class="card" id="leadForm" method="POST" action="{{ route('leads.store') }}" style="padding:4px 24px 0" novalidate>
        @csrf

        <section class="fsec">
            <div><h2>Affectation du destinataire</h2><p>Choisissez le membre qui recevra ce lead et l'entreprise concernée.</p></div>
            <div class="fgrid">
                <div class="field full" style="position:relative" id="destField">
                    <label class="label" for="dest">Destinataire</label>
                    <input type="hidden" name="receiver_id" id="receiverId" value="{{ $selectedDest?->id }}">
                    <button type="button" class="select {{ $selectedDest ? 'filled' : '' }} {{ $err('receiver_id') }}" id="dest" aria-haspopup="listbox" aria-expanded="false">
                        <span id="destLabel" style="display:flex;align-items:center;gap:8px">
                            @if($selectedDest)<x-lx2-avatar :user="$selectedDest" :size="22" />{{ member_name($selectedDest) }}@else Sélectionner un destinataire @endif
                        </span>
                        <x-lx2-icon name="chevron-down" />
                    </button>
                    <div class="pop dest-pop hidden" id="destPop" role="listbox">
                        @if($connections->isEmpty())
                            <div class="lx2-dd-state">Connectez-vous avec des membres pour leur envoyer des leads.<br><a class="link" href="{{ route('connections.index') }}">Explorer le réseau</a></div>
                        @else
                            <div class="search"><input class="input" id="destSearch" placeholder="Rechercher une connexion" autocomplete="off"></div>
                            @foreach($connections as $c)
                            @php $blocked = $c->points_balance < 1; $r = $ratings[$c->id] ?? null; @endphp
                            <button type="button" class="dest-opt" role="option"
                                    data-id="{{ $c->id }}" data-name="{{ member_name($c) }}" data-role="{{ $c->profile?->job_title ?? 'Membre LeadXchange' }}"
                                    data-stars="{{ $r ? round($r) : '' }}" data-search="{{ mb_strtolower(member_name($c)) }}"
                                    @if($blocked) aria-disabled="true" data-balance="{{ $c->points_balance }}" @endif>
                                <x-lx2-avatar :user="$c" :size="28" />
                                <span style="flex:1;min-width:0">
                                    {{ member_name($c) }}
                                    @if($blocked)
                                        <small class="dest-warn">Ne peut pas recevoir de lead (solde {{ $c->points_balance }} pt)</small>
                                    @else
                                        <small>{{ $c->profile?->job_title ?? 'Membre LeadXchange' }}</small>
                                    @endif
                                </span>
                                <span class="dest-avatar" hidden><x-lx2-avatar :user="$c" :size="22" /></span>
                                <span class="dest-avatar-lg" hidden><x-lx2-avatar :user="$c" :size="40" /></span>
                            </button>
                            @endforeach
                            <div class="lx2-dd-state" id="destNoResult" hidden>Aucun résultat</div>
                        @endif
                    </div>
                    @error('receiver_id')<p class="field-err">{{ $message }}</p>@enderror
                </div>
                <div class="field full">
                    <label class="label" for="fEnt">Entreprise</label>
                    <input class="input {{ $err('company_name') }}" id="fEnt" name="company_name" value="{{ old('company_name') }}" maxlength="150" required placeholder="Nom de l'entreprise">
                    @error('company_name')<p class="field-err">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="fsec">
            <div><h2>Information de contact</h2><p>La personne à contacter chez le prospect.</p></div>
            <div class="fgrid">
                <div class="field">
                    <label class="label" for="fNom">Nom</label>
                    <input class="input {{ $err('contact_name') }}" id="fNom" name="contact_last_name" value="{{ old('contact_last_name') }}" maxlength="60" required placeholder="Nom de contact">
                    @error('contact_name')<p class="field-err">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label class="label" for="fPrenom">Prénom</label>
                    <input class="input" id="fPrenom" name="contact_first_name" value="{{ old('contact_first_name') }}" maxlength="60" placeholder="Prénom de contact">
                </div>
                <div class="field">
                    <label class="label" for="fEmail">Email</label>
                    <input class="input {{ $err('contact_email') }}" id="fEmail" name="contact_email" type="email" value="{{ old('contact_email') }}" maxlength="150" required placeholder="m@exemple.com">
                    @error('contact_email')<p class="field-err">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label class="label" for="fTel">Numéro de téléphone</label>
                    <div class="phone">
                        <select class="select filled" name="contact_phone_prefix" aria-label="Indicatif">
                            @foreach($prefixes as $v => $l)<option value="{{ $v }}" @selected(old('contact_phone_prefix', '+33') === $v)>{{ $l }}</option>@endforeach
                        </select>
                        <input class="input {{ $err('contact_phone') }}" id="fTel" name="contact_phone" value="{{ old('contact_phone') }}" maxlength="20" required placeholder="000000000" inputmode="tel">
                    </div>
                    @error('contact_phone')<p class="field-err">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label class="label" for="fPoste">Poste</label>
                    <input class="input" id="fPoste" name="contact_position" value="{{ old('contact_position') }}" maxlength="100" placeholder="Intitulé du poste">
                </div>
                <div class="field">
                    <label class="label" for="due">Échéance</label>
                    <input class="input {{ $err('deadline') }}" id="due" name="deadline" type="date" value="{{ old('deadline') }}" min="{{ now()->addDay()->format('Y-m-d') }}" required>
                    @error('deadline')<p class="field-err">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="fsec">
            <div><h2>Détails du lead</h2><p>Qualifiez le lead pour aider le destinataire à prioriser.</p></div>
            <div class="fgrid">
                <div class="field full">
                    <span class="label">Qualification</span>
                    <input type="hidden" name="qualification" id="qualInput" value="{{ $qual }}">
                    <div class="toggle" id="qualToggle">
                        @foreach($qualLabels as $k => $l)
                        <button type="button" class="{{ $qual === $k ? 'on' : '' }}" data-v="{{ $k }}">{{ $l }}</button>
                        @endforeach
                    </div>
                    @error('qualification')<p class="field-err">{{ $message }}</p>@enderror
                </div>
                <div class="field full">
                    <label class="label" for="sector">Secteur</label>
                    <select class="select {{ old('sector_id') ? 'filled' : '' }} {{ $err('sector_id') }}" id="sector" name="sector_id" required>
                        <option value="">Sélectionner un secteur</option>
                        @foreach($sectors as $s)<option value="{{ $s->id }}" @selected((int) old('sector_id') === $s->id)>{{ $s->name }}</option>@endforeach
                    </select>
                    @error('sector_id')<p class="field-err">{{ $message }}</p>@enderror
                </div>
                <div class="field full">
                    <label class="label" for="fDesc">Description</label>
                    <textarea class="textarea" id="fDesc" name="description" maxlength="2000" placeholder="Ajouter des commentaires">{{ old('description') }}</textarea>
                </div>
            </div>
        </section>

        <section class="fsec">
            <div><h2>Conformité</h2><p>Obligatoire avant tout partage de données personnelles.</p></div>
            <div class="consent">
                <label class="check"><input type="checkbox" id="cRgpd" name="rgpd_consent" value="1" required @checked(old('rgpd_consent'))><span class="box"></span>
                    <span><b>Consentement RGPD</b><span class="t">Je certifie avoir obtenu le consentement du prospect ou disposer d'une base légale (art. 6 RGPD) pour partager ses données personnelles à des fins de prospection commerciale.</span></span></label>
                <label class="check"><input type="checkbox" id="cLicite" name="no_sensitive_data" value="1" required @checked(old('no_sensitive_data'))><span class="box"></span>
                    <span><b>Données licites</b><span class="t">Ce lead ne contient aucune donnée sensible (santé, opinions politiques…), aucune donnée de mineur, ni d'information couverte par un accord de confidentialité.</span></span></label>
                <p class="field-err" id="consentErr" hidden>Cochez les deux déclarations pour envoyer le lead.</p>
            </div>
        </section>

        <div class="sticky-foot" style="margin:0 -24px;border-radius:0 0 var(--radius-lg) var(--radius-lg)">
            <a class="btn btn-outline" href="{{ route('leads.index') }}">Annuler</a>
            <button class="btn btn-primary" type="submit" id="sendBtn"><x-lx2-icon name="lx-send" />Envoyer le lead</button>
        </div>
    </form>

    <aside class="aside-sticky">
        <div class="card card-pad">
            <h3 style="margin:0 0 12px;font-size:14px;font-weight:600">Destinataire</h3>
            <div id="destCard">
                @if($selectedDest)
                    <div class="mrow" style="padding:0"><x-lx2-avatar :user="$selectedDest" :size="40" /><div class="who"><div class="nm">{{ member_name($selectedDest) }}
                        @if($ratings[$selectedDest->id] ?? null)<span class="stars"><x-lx2-icon name="star" />{{ round($ratings[$selectedDest->id]) }}</span>@endif
                    </div><div class="role">{{ $selectedDest->profile?->job_title ?? 'Membre LeadXchange' }}</div></div></div>
                @else
                    <div style="color:var(--muted-fg);font-size:13px">Aucun destinataire sélectionné.</div>
                @endif
            </div>
        </div>
        <div class="card card-pad">
            <h3 style="margin:0 0 12px;font-size:14px;font-weight:600">Avant d'envoyer</h3>
            <ul class="clean progress-list">
                <li id="ckDest"><span class="c"><x-lx2-icon name="check" /></span>Destinataire choisi</li>
                <li id="ckContact"><span class="c"><x-lx2-icon name="check" /></span>Contact renseigné</li>
                <li id="ckQual" class="done"><span class="c"><x-lx2-icon name="check" /></span>Qualification : <span id="ckQualLabel">{{ $qualLabels[$qual] ?? 'Tiède' }}</span></li>
                <li id="ckConsent"><span class="c"><x-lx2-icon name="check" /></span>Consentements cochés</li>
            </ul>
            <hr class="sep" style="margin:14px 0">
            <div style="display:flex;justify-content:space-between;gap:12px;font-size:13px">
                <span style="color:var(--muted-fg)">Points gagnés si le lead est accepté</span><b class="pos" style="white-space:nowrap">+{{ $pointsOnAccept }} pts</b>
            </div>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const form = $('leadForm');
    const qualLabels = @json($qualLabels);

    /* ── Destinataire ── */
    const destBtn = $('dest'), pop = $('destPop');
    const openPop = (open) => { pop.classList.toggle('hidden', !open); destBtn.setAttribute('aria-expanded', open); if (open) $('destSearch')?.focus(); };
    destBtn.addEventListener('click', (e) => { e.stopPropagation(); openPop(pop.classList.contains('hidden')); });
    document.addEventListener('click', (e) => { if (!$('destField').contains(e.target)) openPop(false); });
    $('destSearch')?.addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase().trim(); let n = 0;
        pop.querySelectorAll('.dest-opt').forEach(o => { const ok = !q || o.dataset.search.includes(q); o.hidden = !ok; if (ok) n++; });
        $('destNoResult').hidden = n > 0;
    });
    pop.addEventListener('click', (e) => {
        const o = e.target.closest('.dest-opt');
        if (!o) return;
        if (o.getAttribute('aria-disabled') === 'true') {
            toast(o.dataset.name + ' ne peut pas recevoir de lead pour le moment (solde de points insuffisant).', 'error');
            return;
        }
        $('receiverId').value = o.dataset.id;
        $('destLabel').innerHTML = o.querySelector('.dest-avatar').innerHTML + escapeHtml(o.dataset.name);
        destBtn.classList.add('filled'); destBtn.classList.remove('is-err');
        const stars = o.dataset.stars ? `<span class="stars">{!! \App\Support\Lx2Icons::svg('star') !!}${o.dataset.stars}</span>` : '';
        $('destCard').innerHTML = `<div class="mrow" style="padding:0">${o.querySelector('.dest-avatar-lg').innerHTML}<div class="who"><div class="nm">${escapeHtml(o.dataset.name)} ${stars}</div><div class="role">${escapeHtml(o.dataset.role)}</div></div></div>`;
        openPop(false);
        refresh();
    });

    /* ── Qualification ── */
    $('qualToggle').addEventListener('click', (e) => {
        const b = e.target.closest('[data-v]');
        if (!b) return;
        $('qualInput').value = b.dataset.v;
        $('qualToggle').querySelectorAll('button').forEach(x => x.classList.toggle('on', x === b));
        $('ckQualLabel').textContent = qualLabels[b.dataset.v];
    });

    $('sector').addEventListener('change', (e) => e.target.classList.toggle('filled', !!e.target.value));

    /* ── Checklist « Avant d'envoyer » ── */
    function refresh() {
        $('ckDest').classList.toggle('done', !!$('receiverId').value);
        $('ckContact').classList.toggle('done', !!($('fNom').value.trim() && $('fEmail').value.trim() && $('fTel').value.trim()));
        $('ckConsent').classList.toggle('done', $('cRgpd').checked && $('cLicite').checked);
    }
    form.addEventListener('input', refresh);
    form.addEventListener('change', refresh);
    refresh();

    /* ── Contrôles avant envoi (le serveur revalide tout) ── */
    form.addEventListener('submit', (e) => {
        let ok = true;
        if (!$('receiverId').value) { destBtn.classList.add('is-err'); ok = false; }
        ['fEnt', 'fNom', 'fEmail', 'fTel', 'due', 'sector'].forEach(id => {
            const el = $(id), bad = !el.value.trim() || (el.type === 'email' && !el.checkValidity());
            el.classList.toggle('is-err', bad); if (bad) ok = false;
        });
        const consent = $('cRgpd').checked && $('cLicite').checked;
        $('consentErr').hidden = consent; if (!consent) ok = false;
        if (!ok) {
            e.preventDefault();
            (form.querySelector('.is-err') || $('consentErr')).scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        $('sendBtn').disabled = true;
    });
})();
</script>
@endpush
