<!DOCTYPE html>
<html lang="fr" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{ $emailTitle ?? 'LeadXchange' }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body, table, td, p, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    body { background-color: #F3F4F6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; }
    img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
    a { color: #4F46E5; }
    .greeting { font-size: 18px; font-weight: 700; color: #111827; margin-bottom: 16px; }
    .text { font-size: 15px; line-height: 1.7; color: #374151; margin-bottom: 20px; }
    .divider { height: 1px; background-color: #E5E7EB; margin: 24px 0; }
    .btn { display:inline-block; background-color:#4338CA; color:#ffffff!important; text-decoration:none; padding:14px 28px; border-radius:8px; font-weight:600; font-size:14px; letter-spacing:0.3px; }
    .btn-teal { background-color:#0D9488; }
    .info-card { background:#F0FDFA; border:1px solid #99F6E4; border-radius:12px; padding:20px; margin:20px 0; }
    .info-card-blue { background:#EFF6FF; border-color:#BFDBFE; }
    .tag { display:inline-block; font-size:12px; padding:3px 10px; border-radius:99px; margin-right:6px; margin-bottom:4px; }
</style>
</head>
<body style="background-color:#F3F4F6; margin:0; padding:0;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F3F4F6;">
  <tr>
    <td align="center" style="padding: 32px 16px;">
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%;">

        {{-- Header --}}
        <tr>
          <td align="center" bgcolor="#4338CA" style="background-color:#4338CA; border-radius:16px 16px 0 0; padding:32px 40px;">
            <p style="font-size:24px; font-weight:800; color:#ffffff; letter-spacing:-0.5px; margin:0;">LeadXchange</p>
          </td>
        </tr>

        {{-- Body --}}
        <tr>
          <td bgcolor="#ffffff" style="background-color:#ffffff; padding:40px; border-radius:0 0 16px 16px;">
            @yield('content')
            <p style="font-size:12px; color:#9CA3AF; margin-top:24px; padding-top:24px; border-top:1px solid #E5E7EB;">
              Cet email a été envoyé automatiquement par la plateforme LeadXchange. Si vous n'êtes pas à l'origine de cette action, ignorez cet email.
            </p>
          </td>
        </tr>

        {{-- Footer --}}
        <tr>
          <td align="center" style="padding: 20px 0 0;">
            <p style="font-size:12px; color:#9CA3AF; margin:0;">
              © {{ date('Y') }} LeadXchange &nbsp;·&nbsp;
              <a href="{{ config('app.url') }}" style="color:#6366F1; text-decoration:none;">Accéder à la plateforme</a>
            </p>
            <p style="font-size:11px; color:#D1D5DB; margin:6px 0 0;">
              Vous recevez cet email car vous êtes inscrit(e) sur LeadXchange.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
