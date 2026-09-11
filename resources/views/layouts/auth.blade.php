{{-- resources/views/layouts/auth.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="/images/brand/logo-mark.svg">
    <title>@yield('title', 'LeadXchange')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">

    @stack('head')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="lx-auth-body">

<div class="lx-auth-shell">

    {{-- ─────────── LEFT — form column ─────────── --}}
    <div class="lx-auth-left">

        {{-- Decorative background ellipse --}}
        <img src="{{ asset('images/auth/bg-ellipse.svg') }}" alt="" class="lx-auth-bg-ellipse" aria-hidden="true">

        {{-- Logo --}}
        <a href="{{ route('login') }}" class="lx-auth-logo lx-auth-logo-word">
            <img src="{{ asset('images/brand/logo-mark.svg') }}" alt="" class="lx-auth-logo-icon">
            <span>Lead</span><span class="lx-logo-plus">+</span><span>change</span>
        </a>

        {{-- Form (centered vertically) --}}
        <main class="lx-auth-form-wrap">
            @yield('content')
            @yield('below_card')
        </main>

        {{-- Footer --}}
        <footer class="lx-auth-footer">
            <span>&copy; {{ date('Y') }} LeadXchange</span>
            <span class="lx-auth-footer-links">
                <a href="{{ route('legal.show', 'cgu') }}" target="_blank">CGU</a>
                <a href="{{ route('legal.show', 'privacy') }}" target="_blank">Confidentialité</a>
            </span>
        </footer>
    </div>

    {{-- ─────────── RIGHT — dark trust panel ─────────── --}}
    <aside class="lx-auth-right">
        <div class="lx-trust-photo-overlay"></div>

        <div class="lx-trust-content">
            <h2 class="lx-trust-title">Fait pour développer votre réseau</h2>

            <ul class="lx-trust-checklist">
                <li>
                    <svg class="lx-check" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="3"><path d="m5 12 5 5L20 7"/></svg>
                    <div>
                        <p class="lx-trust-item-title">Tout au même endroit</p>
                        <p class="lx-trust-item-text">Pilotez vos leads de bout en bout&nbsp;: réception, échange, qualification et suivi.</p>
                    </div>
                </li>
                <li>
                    <svg class="lx-check" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="3"><path d="m5 12 5 5L20 7"/></svg>
                    <div>
                        <p class="lx-trust-item-title">Des leads qualifiés</p>
                        <p class="lx-trust-item-text">Recevez des leads SQL &amp; SP triés selon vos secteurs et vos marchés.</p>
                    </div>
                </li>
                <li>
                    <svg class="lx-check" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="3"><path d="m5 12 5 5L20 7"/></svg>
                    <div>
                        <p class="lx-trust-item-title">Un réseau de confiance</p>
                        <p class="lx-trust-item-text">Connectez-vous à des professionnels vérifiés et échangez vos leads en toute sérénité.</p>
                    </div>
                </li>
                <li>
                    <svg class="lx-check" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="3"><path d="m5 12 5 5L20 7"/></svg>
                    <div>
                        <p class="lx-trust-item-title">Événements &amp; groupes</p>
                        <p class="lx-trust-item-text">Rejoignez des groupes, créez vos événements et rencontrez les bons contacts.</p>
                    </div>
                </li>
            </ul>
        </div>
    </aside>

</div>

@stack('scripts')
</body>
</html>
