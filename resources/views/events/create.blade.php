@extends('layouts.app2')

@section('title', 'Créer un événement — LeadXchange')

{{-- Écran « Créer un événement » — maquette LeadXchange WEB › pageCreateEvent() --}}

@php
    $colors   = ['#4154F4', '#EA580C', '#16A34A', '#E11D48', '#A855F7', '#65D63C', '#EAB308', '#D946EF'];
    $color    = old('cover_color', $colors[0]);
    $scope    = old('is_private') ? 'private' : 'regional';
    $type     = old('type', 'in_person');
    $pricing  = old('pricing', old('price') > 0 ? 'paid' : 'free');
    $isConsul = $user->isConsul();
    $err      = fn($k) => $errors->has($k) ? 'is-err' : '';
@endphp

@section('content')
<x-lx2-header title="Créer un événement" sub="Publiez un événement pour votre région ou un cercle privé" :back="route('events.index')" />

@if($errors->any())
<div class="lx2-flash b-hot" role="alert"><span>Vérifiez les champs signalés ci-dessous.</span></div>
@endif

<div class="form-layout">
    <form class="card" id="evForm" method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" style="padding:4px 24px 0">
        @csrf

        <section class="fsec">
            <div><h2>Informations générales</h2><p>Un titre clair et une description courte.</p></div>
            <div class="fgrid">
                <div class="field full">
                    <label class="label" for="evTitle">Titre de l'événement</label>
                    <input class="input {{ $err('title') }}" id="evTitle" name="title" value="{{ old('title') }}" maxlength="150" required placeholder="Titre de l'événement">
                    @error('title')<p class="field-err">{{ $message }}</p>@enderror
                </div>
                <div class="field full">
                    <label class="label" for="evDesc">Description</label>
                    <textarea class="textarea" id="evDesc" name="description" maxlength="1000" placeholder="Décrivez votre événement…">{{ old('description') }}</textarea>
                </div>
            </div>
        </section>

        <section class="fsec">
            <div><h2>Portée et type</h2><p>Régional : visible par les membres de votre région. Privé : sur invitation.</p></div>
            <div class="fgrid">
                <div class="field full">
                    <span class="label">Portée</span>
                    <input type="hidden" name="is_private" id="isPrivate" value="{{ $scope === 'private' ? 1 : 0 }}">
                    <div class="toggle" data-toggle="scope" style="max-width:320px">
                        <button type="button" class="{{ $scope === 'regional' ? 'on' : '' }}" data-v="regional">Régional</button>
                        <button type="button" class="{{ $scope === 'private' ? 'on' : '' }}" data-v="private">Privé</button>
                    </div>
                </div>
                <div class="field full">
                    <span class="label">Type</span>
                    <input type="hidden" name="type" id="evType" value="{{ $type }}">
                    <div class="toggle" data-toggle="type">
                        @foreach(['in_person' => 'En personne', 'virtual' => 'Virtuel', 'hybrid' => 'Hybride'] as $k => $l)
                        <button type="button" class="{{ $type === $k ? 'on' : '' }}" data-v="{{ $k }}">{{ $l }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="field full">
                    <label class="label" for="evCat">Catégorie</label>
                    <select class="select {{ old('category') ? 'filled' : '' }}" id="evCat" name="category">
                        <option value="">Toutes catégories</option>
                        @foreach($categoryLabels as $k => $l)<option value="{{ $k }}" @selected(old('category') === $k)>{{ $l }}</option>@endforeach
                    </select>
                </div>
                <p class="help full" id="privateHelp" @if($scope !== 'private') hidden @endif style="margin:0">
                    Les membres des groupes que vous administrez et qui portent le même nom que l'événement seront invités automatiquement.
                </p>
            </div>
        </section>

        <section class="fsec">
            <div><h2>Date et lieu</h2></div>
            <div class="fgrid">
                <div class="field full">
                    <label class="label" for="evStart">Date et heure de début</label>
                    <div class="phone" style="grid-template-columns:minmax(0,1fr) 130px">
                        <input class="input {{ $err('starts_at') }}" id="evStart" name="starts_at_date" type="date" value="{{ old('starts_at_date') }}" min="{{ now()->format('Y-m-d') }}" required>
                        <input class="input num" id="evStartT" name="starts_at_time" type="time" value="{{ old('starts_at_time', '10:30') }}" required>
                    </div>
                    @error('starts_at')<p class="field-err">{{ $message }}</p>@enderror
                </div>
                <div class="field full">
                    <label class="label" for="evEnd">Date et heure de fin</label>
                    <div class="phone" style="grid-template-columns:minmax(0,1fr) 130px">
                        <input class="input {{ $err('ends_at') }}" id="evEnd" name="ends_at_date" type="date" value="{{ old('ends_at_date') }}" min="{{ now()->format('Y-m-d') }}">
                        <input class="input num" id="evEndT" name="ends_at_time" type="time" value="{{ old('ends_at_time', '12:30') }}">
                    </div>
                    @error('ends_at')<p class="field-err">{{ $message }}</p>@enderror
                </div>
                <div class="field full">
                    <label class="label" for="evCity">Ville</label>
                    @if($isConsul)
                        <input type="hidden" name="city_id" value="{{ $user->city_id }}">
                        <div class="select filled" style="cursor:default"><span style="display:flex;gap:8px;align-items:center"><x-lx2-icon name="map-pin" />{{ $user->city?->name ?? '—' }}</span><x-lx2-icon name="lock" /></div>
                        <p class="help">En tant que Consul, vos événements sont créés dans votre ville.</p>
                    @else
                        <select class="select filled {{ $err('city_id') }}" id="evCity" name="city_id">
                            <option value="">Indifférent</option>
                            @foreach($cities as $c)<option value="{{ $c->id }}" @selected((int) old('city_id', $user->city_id) === $c->id)>{{ $c->name }}</option>@endforeach
                        </select>
                        @error('city_id')<p class="field-err">{{ $message }}</p>@enderror
                    @endif
                </div>
                <div class="field full" id="placeField" @if($type === 'virtual') hidden @endif>
                    <label class="label" for="evPlace">Lieu</label>
                    <div class="input-wrap"><x-lx2-icon name="map-pin" /><input class="input" id="evPlace" name="location" value="{{ old('location') }}" maxlength="255" placeholder="Adresse ou nom de lieu"></div>
                </div>
                <div class="field full" id="linkField" @if($type === 'in_person') hidden @endif>
                    <label class="label" for="evLink">Lien de réunion</label>
                    <div class="input-wrap"><x-lx2-icon name="video" /><input class="input {{ $err('meeting_link') }}" id="evLink" name="meeting_link" type="url" value="{{ old('meeting_link') }}" maxlength="500" placeholder="https://…"></div>
                    @error('meeting_link')<p class="field-err">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="fsec">
            <div><h2>Tarif et participants</h2></div>
            <div class="fgrid">
                <div class="field full">
                    <span class="label">Tarif</span>
                    <input type="hidden" name="pricing" id="pricing" value="{{ $pricing }}">
                    <div class="toggle" data-toggle="pricing" style="max-width:320px">
                        <button type="button" class="{{ $pricing === 'free' ? 'on' : '' }}" data-v="free">Gratuit</button>
                        <button type="button" class="{{ $pricing === 'paid' ? 'on' : '' }}" data-v="paid">Payant</button>
                    </div>
                </div>
                <div class="field" id="priceField" @if($pricing !== 'paid') hidden @endif>
                    <label class="label" for="evPrice">Prix</label>
                    <input class="input num {{ $err('price') }}" id="evPrice" name="price" type="number" min="0" step="0.01" value="{{ old('price') }}" placeholder="Prix en {{ currency_symbol() }}">
                    @error('price')<p class="field-err">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label class="label" for="evMax">Nombre max de participants</label>
                    <input class="input num {{ $err('max_attendees') }}" id="evMax" name="max_attendees" type="number" min="1" value="{{ old('max_attendees') }}" placeholder="Illimité">
                    @error('max_attendees')<p class="field-err">{{ $message }}</p>@enderror
                </div>
                <div class="field {{ $pricing === 'paid' ? 'full' : '' }}" id="sectorField">
                    <label class="label" for="evSector">Secteur</label>
                    <select class="select {{ old('sector_id') ? 'filled' : '' }}" id="evSector" name="sector_id">
                        <option value="">Tous secteurs</option>
                        @foreach($sectors as $s)<option value="{{ $s->id }}" @selected((int) old('sector_id') === $s->id)>{{ $s->name }}</option>@endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="fsec">
            <div><h2>Apparence</h2><p>Image de couverture ou couleur si aucune image.</p></div>
            <div class="fgrid">
                <div class="field full">
                    <span class="label">Image de couverture</span>
                    <label class="drop" id="coverDrop" style="cursor:pointer">
                        <input type="file" name="cover_image" id="coverInput" accept="image/png,image/jpeg,image/webp" hidden>
                        <span id="coverDropText"><x-lx2-icon name="plus" />Ajouter photo<br><small style="color:var(--muted-fg)">PNG ou JPG, 1600×900 recommandé · 2 Mo max</small></span>
                    </label>
                    @error('cover_image')<p class="field-err">{{ $message }}</p>@enderror
                </div>
                <div class="field full">
                    <span class="label">Couleur de couverture</span>
                    <input type="hidden" name="cover_color" id="coverColor" value="{{ $color }}">
                    <div class="swatches" id="swatches">
                        @foreach($colors as $i => $c)
                        <button type="button" class="sw {{ $color === $c ? 'on' : '' }}" style="background:{{ $c }}" data-color="{{ $c }}" aria-label="Couleur {{ $i + 1 }}">@if($color === $c)<x-lx2-icon name="check" />@endif</button>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <div class="sticky-foot" style="margin:0 -24px;border-radius:0 0 var(--radius-lg) var(--radius-lg)">
            <a class="btn btn-outline" href="{{ route('events.index') }}">Annuler</a>
            <button class="btn btn-primary" type="submit" id="evSubmit">Créer</button>
        </div>
    </form>

    <aside class="aside-sticky">
        <div style="font-size:13px;font-weight:500;color:var(--muted-fg)">Aperçu de la carte</div>
        <article class="card ecard" id="evPreview">
            <div class="cover" id="pvCover" style="background:linear-gradient(135deg,{{ $color }},{{ $color }}99)">
                <img id="pvImg" alt="" hidden>
                <span class="tl"><span class="badge b-plain" id="pvCat">Événement</span><span class="badge b-ok" id="pvPrice">Gratuit</span><span class="badge b-orange" id="pvPrivate" hidden>Privé</span></span>
            </div>
            <div class="body">
                <h3 id="pvTitle">Titre de l'événement</h3>
                <div class="when" id="pvWhen">Date à définir</div>
                <div class="meta"><span class="stack"><x-lx2-avatar :user="$user" :size="26" /></span><span>Participants · <span class="mode" id="pvMode">En personne</span></span></div>
                <div class="actions"><span class="btn btn-primary" style="flex:1">Rejoindre</span></div>
            </div>
        </article>
    </aside>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const modeLabels = { in_person: 'En personne', virtual: 'Virtuel', hybrid: 'Hybride' };
    const currency = @json(currency_symbol());
    const months = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

    document.querySelectorAll('[data-toggle]').forEach(group => group.addEventListener('click', (e) => {
        const b = e.target.closest('[data-v]');
        if (!b) return;
        group.querySelectorAll('button').forEach(x => x.classList.toggle('on', x === b));
        const v = b.dataset.v, kind = group.dataset.toggle;
        if (kind === 'scope')   { $('isPrivate').value = v === 'private' ? 1 : 0; $('privateHelp').hidden = v !== 'private'; }
        if (kind === 'type')    { $('evType').value = v; $('placeField').hidden = v === 'virtual'; $('linkField').hidden = v === 'in_person'; }
        if (kind === 'pricing') { $('pricing').value = v; $('priceField').hidden = v !== 'paid'; $('sectorField').classList.toggle('full', v === 'paid'); }
        preview();
    }));

    $('swatches').addEventListener('click', (e) => {
        const b = e.target.closest('[data-color]');
        if (!b) return;
        $('coverColor').value = b.dataset.color;
        $('swatches').querySelectorAll('.sw').forEach(x => { x.classList.toggle('on', x === b); x.innerHTML = x === b ? @json(\App\Support\Lx2Icons::svg('check')) : ''; });
        preview();
    });

    $('coverInput').addEventListener('change', (e) => {
        const f = e.target.files[0];
        if (!f) return;
        if (f.size > 2 * 1024 * 1024) { toast('Image trop lourde (2 Mo maximum).', 'error'); e.target.value = ''; return; }
        const url = URL.createObjectURL(f);
        $('pvImg').src = url; $('pvImg').hidden = false;
        $('coverDropText').innerHTML = '<b>' + f.name.replace(/[<>&]/g, '') + '</b><br><small style="color:var(--muted-fg)">Cliquer pour changer</small>';
    });

    function preview() {
        $('pvTitle').textContent = $('evTitle').value.trim() || "Titre de l'événement";
        const cat = $('evCat'); $('pvCat').textContent = cat.value ? cat.options[cat.selectedIndex].text : 'Événement';
        const paid = $('pricing').value === 'paid', price = parseFloat($('evPrice').value);
        $('pvPrice').textContent = paid && price > 0 ? price.toLocaleString('fr-FR') + ' ' + currency : 'Gratuit';
        $('pvPrice').className = 'badge ' + (paid && price > 0 ? 'b-soft' : 'b-ok');
        $('pvPrivate').hidden = $('isPrivate').value !== '1';
        $('pvMode').textContent = modeLabels[$('evType').value];
        const c = $('coverColor').value; $('pvCover').style.background = `linear-gradient(135deg,${c},${c}99)`;
        const d = $('evStart').value, t = $('evStartT').value;
        if (d) { const [y, m, dd] = d.split('-'); $('pvWhen').textContent = `${months[+m - 1]} ${+dd}, ${y}` + (t ? ` à ${t}` : ''); }
        else $('pvWhen').textContent = 'Date à définir';
    }
    $('evForm').addEventListener('input', preview);
    $('evForm').addEventListener('change', (e) => { if (e.target.tagName === 'SELECT') e.target.classList.toggle('filled', !!e.target.value); preview(); });
    $('evStart').addEventListener('change', () => { if (!$('evEnd').value) $('evEnd').value = $('evStart').value; });
    $('evForm').addEventListener('submit', (e) => {
        let ok = true;
        [$('evTitle'), $('evStart')].forEach(el => { const bad = !el.value.trim(); el.classList.toggle('is-err', bad); if (bad) ok = false; });
        if (!ok) { e.preventDefault(); document.querySelector('.is-err').scrollIntoView({ behavior: 'smooth', block: 'center' }); return; }
        $('evSubmit').disabled = true;
    });
    preview();
})();
</script>
@endpush
