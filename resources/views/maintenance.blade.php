<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance — LeadXchange</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #E2E8F0;
            padding: 24px;
        }
        .card {
            max-width: 520px;
            width: 100%;
            text-align: center;
        }
        .icon-wrap {
            width: 80px; height: 80px;
            border-radius: 24px;
            background: linear-gradient(135deg, #F59E0B22, #D9770622);
            border: 1px solid #F59E0B44;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 28px;
        }
        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #F1F5F9;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }
        .subtitle {
            font-size: 15px;
            color: #94A3B8;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .message-box {
            background: #1E293B;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 20px 24px;
            font-size: 14px;
            color: #CBD5E1;
            line-height: 1.6;
            margin-bottom: 32px;
            text-align: left;
        }
        .brand {
            font-size: 13px;
            color: #475569;
            letter-spacing: 0.3px;
        }
        .brand strong {
            color: #64748B;
        }
        .dot {
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #F59E0B;
            margin-right: 8px;
            animation: pulse 1.5s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="1.6">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
        </div>

        <h1>Site en maintenance</h1>
        <p class="subtitle">Nous effectuons des opérations de maintenance pour améliorer votre expérience.</p>

        @if($message)
        <div class="message-box">
            <span class="dot"></span>{{ $message }}
        </div>
        @endif

        <p class="brand">Merci de votre patience — <strong>LeadXchange</strong></p>

        @if(isset($adminLoginUrl))
        <p style="margin-top:32px;">
            <a href="{{ $adminLoginUrl }}" style="font-size:11px;color:#334155;text-decoration:none;opacity:.5;">
                &#x1F512; Accès administrateur
            </a>
        </p>
        @endif
    </div>
</body>
</html>
