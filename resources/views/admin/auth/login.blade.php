<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Console — LeadXchange Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin-auth.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @keyframes sa-spin { to { transform: rotate(360deg); } }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="sa-body">

<div class="sa-aurora sa-aurora--teal" aria-hidden="true"></div>
<div class="sa-aurora sa-aurora--violet" aria-hidden="true"></div>
<div class="sa-grid" aria-hidden="true"></div>

<x-admin.auth.card env="{{ app()->environment() }}">

    {{-- Header --}}
    <div class="sa-header">
        <div class="sa-brand">
            <div class="sa-brand__mark">LX</div>
            <div class="sa-brand__text">
                <span class="sa-brand__name">LeadXchange</span>
                <span class="sa-brand__sub">Console Admin</span>
            </div>
        </div>
        <span class="sa-role">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Super Admin
        </span>
    </div>

    <h1 class="sa-title">Accès <em>privilégié</em></h1>
    <p class="sa-subtitle">Identifiez-vous pour accéder à la console d'administration sécurisée.</p>

    <form class="sa-form" method="POST" action="{{ route('admin.login.post') }}"
          x-data="adminLogin()" @submit="handleSubmit()" novalidate>
        @csrf

        {{-- Email --}}
        <div class="sa-field">
            <label class="sa-label" for="sa-email">Adresse email</label>
            <div class="sa-input-wrap">
                <span class="sa-input-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </span>
                <input id="sa-email" type="email" name="email"
                       class="sa-input @error('email') sa-input--error @enderror"
                       value="{{ old('email') }}" placeholder="admin@x-tensia.com"
                       autocomplete="username" autofocus required>
            </div>
            @error('email')
            <span class="sa-error">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
                {{ $message }}
            </span>
            @enderror
        </div>

        {{-- Password --}}
        <div class="sa-field">
            <label class="sa-label" for="sa-password">Mot de passe</label>
            <div class="sa-input-wrap">
                <span class="sa-input-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <input id="sa-password" x-ref="passwordInput"
                       :type="showPassword ? 'text' : 'password'"
                       name="password"
                       class="sa-input sa-input--pw @error('password') sa-input--error @enderror"
                       placeholder="••••••••••••" autocomplete="current-password" required>
                <button type="button" class="sa-pw-toggle"
                        :class="{ 'sa-pw-toggle--active': showPassword }"
                        @click="togglePassword()">
                    <svg x-show="!showPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg x-show="showPassword" x-cloak width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
            @error('password')
            <span class="sa-error">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
                {{ $message }}
            </span>
            @enderror
        </div>

        {{-- Trusted device --}}
        <label class="sa-check">
            <input type="checkbox" name="remember" class="sa-check__input">
            <span class="sa-check__label">Appareil de confiance</span>
        </label>

        {{-- General error --}}
        @if($errors->has('general'))
        <div class="sa-error" style="padding:10px 14px;background:var(--red-soft);border:1px solid rgba(224,92,108,.3);border-radius:9px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
            {{ $errors->first('general') }}
        </div>
        @endif

        {{-- Submit --}}
        <button type="submit" class="sa-btn" :disabled="submitting">
            <template x-if="!submitting">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </template>
            <template x-if="submitting">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:sa-spin .8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            </template>
            <span x-text="submitting ? 'Connexion…' : 'Continuer vers la vérification'"></span>
        </button>
    </form>

    {{-- 2FA notice --}}
    <div class="sa-2fa">
        <div class="sa-2fa__icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
        </div>
        <p class="sa-2fa__text">
            <strong>Vérification en deux étapes requise.</strong><br>
            Un code à 6 chiffres sera demandé via votre application d'authentification.
        </p>
    </div>

</x-admin.auth.card>

<script>
function adminLogin() {
    return {
        showPassword: false,
        submitting: false,
        togglePassword() { this.showPassword = !this.showPassword; this.$refs.passwordInput.focus(); },
        handleSubmit() { this.submitting = true; },
    };
}
</script>

</body>
</html>
