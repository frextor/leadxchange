<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/logo-mark.svg') }}">
    <title>@yield('title', 'LeadXchange')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    @if(config('firebase.api_key'))
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js"></script>
    @endif
    <link rel="stylesheet" href="{{ asset('css/lx2.css') }}">
    @stack('styles')
    @stack('head')
</head>
<body>

@php
    ['pendingLeadsCount' => $pendingLeadsCount, 'unreadChatCount' => $unreadChatCount, 'unreadNotifCount' => $unreadNotifCount]
        = \App\Support\NavCounts::forCurrentUser();

    // [route, libellé, icône, pattern "actif", compteur]
    $lx2Nav = [
        ['dashboard',         'Accueil',    'house',          'dashboard',     0],
        ['leads.index',       'Leads',      'file-text',      'leads.*',       $pendingLeadsCount],
        ['events.index',      'Événements', 'calendar-days',  'events.*',      0],
        ['connections.index', 'Réseau',     'users-round',    ['connections.*', 'profile.show'], 0],
        ['groups.index',      'Groupes',    'users',          'groups.*',      0],
        ['chat.index',        'Chat',       'message-circle', 'chat.*',        $unreadChatCount],
    ];
@endphp

<div class="app">
    <aside class="sidebar">
        <a class="brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/brand/logo-mark.svg') }}" alt="">
            <span>LeadXchange</span>
        </a>
        <div class="nav-label">Platform</div>
        <nav class="nav">
            @foreach($lx2Nav as [$routeName, $label, $icon, $pattern, $count])
            <a href="{{ route($routeName) }}" class="{{ request()->routeIs(...(array) $pattern) ? 'on' : '' }}" title="{{ $label }}">
                <x-lx2-icon :name="$icon" />
                <span class="lbl">{{ $label }}</span>
                @if($count > 0)<span class="count">{{ $count > 9 ? '9+' : $count }}</span>@endif
            </a>
            @endforeach
        </nav>
        <a class="send-cta" href="{{ route('leads.create') }}" title="Envoyer un lead">
            <span class="lbl">Envoyer un lead</span>
            <span class="bub"><x-lx2-icon name="arrow-up-right" /></span>
        </a>
        <div class="spacer"></div>
        <a class="upgrade" href="{{ route('upgrade') }}" title="Upgrade Plan">
            <x-lx2-icon name="gem" /><span class="lbl">Upgrade Plan</span>
        </a>
    </aside>

    <main class="main">
        @include('layouts.partials.banners')

        <div class="page">
            {{-- Messages flash (redirections vers cette page : achat de points, abonnement, onboarding…) --}}
            @foreach(['success' => 'b-ok', 'error' => 'b-hot', 'info' => 'b-soft'] as $flashKey => $flashClass)
                @if(session($flashKey) && !($flashKey === 'success' && str_contains(session('success', ''), 'vérification')))
                <div class="lx2-flash {{ $flashClass }}" role="status">
                    <span>{{ session($flashKey) }}</span>
                    <button type="button" onclick="this.parentElement.remove()" aria-label="Fermer"><x-lx2-icon name="x" /></button>
                </div>
                @endif
            @endforeach

            @yield('content')

        </div>
    </main>
</div>

<nav class="bnav" aria-label="Navigation principale">
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'on' : '' }}"><x-lx2-icon name="house" />Accueil</a>
    <a href="{{ route('leads.index') }}" class="{{ request()->routeIs('leads.*') ? 'on' : '' }}"><x-lx2-icon name="file-text" />Leads</a>
    <a href="{{ route('leads.create') }}" class="mid" aria-label="Envoyer un lead"><span><x-lx2-icon name="lx-send" /></span></a>
    <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'on' : '' }}"><x-lx2-icon name="calendar-days" />Événements</a>
    <a href="{{ route('connections.index') }}" class="{{ request()->routeIs('connections.*', 'profile.show') ? 'on' : '' }}"><x-lx2-icon name="users-round" />Réseau</a>
</nav>

{{-- Scripts partagés avec layouts/app.blade.php --}}
@include('layouts.partials.cookie-banner')
@include('layouts.partials.api-token')
@include('layouts.partials.nav-scripts')
@include('layouts.partials.firebase')
@include('layouts.partials.upgrade-modal')
@include('layouts.partials.sweetalert')

{{-- Après nav-scripts : remplace toast() et le rendu des notifications par la version lx2 --}}
@include('layouts.partials.lx2-overlays')

@stack('scripts')
</body>
</html>
