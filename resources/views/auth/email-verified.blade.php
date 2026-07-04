<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Verified</title>
    <script>
        window.location = 'x-tensia://auth/email-verified';
        setTimeout(function() { window.location = '/dashboard'; }, 2500);
    </script>
</head>
<body style="font-family:sans-serif;text-align:center;padding:60px;background:#f0f4f8;color:#0D2B45">
    <div style="max-width:400px;margin:0 auto;background:#fff;padding:40px;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08)">
        <div style="font-size:48px;margin-bottom:16px">✅</div>
        <h2 style="margin:0 0 12px">Email verified!</h2>
        <p style="color:#9BA8B7;margin:0 0 24px">Opening the app… If nothing happens, <a href="/dashboard" style="color:#1E8F88">continue to dashboard</a>.</p>
    </div>
</body>
</html>
