<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès restreint — LeadXchange</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #E2E8F0; padding: 24px;
        }
        .card { max-width: 480px; width: 100%; text-align: center; }
        .icon-wrap {
            width: 80px; height: 80px; border-radius: 24px;
            background: linear-gradient(135deg, #EF444422, #DC262622);
            border: 1px solid #EF444444;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 28px;
        }
        .flag { font-size: 48px; margin-bottom: 4px; }
        h1 { font-size: 26px; font-weight: 700; color: #F1F5F9; margin-bottom: 12px; }
        .subtitle { font-size: 15px; color: #94A3B8; line-height: 1.6; margin-bottom: 24px; }
        .country-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: #1E293B; border: 1px solid #334155;
            border-radius: 999px; padding: 6px 16px;
            font-size: 13px; color: #CBD5E1; margin-bottom: 32px;
        }
        .brand { font-size: 12px; color: #475569; }
        .brand strong { color: #64748B; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="1.6">
                <circle cx="12" cy="12" r="10"/>
                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
            </svg>
        </div>

        <h1>Accès restreint</h1>
        <p class="subtitle">
            LeadXchange n'est pas disponible dans votre région.<br>
            Ce service est actuellement limité à certains pays.
        </p>

        @if(!empty($location))
        <div class="country-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2"><circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 0-8 8c0 5.4 7.05 11.5 7.35 11.76a1 1 0 0 0 1.3 0C12.95 21.5 20 15.4 20 10a8 8 0 0 0-8-8z"/></svg>
            Localisation détectée : <strong>{{ $location }}</strong>
        </div>
        @endif

        <p class="brand">— <strong>LeadXchange</strong></p>
    </div>
</body>
</html>
