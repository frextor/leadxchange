<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $emailTitle ?? 'LeadXchange' }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { background: #F3F4F6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #1F2937; }
    .wrapper { max-width: 600px; margin: 32px auto; }
    .card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    .header { background: linear-gradient(135deg, #6366F1, #4338CA); padding: 32px 40px; text-align: center; }
    .header-logo { color: #fff; font-size: 22px; font-weight: 800; letter-spacing: -.5px; }
    .body { padding: 40px; }
    .greeting { font-size: 18px; font-weight: 700; color: #111827; margin-bottom: 12px; }
    .text { font-size: 15px; line-height: 1.7; color: #374151; margin-bottom: 20px; }
    .btn { display: inline-block; padding: 13px 28px; background: linear-gradient(135deg, #6366F1, #4338CA); color: #fff; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 14px; margin: 8px 0 24px; }
    .divider { height: 1px; background: #F3F4F6; margin: 24px 0; }
    .footer { padding: 24px 40px; text-align: center; font-size: 12px; color: #9CA3AF; }
    .footer a { color: #6366F1; text-decoration: none; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <div class="header-logo">LeadXchange</div>
        </div>
        <div class="body">
            @yield('content')
        </div>
    </div>
    <div class="footer">
        © {{ date('Y') }} LeadXchange · <a href="{{ config('app.url') }}">Accéder à la plateforme</a><br>
        Vous recevez cet email car vous êtes inscrit(e) sur LeadXchange.
    </div>
</div>
</body>
</html>
