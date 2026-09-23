{{-- Fenêtres du détail de lead (style maquette .overlay / .dialog) --}}

{{-- Accepter — rappel des obligations du receveur (CGU §7.5) --}}
<div class="overlay hidden" id="acceptDialog" data-dialog>
    <form class="dialog" method="POST" action="{{ route('leads.accept', $lead->id) }}">
        @csrf
        <div class="dh">
            <div><h3>Accepter ce lead</h3><p>En acceptant, vous vous engagez à (CGU §7.5) :</p></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db">
            <ul class="clean progress-list">
                <li class="done"><span class="c"><x-lx2-icon name="check" /></span>Utiliser ces informations uniquement à des fins professionnelles</li>
                <li class="done"><span class="c"><x-lx2-icon name="check" /></span>Ne pas revendre les données à des tiers</li>
                <li class="done"><span class="c"><x-lx2-icon name="check" /></span>Informer le prospect de l'origine de ses coordonnées lors du premier contact</li>
                <li class="done"><span class="c"><x-lx2-icon name="check" /></span>Respecter les droits RGPD du prospect</li>
            </ul>
        </div>
        <div class="df">
            <button type="button" class="btn btn-outline" data-close>Annuler</button>
            <button type="submit" class="btn btn-primary">Accepter le lead</button>
        </div>
    </form>
</div>

{{-- Noter le lead — 3 critères + type (règles de LeadController@rate) --}}
<div class="overlay hidden" id="rateDialog" data-dialog>
    <form class="dialog" method="POST" action="{{ route('leads.rate', $lead->id) }}" id="rateForm">
        @csrf
        <div class="dh">
            <div><h3>Noter le lead</h3><p>Votre avis alimente la réputation de l'expéditeur.</p></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db" style="display:flex;flex-direction:column;gap:14px">
            @foreach(['quality' => 'Qualité', 'relevance' => 'Pertinence', 'reactivity' => 'Réactivité'] as $field => $label)
            <div class="rate-row">
                <span class="label" style="margin:0">{{ $label }}</span>
                <div class="rate-stars" data-field="{{ $field }}" role="radiogroup" aria-label="{{ $label }}">
                    @for($s = 1; $s <= 5; $s++)
                    <button type="button" class="x" data-val="{{ $s }}" aria-label="{{ $s }} sur 5"><x-lx2-icon name="star" /></button>
                    @endfor
                </div>
                <input type="hidden" name="{{ $field }}" required>
            </div>
            @endforeach
            <div>
                <span class="label">Type de lead <span style="color:var(--muted-fg);font-weight:400">(facultatif)</span></span>
                <div class="toggle" id="leadTypeToggle">
                    @foreach(['MQL' => 'MQL', 'SQL' => 'SQL', 'SP' => 'SP'] as $k => $l)
                    <button type="button" data-v="{{ $k }}">{{ $l }}</button>
                    @endforeach
                </div>
                <input type="hidden" name="lead_type" id="leadTypeInput">
                <p class="help">MQL : Marketing Qualified · SQL : Sales Qualified · SP : Strategic Partner</p>
            </div>
            <p class="help" id="rateErr" style="color:var(--destructive);margin:0" hidden>Veuillez noter les 3 critères.</p>
        </div>
        <div class="df">
            <span class="help" style="margin:0 auto 0 0;align-self:center">Moyenne : <b id="rateAvg">—</b></span>
            <button type="submit" class="btn btn-primary">Envoyer</button>
        </div>
    </form>
</div>

{{-- Signaler — motifs de LeadController@report --}}
<div class="overlay hidden" id="reportDialog" data-dialog>
    <form class="dialog" method="POST" action="{{ route('leads.report', $lead->id) }}">
        @csrf
        <div class="dh">
            <div><h3>Signaler ce lead</h3><p>Une pénalité de −1 point sera appliquée à l'expéditeur.</p></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db" style="display:flex;flex-direction:column;gap:10px">
            @foreach(['faux_profil' => 'Faux profil', 'lead_frauduleux' => 'Lead frauduleux', 'spam' => 'Spam', 'comportement_inapproprie' => 'Comportement inapproprié', 'autre' => 'Autre'] as $k => $l)
            <label class="radio"><input type="radio" name="fraud_reason" value="{{ $k }}" required><span class="dot"></span>{{ $l }}</label>
            @endforeach
        </div>
        <div class="df">
            <button type="button" class="btn btn-outline" data-close>Annuler</button>
            <button type="submit" class="btn btn-danger">Signaler</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const form = document.getElementById('rateForm');
    if (!form) return;
    const vals = {};

    form.querySelectorAll('.rate-stars').forEach(group => {
        group.addEventListener('click', (e) => {
            const b = e.target.closest('[data-val]');
            if (!b) return;
            const v = +b.dataset.val, f = group.dataset.field;
            vals[f] = v;
            group.parentElement.querySelector('input').value = v;
            group.querySelectorAll('[data-val]').forEach(x => x.classList.toggle('on', +x.dataset.val <= v));
            const n = Object.values(vals);
            document.getElementById('rateAvg').textContent = n.length === 3 ? (n.reduce((a, c) => a + c, 0) / 3).toFixed(1) + ' / 5' : '—';
        });
    });

    document.getElementById('leadTypeToggle').addEventListener('click', (e) => {
        const b = e.target.closest('[data-v]');
        if (!b) return;
        const input = document.getElementById('leadTypeInput');
        const same = input.value === b.dataset.v;
        input.value = same ? '' : b.dataset.v;
        document.querySelectorAll('#leadTypeToggle button').forEach(x => x.classList.toggle('on', !same && x === b));
    });

    form.addEventListener('submit', (e) => {
        if (Object.keys(vals).length < 3) {
            e.preventDefault();
            document.getElementById('rateErr').hidden = false;
        }
    });
})();
</script>
@endpush
