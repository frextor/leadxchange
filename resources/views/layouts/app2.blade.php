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
    <link rel="stylesheet" href="{{ asset('css/lx2.css') }}">
    @stack('styles')
    @stack('head')
</head>
<body>

@php
    $lx2Nav = [
        ['dashboard',        'Accueil',      'house'],
        ['leads.index',      'Leads',        'file-text'],
        ['events.index',     'Événements',   'calendar-days'],
        ['connections.index','Réseau',       'users-round'],
        ['groups.index',     'Groupes',      'users'],
        ['chat.index',       'Chat',         'message-circle'],
    ];
    $lx2Current = Route::currentRouteName();
    $lx2Initials = strtoupper(mb_substr(auth()->user()->first_name ?? '?', 0, 1) . mb_substr(auth()->user()->last_name ?? '', 0, 1));
@endphp

<div class="app">
    <aside class="sidebar">
        <a class="brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/brand/logo-mark.svg') }}" alt="">
            <span>LeadXchange</span>
        </a>
        <div class="nav-label">Platform</div>
        <nav class="nav">
            @foreach($lx2Nav as [$routeName, $label, $icon])
            <a href="{{ route($routeName) }}" class="{{ $lx2Current === $routeName ? 'on' : '' }}" title="{{ $label }}">
                <x-lx2-icon :name="$icon" />
                <span class="lbl">{{ $label }}</span>
            </a>
            @endforeach
        </nav>
        <a class="send-cta" href="{{ route('leads.index') }}" title="Envoyer un lead">
            <span class="lbl">Envoyer un lead</span>
            <span class="bub"><x-lx2-icon name="arrow-up-right" /></span>
        </a>
        <div class="spacer"></div>
        <a class="upgrade" href="{{ route('upgrade') }}" title="Upgrade Plan">
            <x-lx2-icon name="gem" /><span class="lbl">Upgrade Plan</span>
        </a>
    </aside>

    <main class="main"><div class="page">
        @yield('content')
    </div></main>
</div>

<nav class="bnav" aria-label="Navigation principale">
    <a href="{{ route('dashboard') }}" class="{{ $lx2Current === 'dashboard' ? 'on' : '' }}"><x-lx2-icon name="house" />Accueil</a>
    <a href="{{ route('leads.index') }}" class="{{ $lx2Current === 'leads.index' ? 'on' : '' }}"><x-lx2-icon name="file-text" />Leads</a>
    <a href="{{ route('leads.index') }}" class="mid" aria-label="Envoyer un lead"><span><x-lx2-icon name="lx-send" /></span></a>
    <a href="{{ route('events.index') }}" class="{{ $lx2Current === 'events.index' ? 'on' : '' }}"><x-lx2-icon name="calendar-days" />Événements</a>
    <a href="{{ route('connections.index') }}" class="{{ $lx2Current === 'connections.index' ? 'on' : '' }}"><x-lx2-icon name="users-round" />Réseau</a>
</nav>

@stack('scripts')
</body>
</html>
