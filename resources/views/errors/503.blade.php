<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance — LeadXchange</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Geist', system-ui, sans-serif;
            background: #0F172A;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }
        .glow-teal   { position: fixed; width: 500px; height: 500px; top: -150px; left: -100px; border-radius: 50%; background: radial-gradient(circle, rgba(20,169,140,.18) 0%, transparent 70%); pointer-events: none; }
        .glow-violet { position: fixed; width: 400px; height: 400px; bottom: -100px; right: -80px; border-radius: 50%; background: radial-gradient(circle, rgba(108,123,224,.14) 0%, transparent 70%); pointer-events: none; }
        .card {
            position: relative;
            z-index: 1;
            background: #1E293B;
            border: 1px solid #334155;
            border-radius: 24px;
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,.5);
        }
        .icon-wrap {
            width: 72px; height: 72px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(20,169,140,.2), rgba(20,169,140,.08));
            border: 1px solid rgba(20,169,140,.3);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 28px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 99px;
            background: rgba(20,169,140,.12);
            border: 1px solid rgba(20,169,140,.25);
            color: #2DD4B0;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .badge-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #2DD4B0;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse { 0%,100%{opacity:1}50%{opacity:.4} }
        h1 { font-size: 28px; font-weight: 700; color: #F1F5F9; margin-bottom: 12px; letter-spacing: -.3px; }
        .subtitle { font-size: 15px; color: #94A3B8; line-height: 1.6; margin-bottom: 32px; }
        .info-box {
            background: rgba(255,255,255,.04);
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 28px;
            text-align: left;
        }
        .info-row { display: flex; align-items: flex-start; gap: 12px; }
        .info-row + .info-row { margin-top: 14px; padding-top: 14px; border-top: 1px solid #1E293B; }
        .info-icon { width: 32px; height: 32px; border-radius: 9px; background: rgba(20,169,140,.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .info-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #64748B; margin-bottom: 2px; }
        .info-value { font-size: 13.5px; color: #CBD5E1; font-weight: 500; }
        .back-link { font-size: 13px; color: #64748B; }
        .back-link a { color: #2DD4B0; text-decoration: none; font-weight: 500; }
        .back-link a:hover { text-decoration: underline; }

        @media (max-width: 520px) {
            .card { padding: 32px 24px; }
            h1 { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="glow-teal"></div>
    <div class="glow-violet"></div>

    <div class="card">
        <div class="icon-wrap">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2DD4B0" stroke-width="1.6">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="M12 8v4M12 16h.01" stroke-width="2"/>
            </svg>
        </div>

        <div class="badge">
            <span class="badge-dot"></span>
            Maintenance en cours
        </div>

        <h1>Service temporairement indisponible</h1>
        <p class="subtitle">
            LeadXchange fait actuellement l'objet d'une maintenance programmée pour améliorer votre expérience.
            Le service sera rétabli très prochainement.
        </p>

        <div class="info-box">
            <div class="info-row">
                <div class="info-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2DD4B0" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                </div>
                <div>
                    <p class="info-label">Durée estimée</p>
                    <p class="info-value">{{ $message ?? 'Quelques minutes' }}</p>
                </div>
            </div>
            <div class="info-row">
                <div class="info-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2DD4B0" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 14a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.62 3h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 10.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 17.92z"/></svg>
                </div>
                <div>
                    <p class="info-label">Contact</p>
                    <p class="info-value">contact@leadxchange.com</p>
                </div>
            </div>
        </div>

        <p class="back-link">
            <a href="javascript:location.reload()">↻ Rafraîchir la page</a>
            &nbsp;·&nbsp;
            <a href="mailto:contact@leadxchange.com">Nous contacter</a>
        </p>
    </div>
</body>
</html>
