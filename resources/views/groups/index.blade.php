@extends('layouts.app2')

@section('title', 'Groupes — LeadXchange')

{{-- Écran « Groupes » — maquette LeadXchange WEB › pageGroupes() --}}

@php
    $me        = auth()->user();
    $canCreate = $me->canFeature('can_create_pole');
    $colors    = ['#4154F4', '#EA580C', '#16A34A', '#E11D48', '#A855F7', '#65D63C', '#EAB308', '#D946EF'];
    $isConsul  = $me->isConsulForCurrentCity();
    $panes     = [
        'invitations' => ['Invitations', $invitations->count()],
        'mine'        => ['Mes groupes', null],
        'discover'    => ['Découvrir', null],
    ];
@endphp

@section('content')
<x-lx2-header title="Groupes" sub="Échangez avec des communautés de professionnels" />

<div class="toolbar">
    <div class="chips" id="grpTabs">
        @foreach($panes as $key => [$label, $n])
        <button type="button" class="chip {{ $tab === $key ? 'on' : '' }}" data-pane="{{ $key }}">{{ $label }}@if($n)<span class="n num">{{ $n }}</span>@endif</button>
        @endforeach
    </div>
    <div class="grow"></div>
    <div class="input-wrap" style="width:280px;max-width:100%">
        <x-lx2-icon name="search" />
        <input class="input" id="groupSearch" type="search" placeholder="Rechercher des groupes" autocomplete="off">
    </div>
    @if($canCreate)
        <button type="button" class="btn btn-primary" onclick="lx2Dialog('createGroupDialog')"><x-lx2-icon name="plus" /><span class="lbl-m">Créer un groupe</span></button>
    @else
        <button type="button" class="btn btn-primary" onclick="openUpgradeModal('can_create_pole')"><x-lx2-icon name="plus" /><span class="lbl-m">Créer un groupe</span></button>
    @endif
</div>

<div data-pane-body="invitations" @if($tab !== 'invitations') hidden @endif>
    @if($invitations->isNotEmpty())
    <div class="grid-3">
        @foreach($invitations as $inv)
            @if($inv->group)
            @include('groups._card', ['group' => $inv->group, 'invitation' => $inv])
            @endif
        @endforeach
    </div>
    @else
    <div class="card empty">Aucune invitation en attente.</div>
    @endif
</div>

<div data-pane-body="mine" @if($tab !== 'mine') hidden @endif>
    @if($myGroups->isNotEmpty())
    <div class="grid-3">
        @foreach($myGroups as $g)
            @include('groups._card', ['group' => $g, 'role' => $userRoles[$g->id] ?? 'member'])
        @endforeach
    </div>
    @else
    <div class="card empty">Vous n'êtes membre d'aucun groupe. <a class="link" href="?tab=discover">Découvrir des groupes</a></div>
    @endif
</div>

<div data-pane-body="discover" @if($tab !== 'discover') hidden @endif>
    @if($discover->isNotEmpty())
    <div class="grid-3">
        @foreach($discover as $g)
            @include('groups._card', ['group' => $g, 'requested' => in_array($g->id, $requestedIds)])
        @endforeach
    </div>
    @else
    <div class="card empty">Aucun nouveau groupe à découvrir pour le moment.</div>
    @endif
</div>
<div class="card empty" id="grpNoMatch" hidden>Aucun groupe ne correspond à votre recherche.</div>

{{-- Créer un groupe (mêmes champs que GroupController@store) --}}
@if($canCreate)
<div class="overlay {{ $errors->hasAny(['name', 'description', 'sector_id', 'city_id', 'cover_photo', 'cover_color']) ? '' : 'hidden' }}" id="createGroupDialog" data-dialog>
    <form class="dialog" method="POST" action="{{ route('groups.store') }}" enctype="multipart/form-data" style="max-width:520px">
        @csrf
        <div class="dh">
            <div><h3>Créer un groupe</h3><p>Réunissez des professionnels autour d'un secteur ou d'une ville.</p></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db" style="display:flex;flex-direction:column;gap:14px">
            <div class="field">
                <label class="label" for="gName">Nom du groupe</label>
                <input class="input @error('name') is-err @enderror" id="gName" name="name" value="{{ old('name') }}" maxlength="100" required placeholder="Ex. Tech & SaaS Paris">
                @error('name')<p class="field-err">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label class="label" for="gDesc">Description</label>
                <textarea class="textarea" id="gDesc" name="description" maxlength="500" placeholder="À qui s'adresse ce groupe ?">{{ old('description') }}</textarea>
            </div>
            <div class="fgrid">
                <div class="field">
                    <label class="label" for="gSector">Secteur</label>
                    <select class="select filled" id="gSector" name="sector_id">
                        <option value="">Tous secteurs</option>
                        @foreach($sectors as $s)<option value="{{ $s->id }}" @selected((int) old('sector_id') === $s->id)>{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="label" for="gCity">Ville</label>
                    @if($isConsul)
                        <div class="select filled" style="cursor:default">{{ $me->city?->name ?? '—' }}<x-lx2-icon name="lock" /></div>
                    @else
                        <select class="select filled @error('city_id') is-err @enderror" id="gCity" name="city_id">
                            <option value="">Indifférent</option>
                            @foreach($cities as $c)<option value="{{ $c->id }}" @selected((int) old('city_id', $me->city_id) === $c->id)>{{ $c->name }}</option>@endforeach
                        </select>
                        @error('city_id')<p class="field-err">{{ $message }}</p>@enderror
                    @endif
                </div>
            </div>
            <div class="field">
                <span class="label">Couverture</span>
                <label class="drop" style="height:84px;cursor:pointer">
                    <input type="file" name="cover_photo" id="gCover" accept="image/png,image/jpeg,image/webp" hidden>
                    <span id="gCoverText"><x-lx2-icon name="plus" />Ajouter une photo <small style="color:var(--muted-fg)">· 2 Mo max</small></span>
                </label>
                @error('cover_photo')<p class="field-err">{{ $message }}</p>@enderror
                <input type="hidden" name="cover_color" id="gColor" value="{{ old('cover_color', $colors[0]) }}">
                <div class="swatches" id="gSwatches" style="margin-top:10px">
                    @foreach($colors as $i => $c)
                    <button type="button" class="sw {{ old('cover_color', $colors[0]) === $c ? 'on' : '' }}" style="background:{{ $c }}" data-color="{{ $c }}" aria-label="Couleur {{ $i + 1 }}">@if(old('cover_color', $colors[0]) === $c)<x-lx2-icon name="check" />@endif</button>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="df">
            <button type="button" class="btn btn-outline" data-close>Annuler</button>
            <button type="submit" class="btn btn-primary">Créer le groupe</button>
        </div>
    </form>
</div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    const $ = (id) => document.getElementById(id);
    let pane = @json($tab);

    function apply() {
        const q = $('groupSearch').value.toLowerCase().trim();
        let shown = 0;
        document.querySelectorAll('[data-pane-body]').forEach(p => {
            const on = p.dataset.paneBody === pane;
            p.hidden = !on;
            if (!on) return;
            p.querySelectorAll('.ecard').forEach(c => { const ok = !q || c.dataset.search.includes(q); c.hidden = !ok; if (ok) shown++; });
        });
        $('grpNoMatch').hidden = !q || shown > 0;
    }
    $('grpTabs').addEventListener('click', (e) => {
        const b = e.target.closest('[data-pane]');
        if (!b) return;
        pane = b.dataset.pane;
        document.querySelectorAll('#grpTabs .chip').forEach(c => c.classList.toggle('on', c === b));
        history.replaceState(null, '', '?tab=' + pane);
        apply();
    });
    $('groupSearch').addEventListener('input', apply);

    $('gSwatches')?.addEventListener('click', (e) => {
        const b = e.target.closest('[data-color]');
        if (!b) return;
        $('gColor').value = b.dataset.color;
        $('gSwatches').querySelectorAll('.sw').forEach(x => { x.classList.toggle('on', x === b); x.innerHTML = x === b ? @json(\App\Support\Lx2Icons::svg('check')) : ''; });
    });
    $('gCover')?.addEventListener('change', (e) => {
        const f = e.target.files[0];
        if (!f) return;
        if (f.size > 2 * 1024 * 1024) { toast('Image trop lourde (2 Mo maximum).', 'error'); e.target.value = ''; return; }
        $('gCoverText').textContent = f.name;
    });
})();
</script>
@endpush
