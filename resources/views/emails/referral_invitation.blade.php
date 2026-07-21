@php
    $referrerName = $referrer->first_name . ' ' . $referrer->last_name;
    $deepLink     = 'x-tensia://register?referralToken=' . $token;
    $webLink      = config('app.url') . '/referral/' . $token;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation LeadXchange</title>
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
                            <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#1E8F88;text-transform:uppercase;letter-spacing:1px;">Invitation personnelle</p>
                            <h1 style="margin:0 0 20px;font-size:24px;font-weight:700;color:#0D2B45;line-height:1.3;">
                                {{ $referrerName }} t'invite à rejoindre LeadXchange
                            </h1>

                            <p style="margin:0 0 16px;font-size:15px;color:#4A5568;line-height:1.6;">
                                Rejoindre LeadXchange te permet d'avoir accès à une multitude de professionnels
                                qui peuvent te mettre en relation avec des clients finaux.
                            </p>

                            <p style="margin:0 0 28px;font-size:15px;color:#4A5568;line-height:1.6;">
                                Développe ton réseau, génère des leads qualifiés et accélère ta croissance grâce
                                à la communauté LeadXchange.
                            </p>

                            {{-- CTA --}}
                            <table cellpadding="0" cellspacing="0" style="margin:0 auto 28px;">
                                <tr>
                                    <td style="background:linear-gradient(135deg,#1E8F88,#0B6E6A);border-radius:12px;padding:0;">
                                        <a href="{{ $webLink }}"
                                           style="display:inline-block;padding:14px 36px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;border-radius:12px;">
                                            Rejoindre LeadXchange
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:12px;color:#9BA8B7;text-align:center;">
                                Si le bouton ne fonctionne pas,
                                <a href="{{ $webLink }}" style="color:#1E8F88;text-decoration:none;">clique ici</a>.
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
