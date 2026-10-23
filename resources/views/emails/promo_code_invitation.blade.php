<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Un accès Premium vous a été offert — LeadXchange</title>
</head>
<body style="margin:0;padding:0;background:#F0F4F8;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#F0F4F8;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06);">

                    {{-- Header --}}
                    <tr>
                        <td style="background:linear-gradient(135deg,#0D2B45,#0B6E6A);padding:32px 40px;text-align:center;">
                            <p style="margin:0;color:#ffffff;font-size:22px;font-weight:700;letter-spacing:-0.5px;">LeadXchange</p>
                            <p style="margin:6px 0 0;color:rgba(255,255,255,0.75);font-size:13px;">Le réseau des professionnels B2B</p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:36px 40px;">
                            <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#1E8F88;text-transform:uppercase;letter-spacing:1px;">Cadeau LeadXchange</p>
                            <h1 style="margin:0 0 20px;font-size:24px;font-weight:700;color:#0D2B45;line-height:1.3;">
                                {{ $premium_days }} jour{{ (int) $premium_days > 1 ? 's' : '' }} de Premium vous sont offerts
                            </h1>

                            <p style="margin:0 0 24px;font-size:15px;color:#4A5568;line-height:1.6;">
                                Utilisez le code ci-dessous sur votre compte LeadXchange pour profiter de toutes les fonctionnalités Premium gratuitement pendant {{ $premium_days }} jour{{ (int) $premium_days > 1 ? 's' : '' }}.
                            </p>

                            {{-- Code --}}
                            <table cellpadding="0" cellspacing="0" style="margin:0 auto 28px;">
                                <tr>
                                    <td style="background:#F0FDFA;border:2px dashed #1E8F88;border-radius:12px;padding:16px 36px;">
                                        <p style="margin:0;font-size:22px;font-weight:700;letter-spacing:3px;color:#0D2B45;font-family:'Courier New',monospace;text-align:center;">{{ $code }}</p>
                                    </td>
                                </tr>
                            </table>

                            {{-- CTA --}}
                            <table cellpadding="0" cellspacing="0" style="margin:0 auto 28px;">
                                <tr>
                                    <td style="background:linear-gradient(135deg,#1E8F88,#0B6E6A);border-radius:12px;">
                                        <a href="{{ $redeem_url }}"
                                           style="display:inline-block;padding:14px 36px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;border-radius:12px;">
                                            Activer mon code
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:12px;color:#9BA8B7;text-align:center;">
                                Ce code est à usage unique. Si vous n'avez pas encore de compte,
                                <a href="{{ config('app.url') }}/register" style="color:#1E8F88;text-decoration:none;">créez-en un</a> avant de l'activer.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#F0F4F8;padding:20px 40px;text-align:center;border-top:1px solid #E8EDF2;">
                            <p style="margin:0;font-size:11px;color:#9BA8B7;">
                                © {{ date('Y') }} LeadXchange. Tous droits réservés.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
