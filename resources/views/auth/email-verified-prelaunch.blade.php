<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/logo-mark.svg') }}">
    <title>Email vérifié — LeadXchange</title>
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
        .check-wrap {
            width: 72px; height: 72px;
            border-radius: 50%;
            margin: 0 auto 28px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(52,211,153,.15);
            border: 1px solid rgba(52,211,153,.4);
        }
        .pill {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 5px 14px; border-radius: 999px;
            font-size: 12px; font-weight: 600;
            background: rgba(52,211,153,.15);
            color: #34D399;
            border: 1px solid rgba(52,211,153,.35);
            margin-bottom: 20px;
        }
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
            margin-bottom: 12px;
        }
        .brand { margin-top: 24px; font-size: 12px; color: rgba(230,235,255,.35); letter-spacing: .03em; }
    </style>
</head>
<body>
    <div class="card">
        <div class="check-wrap">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
        </div>

        <span class="pill">Email vérifié</span>

        <h1>Votre inscription est confirmée</h1>
        <p class="subtitle">Votre adresse email a bien été validée. Votre compte LeadXchange est prêt.</p>

        <div class="message-box">{{ $message }}</div>

        <p class="brand">LeadXchange</p>
    </div>
</body>
</html>
