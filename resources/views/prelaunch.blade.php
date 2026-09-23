<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/logo-mark.svg') }}">
    <title>Bientôt disponible — LeadXchange</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0B1140 0%, #1E2A99 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #E2E8F0;
            padding: 24px;
        }
        .card { max-width: 520px; width: 100%; text-align: center; }
        .logo-wrap {
            width: 72px; height: 72px;
            border-radius: 20px;
            margin: 0 auto 28px;
            overflow: hidden;
            box-shadow: 0 12px 30px -10px rgba(55,80,255,.5);
        }
        .logo-wrap img { width: 100%; height: 100%; display: block; }
        .pill {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 5px 14px; border-radius: 999px;
            font-size: 12px; font-weight: 600;
            background: rgba(52,211,153,.15);
            color: #34D399;
            border: 1px solid rgba(52,211,153,.35);
            margin-bottom: 20px;
        }
        .dot { width: 7px; height: 7px; border-radius: 50%; background: #34D399; animation: pulse 1.6s ease-in-out infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .35; } }
        h1 { font-size: 26px; font-weight: 700; color: #fff; margin-bottom: 12px; letter-spacing: -.02em; }
        .subtitle { font-size: 15px; color: rgba(230,235,255,.75); line-height: 1.6; margin-bottom: 28px; }
        .message-box {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 16px;
            padding: 18px 22px;
            font-size: 14px;
            color: rgba(230,235,255,.85);
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .logout-link { font-size: 12.5px; color: rgba(230,235,255,.45); text-decoration: none; }
        .logout-link:hover { color: rgba(230,235,255,.75); }
        .brand { margin-top: 24px; font-size: 12px; color: rgba(230,235,255,.35); letter-spacing: .03em; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo-wrap"><img src="{{ asset('images/brand/logo-mark.svg') }}" alt="LeadXchange"></div>

        <span class="pill"><span class="dot"></span>Inscription confirmée</span>

        <h1>LeadXchange arrive bientôt</h1>

        @if(session('success'))
        <div class="message-box" style="background:rgba(52,211,153,.12);border-color:rgba(52,211,153,.35);color:#34D399;font-weight:600;margin-bottom:16px;">
            ✅ {{ session('success') }}
        </div>
        @endif

        <p class="subtitle">La plateforme n'est pas encore ouverte au public. Votre compte est bien créé — vous serez parmi les premiers avertis dès l'ouverture officielle.</p>

        <div class="message-box">{{ $message }}</div>

        @auth
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-link" style="background:none;border:none;cursor:pointer;font-family:inherit;">Se déconnecter</button>
        </form>
        @endauth

        <p class="brand">LeadXchange</p>
    </div>
</body>
</html>
