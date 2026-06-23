@props([
    'env'   => 'production',
])

<div class="sa-card">

    {{-- ── Security strip ───────────────────────────────────────── --}}
    <div class="sa-strip">
        <span class="sa-strip__label">
            <span class="sa-strip__dot"></span>
            SESSION SÉCURISÉE &nbsp;·&nbsp; TLS 1.3
        </span>
        <span class="sa-strip__env">ENV {{ strtoupper($env) }}</span>
    </div>

    {{-- ── Card body (slot) ─────────────────────────────────────── --}}
    <div class="sa-body-inner">
        {{ $slot }}
    </div>

    {{-- ── Footer ───────────────────────────────────────────────── --}}
    <div class="sa-foot">
        <a href="{{ url('/login') }}" class="sa-foot__link">
            Compte admin standard ?&nbsp;<span>Connexion classique</span>
        </a>
        <span class="sa-foot__ip">IP journalisée</span>
    </div>

</div>
