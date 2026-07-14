{{-- resources/views/layouts/auth.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        {{-- Logo --}}
        <a href="{{ route('login') }}" class="lx-auth-logo">
            <div class="lx-logo-mark"><span>LX</span></div>
            <span class="lx-logo-text">LeadXchange</span>
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
        <div class="lx-trust-bg-grid"></div>

        {{-- Tagline --}}
        <div class="lx-trust-head">
            <span class="lx-trust-pill">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M7 7h10l-3-3M17 17H7l3 3"/>
                </svg>
                B2B Lead Marketplace
            </span>
            <h2 class="lx-trust-title">
                Share a lead.<br>
                Receive a lead.<br>
                <span class="lx-trust-accent">Grow together.</span>
            </h2>
            <p class="lx-trust-sub">
                The platform built for Business Developers who believe in the give-to-get principle.
            </p>
        </div>

        {{-- Avatars + rating --}}
        <div class="lx-trust-row">
            <div class="lx-avatars">
                <div class="lx-avatar" style="background:linear-gradient(135deg,hsl(165,60%,60%),hsl(205,55%,45%));">C</div>
                <div class="lx-avatar" style="background:linear-gradient(135deg,hsl(195,60%,60%),hsl(235,55%,45%));">L</div>
                <div class="lx-avatar" style="background:linear-gradient(135deg,hsl(30,60%,60%),hsl(70,55%,45%));">S</div>
                <div class="lx-avatar" style="background:linear-gradient(135deg,hsl(220,60%,60%),hsl(260,55%,45%));">A</div>
                <div class="lx-avatar" style="background:linear-gradient(135deg,hsl(340,60%,60%),hsl(20,55%,45%));">T</div>
                <div class="lx-avatar lx-avatar-count">+1.5k</div>
            </div>
            <div class="lx-trust-meta">
                <div class="lx-trust-meta-strong">Trusted by 1,500+ Business Developers</div>
                <div class="lx-trust-meta-stars">
                    <span>
                        @for ($i = 0; $i < 5; $i++)
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="#FFC65C"><path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z"/></svg>
                        @endfor
                    </span>
                    4.8/5 &middot; 312 reviews
                </div>
            </div>
        </div>

        {{-- Companies --}}
        <div class="lx-trust-companies">
            <div class="lx-trust-eyebrow">Companies using LeadXchange</div>
            <div class="lx-trust-companies-grid">
                <div class="lx-trust-company" style="font-weight:600;letter-spacing:-0.03em;">Greenway</div>
                <div class="lx-trust-company" style="font-family:ui-monospace,Menlo,monospace;font-size:12px;letter-spacing:0.18em;">NORTHBEAM</div>
                <div class="lx-trust-company" style="font-weight:500;letter-spacing:-0.02em;">pivotlabs</div>
                <div class="lx-trust-company" style="font-weight:700;letter-spacing:0.1em;">VOLTIC</div>
                <div class="lx-trust-company" style="font-weight:600;letter-spacing:-0.04em;">Lyra&middot;</div>
                <div class="lx-trust-company" style="font-family:ui-monospace,Menlo,monospace;letter-spacing:-0.02em;">acme/</div>
            </div>
        </div>

        {{-- Testimonial --}}
        <div class="lx-trust-quote">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="rgba(93,223,200,0.55)" style="margin-bottom:-4px;flex-shrink:0;">
                <path d="M9 7H5a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h2v2a2 2 0 0 1-2 2H4v2h1a4 4 0 0 0 4-4V7zm10 0h-4a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h2v2a2 2 0 0 1-2 2h-1v2h1a4 4 0 0 0 4-4V7z"/>
            </svg>
            <p class="lx-trust-quote-text">
                In 3 months I received 24 qualified leads. The give-and-receive system replaced 80% of my cold prospecting.
            </p>
            <div class="lx-trust-quote-author">
                <div class="lx-avatar" style="width:36px;height:36px;background:linear-gradient(135deg,hsl(165,60%,60%),hsl(180,55%,45%));border:none;font-size:13px;flex-shrink:0;">C</div>
                <div>
                    <div class="lx-trust-author-name">Camille D.</div>
                    <div class="lx-trust-author-role">Head of Sales &middot; Greenway</div>
                </div>
            </div>
        </div>
    </aside>

</div>

@stack('scripts')
</body>
</html>
