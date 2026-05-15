<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeadXchange — Échangez des leads, accélérez votre business</title>
    <meta name="description" content="LeadXchange est la plateforme B2B qui connecte les professionnels pour échanger des opportunités business, des leads qualifiés et construire un réseau solide.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }

        /* ── Animations ── */
        @keyframes fadeUp   { from { opacity:0; transform:translateY(30px) } to { opacity:1; transform:translateY(0) } }
        @keyframes fadeIn   { from { opacity:0 } to { opacity:1 } }
        @keyframes float    { 0%,100% { transform:translateY(0) } 50% { transform:translateY(-10px) } }
        @keyframes shimmer  { 0% { background-position:200% center } 100% { background-position:-200% center } }
        @keyframes spin-slow { to { transform:rotate(360deg) } }
        @keyframes pulse-ring { 0% { transform:scale(.8);opacity:1 } 100% { transform:scale(2);opacity:0 } }
        @keyframes slide-in-left  { from { opacity:0;transform:translateX(-40px) } to { opacity:1;transform:translateX(0) } }
        @keyframes slide-in-right { from { opacity:0;transform:translateX(40px)  } to { opacity:1;transform:translateX(0) } }
        @keyframes count-up { from { opacity:0;transform:translateY(10px) } to { opacity:1;transform:translateY(0) } }

        .animate-fade-up    { animation: fadeUp .7s ease-out both }
        .animate-fade-in    { animation: fadeIn .6s ease-out both }
        .animate-float      { animation: float 4s ease-in-out infinite }
        .animate-slide-left { animation: slide-in-left .7s ease-out both }
        .animate-slide-right{ animation: slide-in-right .7s ease-out both }
        .delay-100 { animation-delay:.1s } .delay-200 { animation-delay:.2s }
        .delay-300 { animation-delay:.3s } .delay-400 { animation-delay:.4s }
        .delay-500 { animation-delay:.5s } .delay-600 { animation-delay:.6s }

        /* ── Gradient text ── */
        .gradient-text {
            background: linear-gradient(135deg, #1E8F88 0%, #34d4bf 40%, #6366F1 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .gradient-text-gold {
            background: linear-gradient(135deg, #F59E0B 0%, #FCD34D 50%, #F59E0B 100%);
            background-size: 200% auto;
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; animation: shimmer 3s linear infinite;
        }

        /* ── Buttons ── */
        .btn-primary {
            background: #111827; color: white; padding: 14px 28px; border-radius: 14px;
            font-weight: 700; font-size: 15px; transition: all .2s;
            border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-primary:hover { background: #1F2937; transform: translateY(-2px); box-shadow: 0 12px 30px rgba(17,24,39,.25); }

        .btn-teal {
            background: linear-gradient(135deg, #1E8F88, #34d4bf); color: white;
            padding: 14px 28px; border-radius: 14px; font-weight: 700; font-size: 15px;
            transition: all .2s; border: none; cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-teal:hover { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(30,143,136,.35); }

        .btn-ghost {
            background: transparent; color: #111827; padding: 13px 24px; border-radius: 14px;
            font-weight: 600; font-size: 15px; transition: all .2s;
            border: 1.5px solid #E5E7EB; cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-ghost:hover { border-color: #111827; background: #F9FAFB; transform: translateY(-1px); }

        /* ── Cards ── */
        .feature-card {
            background: white; border-radius: 20px; padding: 28px; border: 1px solid #F3F4F6;
            transition: all .25s; position: relative; overflow: hidden;
        }
        .feature-card:hover {
            border-color: #1E8F88; transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(30,143,136,.12);
        }
        .feature-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #1E8F88, #6366F1);
            opacity: 0; transition: opacity .25s;
        }
        .feature-card:hover::before { opacity: 1; }

        /* ── Step connector ── */
        .step-line { position: absolute; top: 24px; left: calc(50% + 40px); width: calc(100% - 80px); height: 2px; background: linear-gradient(90deg, #1E8F88, #E5E7EB); }

        /* ── Noise texture overlay ── */
        .noise { position: relative; }
        .noise::after { content:''; position:absolute; inset:0; background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E"); pointer-events:none; border-radius:inherit; }

        /* ── Glow orbs ── */
        .orb { border-radius: 50%; filter: blur(80px); position: absolute; pointer-events: none; }

        /* ── Header scrolled ── */
        .header-scrolled { box-shadow: 0 4px 20px rgba(0,0,0,.08); background: rgba(255,255,255,.97); }

        /* ── Dashboard mockup ── */
        .mockup-card { background: white; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,.06); overflow: hidden; }

        /* ── Stat counter ── */
        .stat-number { font-size: 2.5rem; font-weight: 900; line-height: 1; }

        /* ── Marquee ── */
        @keyframes marquee { 0% { transform:translateX(0) } 100% { transform:translateX(-50%) } }
        .marquee-track { display:flex; width:max-content; animation: marquee 25s linear infinite; }
        .marquee-track:hover { animation-play-state: paused; }
    </style>
</head>
<body class="bg-white overflow-x-hidden">

{{-- ═══════════════════════════════════════════════════════════════
     HEADER
═══════════════════════════════════════════════════════════════ --}}
<header id="header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-white/80 backdrop-blur-md border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-black text-sm"
                 style="background:linear-gradient(135deg,#1E8F88,#34d4bf);">LX</div>
            <span class="font-bold text-[17px] text-gray-900 hidden sm:block">LeadXchange</span>
        </a>

        {{-- Nav links (desktop) --}}
        <nav class="hidden md:flex items-center gap-6">
            <a href="#features"   class="text-sm font-medium text-gray-500 hover:text-gray-900 transition">Fonctionnalités</a>
            <a href="#how"        class="text-sm font-medium text-gray-500 hover:text-gray-900 transition">Comment ça marche</a>
            <a href="#modules"    class="text-sm font-medium text-gray-500 hover:text-gray-900 transition">Modules</a>
        </nav>

        {{-- Auth buttons --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}"
               class="hidden sm:inline-flex text-sm font-semibold text-gray-700 px-4 py-2 rounded-xl hover:bg-gray-100 transition">
               Se connecter
            </a>
            <a href="{{ route('register') }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-white px-4 py-2 rounded-xl transition"
               style="background:#111827;" onmouseover="this.style.background='#1F2937'" onmouseout="this.style.background='#111827'">
                S'inscrire
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</header>

{{-- ═══════════════════════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════════════════════ --}}
<section class="relative min-h-screen flex items-center pt-16 overflow-hidden" style="background:linear-gradient(160deg,#F0FDFC 0%,#FAFAFA 50%,#EEF2FF 100%);">

    {{-- Background orbs --}}
    <div class="orb w-96 h-96" style="background:#1E8F88;opacity:.08;top:-100px;left:-100px;"></div>
    <div class="orb w-80 h-80" style="background:#6366F1;opacity:.06;bottom:-50px;right:-80px;"></div>
    <div class="orb w-64 h-64" style="background:#F59E0B;opacity:.05;top:40%;right:15%;"></div>

    <div class="max-w-7xl mx-auto px-6 py-20 w-full">
        <div class="grid lg:grid-cols-2 gap-16 items-center">

            {{-- Left: Text --}}
            <div>
                {{-- Badge --}}
                <div class="animate-fade-up inline-flex items-center gap-2 px-4 py-2 rounded-full mb-6 border border-teal-200"
                     style="background:linear-gradient(90deg,#E6F7F4,#EEF2FF);">
                    <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                    <span class="text-sm font-semibold text-teal-700">Plateforme B2B Professionnelle</span>
                </div>

                <h1 class="animate-fade-up delay-100 text-5xl lg:text-6xl font-black text-gray-900 leading-[1.05] mb-6 tracking-tight">
                    Échangez des leads,<br>
                    <span class="gradient-text">accélérez votre<br>business.</span>
                </h1>

                <p class="animate-fade-up delay-200 text-lg text-gray-500 leading-relaxed mb-8 max-w-lg">
                    La plateforme B2B dédiée à l'échange de leads qualifiés. Connectez-vous avec des professionnels de votre secteur, partagez vos opportunités et développez votre réseau business.
                </p>

                {{-- CTAs --}}
                <div class="animate-fade-up delay-300 flex flex-wrap gap-3 mb-10">
                    <a href="{{ route('register') }}" class="btn-teal">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Commencer gratuitement
                    </a>
                    <a href="#how" class="btn-ghost">
                        Comment ça marche
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                    </a>
                </div>

                {{-- Social proof --}}
                <div class="animate-fade-up delay-400 flex items-center gap-4">
                    <div class="flex -space-x-2">
                        @foreach(['#1E8F88','#6366F1','#F59E0B','#EF4444','#10B981'] as $c)
                        <div class="w-9 h-9 rounded-full border-2 border-white flex items-center justify-center text-white text-xs font-bold"
                             style="background:{{ $c }};">
                            {{ ['M','A','K','S','Y'][intval($loop->index)] }}
                        </div>
                        @endforeach
                    </div>
                    <div>
                        <div class="flex items-center gap-1 text-amber-400 text-sm">
                            @for($i=0;$i<5;$i++) ★ @endfor
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5"><strong class="text-gray-900">+500</strong> professionnels déjà inscrits</p>
                    </div>
                </div>
            </div>

            {{-- Right: Dashboard mockup --}}
            <div class="animate-slide-right delay-200 hidden lg:block">
                <div class="relative animate-float" style="animation-duration:5s;">

                    {{-- Main card --}}
                    <div class="bg-white rounded-3xl shadow-2xl p-6 border border-gray-100" style="transform:perspective(1200px) rotateY(-8deg) rotateX(4deg);">

                        {{-- Mini header --}}
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white font-bold text-xs" style="background:linear-gradient(135deg,#1E8F88,#34d4bf);">LX</div>
                                <span class="font-semibold text-sm text-gray-900">Dashboard</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-amber-400"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                            </div>
                        </div>

                        {{-- Stats row --}}
                        <div class="grid grid-cols-3 gap-3 mb-5">
                            @foreach([['12','Connexions','#E6F7F4','#1E8F88'],['8','Leads','#EEF2FF','#6366F1'],['3','Événements','#FEF3C7','#F59E0B']] as [$n,$l,$bg,$c])
                            <div class="rounded-2xl p-3 text-center" style="background:{{ $bg }};">
                                <p class="text-xl font-black" style="color:{{ $c }};">{{ $n }}</p>
                                <p class="text-[10px] font-semibold text-gray-500 mt-0.5">{{ $l }}</p>
                            </div>
                            @endforeach
                        </div>

                        {{-- Lead cards --}}
                        <div class="space-y-2.5">
                            @foreach([
                                ['Projet e-commerce retail','Sara M.','Web Dev','12k €','accepted'],
                                ['Refonte identité visuelle','Thomas B.','Design','8k €','new'],
                                ['Audit comptable PME','Claire H.','Finance','5k €','converted'],
                            ] as [$t,$s,$c,$b,$st])
                            @php
                                $stConf = ['accepted'=>['#ECFDF5','#10B981'],'new'=>['#EFF6FF','#3B82F6'],'converted'=>['#E6F7F4','#1E8F88']][$st];
                            @endphp
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 hover:bg-gray-100 transition">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                     style="background:linear-gradient(135deg,#1E8F88,#34d4bf);">
                                    {{ strtoupper(substr($s,0,1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-900 truncate">{{ $t }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $s }} · {{ $c }}</p>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-[10px] font-bold" style="color:#10B981;">{{ $b }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold" style="background:{{ $stConf[0] }};color:{{ $stConf[1] }};">
                                        {{ ucfirst($st) }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Floating notification --}}
                    <div class="absolute -top-4 -right-6 bg-white rounded-2xl shadow-xl p-3.5 flex items-center gap-3 border border-gray-100" style="min-width:200px;">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#E6F7F4;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="2.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-900">Nouveau lead reçu !</p>
                            <p class="text-[10px] text-gray-400">Marc vous a envoyé une opportunité</p>
                        </div>
                    </div>

                    {{-- Floating conversion badge --}}
                    <div class="absolute -bottom-4 -left-6 bg-white rounded-2xl shadow-xl p-3.5 flex items-center gap-3 border border-gray-100">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:#ECFDF5;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-900">Lead converti 🎉</p>
                            <p class="text-[10px] text-gray-400">+12 000 € de business</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     STATS BAR
═══════════════════════════════════════════════════════════════ --}}
<div class="border-y border-gray-100 bg-white py-10">
    <div class="max-w-5xl mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            @foreach([
                ['500+', 'Professionnels inscrits',  '#1E8F88'],
                ['1 200+','Leads échangés',           '#6366F1'],
                ['80+',  'Événements organisés',     '#F59E0B'],
                ['95%',  'Taux de satisfaction',     '#10B981'],
            ] as [$n,$l,$c])
            <div>
                <p class="stat-number mb-1" style="color:{{ $c }};">{{ $n }}</p>
                <p class="text-sm text-gray-500 font-medium">{{ $l }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     FEATURES
═══════════════════════════════════════════════════════════════ --}}
<section id="features" class="py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-6">

        <div class="text-center mb-16">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-semibold mb-4"
                 style="background:#E6F7F4;color:#1E8F88;">
                Tout ce dont vous avez besoin
            </div>
            <h2 class="text-4xl font-black text-gray-900 mb-4">Une plateforme, toutes vos opportunités</h2>
            <p class="text-lg text-gray-500 max-w-2xl mx-auto">LeadXchange centralise la gestion de vos leads, votre réseau professionnel et vos événements en une seule interface.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

            @php
            $features = [
                [
                    'icon'  => 'M17 1l4 4-4 4M3 11V9a4 4 0 0 1 4-4h14M7 23l-4-4 4-4M21 13v2a4 4 0 0 1-4 4H3',
                    'color' => '#6366F1', 'bg' => '#EEF2FF',
                    'title' => 'Échange de Leads',
                    'desc'  => 'Transmettez vos opportunités à la bonne personne. Chaque lead a un statut (Nouveau, Accepté, Converti) pour un suivi précis.',
                ],
                [
                    'icon'  => 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75',
                    'color' => '#1E8F88', 'bg' => '#E6F7F4',
                    'title' => 'Réseau Professionnel',
                    'desc'  => 'Connectez-vous avec des professionnels qualifiés dans votre secteur. Envoyez des demandes, développez votre network.',
                ],
                [
                    'icon'  => 'M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zM9 22V12h6v10',
                    'color' => '#F59E0B', 'bg' => '#FEF3C7',
                    'title' => 'Groupes Professionnels',
                    'desc'  => 'Rejoignez des groupes sectoriels pour échanger, collaborer et partager des ressources avec des pairs de votre domaine.',
                ],
                [
                    'icon'  => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z',
                    'color' => '#EC4899', 'bg' => '#FDF2F8',
                    'title' => 'Événements B2B',
                    'desc'  => 'Participez à des événements networking, workshops et webinaires. Rencontrez des décideurs et créez des opportunités concrètes.',
                ],
                [
                    'icon'  => 'M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16',
                    'color' => '#10B981', 'bg' => '#ECFDF5',
                    'title' => 'Profil & Vitrine',
                    'desc'  => 'Créez un profil professionnel complet : compétences, secteur, portfolio. Soyez visible par les bons décideurs.',
                ],
                [
                    'icon'  => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6 6 0 0 0-5-5.916V4a1 1 0 0 0-2 0v1.084A6 6 0 0 0 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 0 1-6 0v-1m6 0H9',
                    'color' => '#8B5CF6', 'bg' => '#EDE9FE',
                    'title' => 'Notifications Temps Réel',
                    'desc'  => 'Recevez des notifications instantanées pour chaque lead, demande de connexion ou événement. Ne manquez plus aucune opportunité.',
                ],
            ];
            @endphp

            @foreach($features as $f)
            <div class="feature-card">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4" style="background:{{ $f['bg'] }};">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="{{ $f['color'] }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="{{ $f['icon'] }}"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base mb-2">{{ $f['title'] }}</h3>
                <p class="text-sm text-gray-500 leading-relaxed">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     HOW IT WORKS
═══════════════════════════════════════════════════════════════ --}}
<section id="how" class="py-24 bg-white">
    <div class="max-w-6xl mx-auto px-6">

        <div class="text-center mb-16">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-semibold mb-4"
                 style="background:#EEF2FF;color:#6366F1;">
                Simple comme bonjour
            </div>
            <h2 class="text-4xl font-black text-gray-900 mb-4">Démarrez en 3 étapes</h2>
            <p class="text-lg text-gray-500 max-w-xl mx-auto">De la création de compte à votre premier échange de leads — en moins de 5 minutes.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 relative">
            {{-- Connector lines --}}
            <div class="hidden md:block absolute top-10 left-1/3 w-1/3 h-0.5" style="background:linear-gradient(90deg,#1E8F88,#6366F1);"></div>
            <div class="hidden md:block absolute top-10 left-2/3 w-1/3 h-0.5" style="background:linear-gradient(90deg,#6366F1,#F59E0B);"></div>

            @foreach([
                ['01', 'Créez votre profil',     '#1E8F88', '#E6F7F4', 'Inscrivez-vous en 2 minutes. Complétez votre profil : secteur, compétences, entreprise. Plus votre profil est complet, plus vous recevez de leads pertinents.', 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z'],
                ['02', 'Connectez-vous',          '#6366F1', '#EEF2FF', 'Parcourez les membres, envoyez des demandes de connexion et rejoignez des groupes de votre secteur. Votre réseau se construit naturellement.', 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75'],
                ['03', 'Échangez des leads',      '#F59E0B', '#FEF3C7', 'Envoyez des opportunités business à la bonne personne, acceptez des leads reçus et convertissez-les en projets réels. Votre business accélère.', 'M17 1l4 4-4 4M3 11V9a4 4 0 0 1 4-4h14M7 23l-4-4 4-4M21 13v2a4 4 0 0 1-4 4H3'],
            ] as [$num, $title, $color, $bg, $desc, $icon])
            <div class="relative text-center">
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6 relative"
                     style="background:{{ $bg }};">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="{{ $color }}" stroke-width="1.8">
                        <path d="{{ $icon }}"/>
                    </svg>
                    <span class="absolute -top-3 -right-3 w-7 h-7 rounded-xl text-xs font-black text-white flex items-center justify-center"
                          style="background:{{ $color }};">{{ $num }}</span>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-3">{{ $title }}</h3>
                <p class="text-sm text-gray-500 leading-relaxed max-w-xs mx-auto">{{ $desc }}</p>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-14">
            <a href="{{ route('register') }}" class="btn-teal">
                Je commence maintenant — c'est gratuit
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     MODULES SHOWCASE
═══════════════════════════════════════════════════════════════ --}}
<section id="modules" class="py-24 overflow-hidden" style="background:linear-gradient(160deg,#0F172A 0%,#111827 100%);">
    <div class="max-w-7xl mx-auto px-6">

        <div class="text-center mb-16">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-semibold mb-4"
                 style="background:rgba(30,143,136,.2);color:#34d4bf;">
                Plateforme complète
            </div>
            <h2 class="text-4xl font-black text-white mb-4">Tout pour votre croissance B2B</h2>
            <p class="text-lg max-w-2xl mx-auto" style="color:#94A3B8;">Un écosystème complet conçu pour les professionnels B2B ambitieux.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach([
                ['Exchanges', 'Échangez des leads qualifiés', '#1E8F88', 'M17 1l4 4-4 4M3 11V9a4 4 0 0 1 4-4h14M7 23l-4-4 4-4M21 13v2a4 4 0 0 1-4 4H3', 'ACTIF'],
                ['Réseau',    'Gérez vos connexions pro',     '#6366F1', 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8z',         'ACTIF'],
                ['Groupes',   'Rejoignez des communautés',    '#F59E0B', 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M17 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z',        'ACTIF'],
                ['Événements','Participez & organisez',       '#EC4899', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z', 'ACTIF'],
                ['Profil',    'Votre vitrine professionnelle','#10B981', 'M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z',            'ACTIF'],
                ['Marketplace','Offres & services B2B',       '#8B5CF6', 'M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z',                                          'Bientôt'],
                ['Inbox',     'Messagerie directe',           '#3B82F6', 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z',                      'Bientôt'],
                ['Analytics', 'Tableaux de bord avancés',    '#EF4444', 'M18 20V10M12 20V4M6 20v-6',                                                             'Bientôt'],
            ] as [$name,$desc,$color,$icon,$status])
            @php $isSoon = $status === 'Bientôt'; @endphp
            <div class="rounded-2xl p-5 border transition"
                 style="background:{{ $isSoon ? 'rgba(255,255,255,.03)' : 'rgba(255,255,255,.06)' }};border-color:{{ $isSoon ? 'rgba(255,255,255,.06)' : 'rgba(255,255,255,.1)' }};">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $color }}25;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $color }}" stroke-width="1.8">
                            <path d="{{ $icon }}"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full"
                          style="{{ $isSoon ? 'background:rgba(255,255,255,.08);color:#94A3B8;' : 'background:'.$color.'25;color:'.$color.';' }}">
                        {{ $status }}
                    </span>
                </div>
                <h4 class="font-bold text-sm mb-1" style="color:{{ $isSoon ? '#94A3B8' : 'white' }};">{{ $name }}</h4>
                <p class="text-xs leading-relaxed" style="color:{{ $isSoon ? '#475569' : '#94A3B8' }};">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     TESTIMONIAL / VALUE PROP
═══════════════════════════════════════════════════════════════ --}}
<section class="py-24 bg-white">
    <div class="max-w-6xl mx-auto px-6">

        <div class="text-center mb-14">
            <h2 class="text-4xl font-black text-gray-900 mb-4">Pourquoi LeadXchange ?</h2>
            <p class="text-lg text-gray-500 max-w-xl mx-auto">Une plateforme pensée pour la réalité du business B2B moderne.</p>
        </div>

        <div class="grid md:grid-cols-2 gap-6">

            {{-- Big value prop left --}}
            <div class="rounded-3xl p-8 text-white relative overflow-hidden noise"
                 style="background:linear-gradient(135deg,#0F172A,#1E293B);">
                <div class="orb w-48 h-48" style="background:#1E8F88;opacity:.15;top:-40px;right:-40px;filter:blur(50px);border-radius:50%;position:absolute;"></div>
                <div class="relative">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-5" style="background:rgba(30,143,136,.2);">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#34d4bf" stroke-width="1.8"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Concentré sur le ROI</h3>
                    <p class="text-sm leading-relaxed mb-6" style="color:#94A3B8;">
                        Chaque fonctionnalité est conçue pour générer de la valeur concrète : plus de leads qualifiés, plus de conversions, plus de business.
                    </p>
                    <div class="space-y-3">
                        @foreach(['Leads trackés du premier contact à la conversion','Réseau qualifié dans votre secteur','Événements qui génèrent des opportunités réelles'] as $item)
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0" style="background:rgba(30,143,136,.3);">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#34d4bf" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <span class="text-sm" style="color:#CBD5E1;">{{ $item }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Right column: 2 mini cards --}}
            <div class="space-y-6">
                <div class="rounded-3xl p-7 border border-gray-100 bg-gradient-to-br from-gray-50 to-white">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:#EEF2FF;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">Sécurisé & Privé</h3>
                            <p class="text-xs text-gray-400">Vos données restent les vôtres</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 leading-relaxed">Authentification sécurisée, données chiffrées, aucune revente d'informations. Votre réseau business vous appartient.</p>
                </div>

                <div class="rounded-3xl p-7 border border-gray-100 bg-gradient-to-br from-teal-50 to-white">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:#E6F7F4;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="1.8"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12" y2="18"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">Mobile First</h3>
                            <p class="text-xs text-gray-400">Application iOS & Android</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 leading-relaxed">Gérez vos leads et votre réseau depuis votre smartphone. Notifications push en temps réel pour ne rien manquer.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     CTA FINAL
═══════════════════════════════════════════════════════════════ --}}
<section class="py-24 relative overflow-hidden noise" style="background:linear-gradient(135deg,#0F172A 0%,#1E293B 50%,#0F172A 100%);">
    <div class="orb w-96 h-96" style="background:#1E8F88;opacity:.12;top:-100px;left:-100px;filter:blur(80px);border-radius:50%;position:absolute;"></div>
    <div class="orb w-80 h-80" style="background:#6366F1;opacity:.1;bottom:-80px;right:-80px;filter:blur(80px);border-radius:50%;position:absolute;"></div>

    <div class="max-w-4xl mx-auto px-6 text-center relative">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-semibold mb-6"
             style="background:rgba(30,143,136,.2);color:#34d4bf;">
            Rejoignez la communauté
        </div>
        <h2 class="text-5xl font-black text-white mb-6 leading-tight">
            Prêt à <span class="gradient-text">transformer</span><br>vos connexions en business ?
        </h2>
        <p class="text-lg mb-10" style="color:#94A3B8;">
            Rejoignez +500 professionnels qui utilisent déjà LeadXchange pour développer leur activité. Inscription gratuite, sans carte bancaire.
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('register') }}" class="btn-teal" style="font-size:16px;padding:16px 32px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Créer mon compte gratuitement
            </a>
            <a href="{{ route('login') }}" class="btn-ghost" style="font-size:16px;padding:15px 28px;border-color:rgba(255,255,255,.2);color:white;">
                J'ai déjà un compte
            </a>
        </div>

        {{-- Trust badges --}}
        <div class="flex flex-wrap justify-center gap-6 mt-12">
            @foreach(['✓ Inscription gratuite','✓ Sans engagement','✓ Notifications temps réel','✓ Support dédié'] as $badge)
            <span class="text-sm font-medium" style="color:#64748B;">{{ $badge }}</span>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════════════ --}}
<footer style="background:#0F172A;" class="py-12">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">

            {{-- Logo --}}
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-black text-sm"
                     style="background:linear-gradient(135deg,#1E8F88,#34d4bf);">LX</div>
                <span class="font-bold text-white text-base">LeadXchange</span>
            </div>

            {{-- Links --}}
            <div class="flex items-center gap-6">
                @foreach(['Se connecter' => route('login'), "S'inscrire" => route('register')] as $label => $href)
                <a href="{{ $href }}" class="text-sm transition" style="color:#64748B;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#64748B'">{{ $label }}</a>
                @endforeach
            </div>

            {{-- Copyright --}}
            <p class="text-sm" style="color:#475569;">
                &copy; {{ date('Y') }} LeadXchange. Tous droits réservés.
            </p>
        </div>
    </div>
</footer>

{{-- ═══════════════════════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════════════════════ --}}
<script>
    // Header scroll effect
    const header = document.getElementById('header');
    window.addEventListener('scroll', () => {
        header.classList.toggle('header-scrolled', window.scrollY > 20);
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const target = document.querySelector(a.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Intersection Observer for scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.feature-card').forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = `opacity .5s ease ${i * .07}s, transform .5s ease ${i * .07}s`;
        observer.observe(el);
    });
</script>
</body>
</html>
