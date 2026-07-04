<!DOCTYPE html>
<html lang="fr" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<title>{{ $emailTitle ?? 'LeadXchange' }}</title>
<!--[if mso]>
<noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
<![endif]-->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,400;0,600;1,400;1,600&family=Geist:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet" type="text/css">
<style type="text/css">
/* ── Reset ─────────────────────────────────────────────────────── */
* { box-sizing: border-box; margin: 0; padding: 0; }
body, table, td, p, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
body { background-color: #F4F5F8; font-family: 'Geist', -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; }
img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; max-width: 100%; }
table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
a { color: #14A98C; }

/* ── Legacy classes ─────────────────────────────────────────────── */
.greeting { font-size: 18px; font-weight: 700; color: #0F1623; margin-bottom: 16px; font-family: 'Geist', -apple-system, sans-serif; }
.text     { font-size: 15px; line-height: 1.7; color: #2E3850; margin-bottom: 20px; font-family: 'Geist', -apple-system, sans-serif; }
.divider  { height: 1px; background-color: #E5E7EE; margin: 24px 0; }
.btn      { display: inline-block; background-color: #14A98C; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; letter-spacing: -0.01em; font-family: 'Geist', -apple-system, sans-serif; }
.btn-teal { background-color: #14A98C; }
.info-card      { background: #E6F4F0; border: 1px solid #A8DFCF; border-radius: 10px; padding: 20px; margin: 20px 0; }
.info-card-blue { background: #EFF6FF; border-color: #BFDBFE; }
.tag { display: inline-block; font-size: 12px; padding: 3px 10px; border-radius: 99px; margin-right: 6px; margin-bottom: 4px; }

/* ── Dark mode ─────────────────────────────────────────────────── */
@media (prefers-color-scheme: dark) {
  body, .email-bg { background-color: #0D1117 !important; }
  .email-card   { background-color: #161B22 !important; border-color: #30363D !important; }
  .email-header { background-color: #161B22 !important; }
  .email-body   { background-color: #161B22 !important; }
  .email-footer { background-color: #161B22 !important; }
  .lx-mark      { background-color: #F4F5F8 !important; }
  .lx-mark-text { color: #0F1623 !important; }
  .brand-name   { color: #E5E7EE !important; }
  .accent-bar   { background-color: #14A98C !important; }
  .greeting, h1, h2, h3 { color: #E5E7EE !important; }
  .text, p      { color: #9097AC !important; }
  .divider      { background-color: #30363D !important; }
  .info-card    { background: #0B2A25 !important; border-color: #0F6B55 !important; }
}
</style>
</head>
<body style="background-color:#F4F5F8;margin:0;padding:0;">

<table class="email-bg" width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="background-color:#F4F5F8;">
  <tr>
    <td align="center" style="padding:40px 16px;">

      {{-- Card --}}
      <table class="email-card" width="600" cellpadding="0" cellspacing="0" border="0" role="presentation"
             style="max-width:600px;width:100%;background-color:#ffffff;border:1px solid #E5E7EE;border-radius:14px;">

        {{-- Brand header --}}
        <tr>
          <td class="email-header" style="background-color:#ffffff;padding:32px 40px 0;border-radius:14px 14px 0 0;">
            <table cellpadding="0" cellspacing="0" border="0" role="presentation">
              <tr>
                <td class="lx-mark" width="28" height="28"
                    style="width:28px;height:28px;background-color:#0F1623;border-radius:5px;text-align:center;vertical-align:middle;">
                  <span class="lx-mark-text" style="font-family:'Geist',-apple-system,sans-serif;font-size:11px;font-weight:700;color:#ffffff;line-height:28px;display:block;text-align:center;">LX</span>
                </td>
                <td style="padding-left:10px;vertical-align:middle;">
                  <span class="brand-name" style="font-family:'Geist',-apple-system,sans-serif;font-size:14px;font-weight:600;color:#0F1623;letter-spacing:-0.01em;">LeadXchange</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        {{-- Teal accent bar --}}
        <tr>
          <td class="email-header" style="background-color:#ffffff;padding:16px 40px 0;">
            <table cellpadding="0" cellspacing="0" border="0" role="presentation" width="100%">
              <tr>
                <td class="accent-bar" style="height:3px;background-color:#14A98C;border-radius:1.5px;font-size:3px;line-height:3px;">&nbsp;</td>
              </tr>
            </table>
          </td>
        </tr>

        {{-- Content body --}}
        <tr>
          <td class="email-body" style="background-color:#ffffff;padding:36px 40px 40px;">
            @yield('content')
          </td>
        </tr>

        {{-- Footer --}}
        <tr>
          <td class="email-footer" style="background-color:#ffffff;padding:0 40px 32px;border-radius:0 0 14px 14px;">
            <table cellpadding="0" cellspacing="0" border="0" role="presentation" width="100%">
              <tr>
                <td style="height:1px;background-color:#E5E7EE;font-size:1px;line-height:1px;">&nbsp;</td>
              </tr>
            </table>
            <p style="font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:12px;color:#9097AC;text-align:center;margin:20px 0 0;">
              <a href="{{ config('app.url') }}" style="color:#6C7691;text-decoration:none;">Aide</a>
              &nbsp;·&nbsp;
              <a href="{{ config('app.url') }}/privacy" style="color:#6C7691;text-decoration:none;">Confidentialité</a>
              &nbsp;·&nbsp;
              <a href="{{ config('app.url') }}/contact" style="color:#6C7691;text-decoration:none;">Assistance</a>
            </p>
            <p style="font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:11px;color:#9097AC;text-align:center;margin:8px 0 0;">
              © {{ date('Y') }} LeadXchange — Tous droits réservés
            </p>
          </td>
        </tr>

      </table>
      {{-- End card --}}

    </td>
  </tr>
</table>

</body>
</html>
