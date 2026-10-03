@extends('layouts.app2')

@section('title', 'Parrainage — LeadXchange')

{{-- Écran « Parrainage » — design lx2. Lien partageable, invitation par e-mail et suivi des filleuls. --}}

@php
    $shareKey        = 'share:' . $user->id;
    $emailReferrals  = $referrals->where('referred_email', '!=', $shareKey)->sortByDesc('created_at')->values();
    $registeredCount = $emailReferrals->where('status', 'registered')->count();
    $pendingCount    = $emailReferrals->count() - $registeredCount;
@endphp

@push('styles')
<style>
    .rf-grid{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:20px;align-items:start;margin-top:18px}
    .rf-col{display:flex;flex-direction:column;gap:18px;min-width:0}
    .rf-h{margin:0;font-size:15px;font-weight:600;letter-spacing:-.01em}
    .rf-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}
    .rf-help{margin:0;font-size:12.5px;color:var(--muted-fg);line-height:1.55}

    /* Bannière de récompense */
    .rf-hero{position:relative;overflow:hidden;color:#fff;background:var(--lx-accent-grad);border-radius:var(--radius-lg);padding:26px 28px;box-shadow:var(--shadow-md)}
    .rf-hero::before,.rf-hero::after{content:"";position:absolute;border-radius:999px;background:rgba(255,255,255,.1);pointer-events:none}
    .rf-hero::before{width:300px;height:300px;right:-100px;top:-160px}
    .rf-hero::after{width:160px;height:160px;right:200px;bottom:-120px}
    .rf-hero > *{position:relative}
    .rf-kicker{font-size:12px;font-weight:500;letter-spacing:.04em;text-transform:uppercase;opacity:.85}
    .rf-reward{font-size:28px;font-weight:600;letter-spacing:-.02em;line-height:1.15;margin-top:8px}
    .rf-hero p{margin:8px 0 0;font-size:14px;opacity:.9;max-width:52ch;line-height:1.55}
    .rf-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:22px}
    .rf-stat{background:rgba(255,255,255,.16);border-radius:var(--radius);padding:12px 14px}
    .rf-stat-n{font-size:24px;font-weight:600;letter-spacing:-.02em;line-height:1}
    .rf-stat-l{font-size:12px;opacity:.85;margin-top:6px}

    /* Lien et invitation */
    .rf-copy{display:flex;flex-direction:column;gap:8px}
    .rf-copy input{width:100%;min-width:0;height:40px;border:1px solid var(--input);border-radius:var(--radius);padding:0 12px;font-size:13px;color:var(--fg-2);background:var(--bg);outline:none;font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
    .rf-copy input:focus{border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-soft)}
    .rf-invite{display:flex;gap:8px;flex-wrap:wrap}
    .rf-invite .input{flex:1;min-width:200px}

    /* Étapes */
    .rf-step{display:flex;gap:12px;align-items:flex-start;padding:12px 0;border-top:1px solid var(--border)}
    .rf-step:first-of-type{border-top:0;padding-top:0}
    .rf-num{width:28px;height:28px;border-radius:999px;background:var(--primary-soft);color:var(--primary);display:grid;place-items:center;font-size:13px;font-weight:600;flex:none}
    .rf-step-t{font-size:14px;font-weight:500}

    /* Liste des invitations */
    .rf-row{display:flex;align-items:center;gap:12px;padding:13px 20px;border-top:1px solid var(--border)}
    .rf-row:first-child{border-top:0}
    .rf-row:hover{background:var(--bg)}
    .rf-mail{font-size:14px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0}
    .rf-date{font-size:12.5px;color:var(--muted-fg)}
    .rf-empty{display:flex;flex-direction:column;align-items:center;gap:8px;padding:36px 20px;text-align:center;color:var(--muted-fg);font-size:13.5px}

    @media (max-width:1020px){
        .rf-grid{grid-template-columns:minmax(0,1fr)}
    }
    @media (max-width:640px){
        .rf-hero{padding:22px 20px}
        .rf-reward{font-size:22px}
        .rf-stats{grid-template-columns:1fr 1fr 1fr;gap:8px}
        .rf-stat-n{font-size:20px}
    }
</style>
@endpush

@section('content')
<x-lx2-header title="Parrainage" sub="Invitez vos contacts à rejoindre LeadXchange" />

@if (session('success'))
<div class="lx2-flash b-ok" role="status" style="margin-top:18px"><span>{{ session('success') }}</span></div>
@endif
@if (session('error'))
<div class="lx2-flash b-hot" role="alert" style="margin-top:18px"><span>{{ session('error') }}</span></div>
@endif

<section class="rf-hero" style="margin-top:18px">
    <div class="rf-kicker">Programme de parrainage</div>
    <div class="rf-reward">5 points pour chaque filleul inscrit</div>
    <p>Chaque contact qui crée un compte avec votre lien ou votre invitation vous rapporte 5 points, crédités automatiquement.</p>
    <div class="rf-stats">
        <div class="rf-stat"><div class="rf-stat-n">{{ $emailReferrals->count() }}</div><div class="rf-stat-l">Invitations envoyées</div></div>
        <div class="rf-stat"><div class="rf-stat-n">{{ $registeredCount }}</div><div class="rf-stat-l">Filleuls inscrits</div></div>
        <div class="rf-stat"><div class="rf-stat-n">{{ $pendingCount }}</div><div class="rf-stat-l">En attente</div></div>
    </div>
</section>

<div class="rf-grid">

    {{-- ── Colonne principale : inviter et suivre ── --}}
    <div class="rf-col">

        <section class="card card-pad">
            <div class="rf-head" style="margin-bottom:6px">
                <h2 class="rf-h">Inviter par e-mail</h2>
            </div>
            <p class="rf-help" style="margin-bottom:14px">Un e-mail d’invitation personnalisé sera envoyé à votre contact.</p>

            <form method="POST" action="{{ route('referral.send') }}" class="rf-invite">
                @csrf
                <input type="email" name="email" required placeholder="contact@exemple.com" value="{{ old('email') }}"
                       class="input" aria-label="Adresse e-mail du contact">
                <button type="submit" class="btn btn-primary">
                    <x-lx2-icon name="lx-send" />Envoyer l’invitation
                </button>
            </form>
            @error('email')
                <p class="rf-help" style="color:var(--destructive);margin-top:8px">{{ $message }}</p>
            @enderror
        </section>

        <section class="card" style="overflow:hidden">
            <div class="rf-head" style="padding:18px 20px 0;margin-bottom:12px">
                <h2 class="rf-h">Mes invitations</h2>
                <span class="badge b-muted">{{ $emailReferrals->count() }}</span>
            </div>

            @if ($emailReferrals->isEmpty())
                <div class="rf-empty">
                    <x-lx2-icon name="user-plus" />
                    <span>Aucune invitation envoyée pour l’instant.</span>
                    <span class="help" style="margin:0">Envoyez votre première invitation ci-dessus.</span>
                </div>
            @else
                @foreach ($emailReferrals as $referral)
                    <div class="rf-row">
                        <span class="av-fb" style="width:36px;height:36px;font-size:13px;{{ $referral->status === 'registered' ? '' : 'background:var(--muted);color:var(--muted-fg)' }}">{{ strtoupper(substr($referral->referred_email, 0, 1)) }}</span>
                        <div style="flex:1;min-width:0">
                            <div class="rf-mail">{{ $referral->referred_email }}</div>
                            <div class="rf-date">Invité le {{ $referral->created_at->locale('fr')->isoFormat('D MMMM YYYY') }}</div>
                        </div>
                        @if ($referral->status === 'registered')
                            <span class="badge b-ok">Inscrit · +5 pts</span>
                        @else
                            <span class="badge b-muted">En attente</span>
                        @endif
                    </div>
                @endforeach
            @endif
        </section>
    </div>

    {{-- ── Colonne latérale : lien et mode d'emploi ── --}}
    <aside class="rf-col">

        <section class="card card-pad">
            <h2 class="rf-h" style="margin-bottom:6px">Votre lien de parrainage</h2>
            <p class="rf-help" style="margin-bottom:14px">Partagez-le sur vos réseaux, par message ou par e-mail.</p>
            <div class="rf-copy">
                <input id="referralLinkInput" type="text" readonly value="{{ $referralLink }}" aria-label="Votre lien de parrainage">
                <button type="button" class="btn btn-outline btn-block" id="copyBtn" onclick="copyLink()">
                    <x-lx2-icon name="share-2" />Copier
                </button>
            </div>
        </section>

        <section class="card card-pad">
            <h2 class="rf-h" style="margin-bottom:10px">Comment ça marche</h2>
            <div class="rf-step">
                <span class="rf-num">1</span>
                <div><div class="rf-step-t">Invitez un contact</div><p class="rf-help" style="margin-top:2px">Par lien ou par e-mail.</p></div>
            </div>
            <div class="rf-step">
                <span class="rf-num">2</span>
                <div><div class="rf-step-t">Il crée son compte</div><p class="rf-help" style="margin-top:2px">Via votre lien ou votre invitation.</p></div>
            </div>
            <div class="rf-step">
                <span class="rf-num">3</span>
                <div><div class="rf-step-t">Vous gagnez 5 points</div><p class="rf-help" style="margin-top:2px">Crédités automatiquement.</p></div>
            </div>
        </section>

    </aside>
</div>
@endsection

@push('scripts')
<script>
    function copyLink() {
        const input = document.getElementById('referralLinkInput');
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(function () {
            const btn = document.getElementById('copyBtn');
            const original = btn.innerHTML;
            btn.textContent = 'Lien copié ✓';
            setTimeout(() => { btn.innerHTML = original; }, 2000);
        });
    }
</script>
@endpush
