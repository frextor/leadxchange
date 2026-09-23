    <!-- §5.3 — Bandeau annonce maintenance programmée -->
    @php $maintenanceEnabled = \App\Models\SystemSetting::get('maintenance_banner_enabled', false); @endphp
    @if($maintenanceEnabled)
    <div class="flex items-center justify-between gap-4 px-4 py-2.5 text-sm flex-wrap"
         style="background:#1E293B; color:#CBD5E1;">
        <div class="flex items-center gap-2.5 flex-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#FBBF24" stroke-width="2" class="flex-shrink-0">
                <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <span style="color:#FBBF24; font-weight:600;">Maintenance programmée</span>
            <span>{{ \App\Models\SystemSetting::get('maintenance_banner_message', '') }}</span>
        </div>
        <a href="mailto:contact@leadxchange.com" class="text-xs font-semibold whitespace-nowrap" style="color:#2DD4B0;">
            Nous contacter →
        </a>
    </div>
    @endif

    <!-- §5.4 — Bannière licence Enterprise expirée (affichée 3 jours après expiration) -->
    @auth
    @php $expiredLicense = auth()->user()->recentlyExpiredEnterpriseLicense(); @endphp
    @if($expiredLicense)
    <div class="flex items-center justify-between gap-4 px-4 py-2.5 text-sm flex-wrap"
         style="background:#7F1D1D; color:#FEE2E2;">
        <div class="flex items-center gap-2.5 flex-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#FCA5A5" stroke-width="2" class="flex-shrink-0">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span style="color:#FCA5A5; font-weight:700;">Licence Enterprise expirée</span>
            <span style="color:#FECACA;">
                Votre licence <strong>{{ $expiredLicense->company_name }}</strong>
                a expiré le {{ \Carbon\Carbon::parse($expiredLicense->expires_at)->format('d/m/Y') }}.
                Les fonctionnalités Enterprise ne sont plus disponibles.
            </span>
        </div>
        <a href="mailto:contact@leadxchange.com"
           class="text-xs font-semibold whitespace-nowrap px-3 py-1.5 rounded-lg transition"
           style="background:#991B1B; color:#FEE2E2; hover:background:#7F1D1D;">
            Renouveler →
        </a>
    </div>
    @endif
    @endauth

    <!-- §3.3 CGU — Bannière mise à jour si utilisateur n'a pas accepté la version courante -->
    @auth
    @php
        $cguCurrentVersion = \App\Models\SystemSetting::get('cgu_current_version', '1.1');
        $userCguVersion    = auth()->user()->cgu_version;
        $cguNeedsAcceptance = $userCguVersion !== $cguCurrentVersion;
    @endphp
    @if($cguNeedsAcceptance)
    <div id="cgu-update-banner" class="bg-amber-50 border-b border-amber-200 px-4 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2" class="flex-shrink-0"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <p class="text-sm text-amber-800">
                    <span class="font-semibold">Nos Conditions Générales d'Utilisation ont été mises à jour (v{{ $cguCurrentVersion }}).</span>
                    La poursuite de l'utilisation vaut acceptation.
                    <a href="{{ url('/legal/cgu') }}" target="_blank" class="underline font-semibold ml-1">Lire les CGU →</a>
                </p>
            </div>
            <form method="POST" action="{{ route('cgu.accept') }}" class="flex-shrink-0">
                @csrf
                <button type="submit"
                        class="px-4 py-1.5 rounded-lg text-sm font-semibold text-white transition hover:opacity-90"
                        style="background:#D97706;">
                    J'accepte la v{{ $cguCurrentVersion }}
                </button>
            </form>
        </div>
    </div>
    @endif
    @endauth

    <!-- Email verification banner -->
    @auth
    @if(! auth()->user()->hasVerifiedEmail())
    <div class="sticky top-[72px] z-40 w-full" id="verify-banner">
        <div class="bg-amber-50 border-b border-amber-200 px-4 py-3">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2.2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-amber-900">Vérifiez votre adresse email</p>
                        <p class="text-xs text-amber-700 mt-0.5">
                            Un lien de confirmation a été envoyé à <strong>{{ auth()->user()->email }}</strong>.
                            Vérifiez votre boîte de réception (et les spams).
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 rounded-xl text-xs font-bold text-white transition hover:opacity-90"
                                style="background:linear-gradient(135deg,#F59E0B,#D97706);">
                            Renvoyer l'email
                        </button>
                    </form>
                    <button type="button" onclick="document.getElementById('verify-banner').remove()"
                            class="w-7 h-7 flex items-center justify-center rounded-full text-amber-500 hover:bg-amber-100 transition flex-shrink-0">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>
        @if(session('success') && str_contains(session('success', ''), 'vérification'))
        <div class="bg-emerald-50 border-b border-emerald-200 px-4 py-2.5 text-center text-sm text-emerald-700 font-medium">
            {{ session('success') }}
        </div>
        @endif
    </div>
    @endif
    @endauth
