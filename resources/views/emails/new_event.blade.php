@extends('emails.layout')
@php $emailTitle = 'Événement à venir — ' . ($event_title ?? 'LeadXchange'); @endphp

@section('content')

<p style="font-family:'JetBrains Mono','Courier New',monospace;font-size:11px;font-weight:500;color:#14A98C;letter-spacing:0.1em;text-transform:uppercase;margin:0 0 16px 0;">
  Événement
</p>

<h1 style="font-family:'Newsreader',Georgia,'Times New Roman',serif;font-size:30px;font-weight:400;font-style:italic;color:#0F1623;line-height:1.25;margin:0 0 24px 0;">
  {{ $event_title ?? 'Un événement vous attend' }}
</h1>

<p style="font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:15px;line-height:1.7;color:#2E3850;margin:0 0 6px 0;">
  Bonjour <strong>{{ $name }}</strong>,
</p>
<p style="font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:15px;line-height:1.7;color:#2E3850;margin:0 0 24px 0;">
  <strong>{{ $creator_name }}</strong> organise un événement dans votre région. Ne manquez pas cette opportunité de networking !
</p>

{{-- Event card --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation"
       style="background:#F0FDF9;border-radius:10px;margin:0 0 28px 0;">
  <tr>
    <td style="padding:20px 24px;">
      <p style="font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:16px;font-weight:700;color:#0F1623;margin:0 0 6px 0;">
        {{ $event_title }}
      </p>
      @if(!empty($event_description) && $event_description !== 'Aucune description.')
      <p style="font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:14px;line-height:1.6;color:#4B5563;margin:0 0 12px 0;">
        {{ $event_description }}
      </p>
      @endif
      <table cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin-top:8px;">
        <tr>
          <td style="padding-right:20px;padding-bottom:6px;">
            <p style="font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:12px;color:#6B7280;margin:0;">
              📅 {{ $starts_at }}
            </p>
          </td>
          <td style="padding-right:20px;padding-bottom:6px;">
            <p style="font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:12px;color:#6B7280;margin:0;">
              🖥 {{ $event_type }}
            </p>
          </td>
        </tr>
        <tr>
          @if(!empty($city) && $city !== 'Non précisé')
          <td style="padding-right:20px;">
            <p style="font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:12px;color:#6B7280;margin:0;">
              📍 {{ $city }}
            </p>
          </td>
          @endif
          <td>
            <p style="font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:12px;font-weight:600;color:#14A98C;margin:0;">
              {{ $price_label }}
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

{{-- CTA --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin:0 0 28px 0;">
  <tr>
    <td align="center">
      <!--[if mso]>
      <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word"
        href="{{ $event_url }}" style="height:48px;v-text-anchor:middle;width:240px;"
        arcsize="17%" stroke="f" fillcolor="#14A98C">
        <w:anchorlock/>
        <center style="color:#ffffff;font-family:'Segoe UI',sans-serif;font-size:14px;font-weight:600;">
          Voir l'événement
        </center>
      </v:roundrect>
      <![endif]-->
      <!--[if !mso]><!-->
      <a href="{{ $event_url }}" target="_blank"
         style="display:inline-block;background-color:#14A98C;color:#ffffff !important;text-decoration:none;font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:14px;font-weight:600;letter-spacing:-0.01em;padding:14px 32px;border-radius:8px;">
        Voir l'événement
      </a>
      <!--<![endif]-->
    </td>
  </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
  <tr>
    <td style="background-color:#E6F4F0;border-radius:8px;padding:14px 20px;">
      <p style="font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:13px;line-height:1.6;color:#0B6F5C;margin:0;">
        Cet email a été envoyé automatiquement par LeadXchange. Si vous pensez l'avoir reçu par erreur, ignorez-le ou contactez notre support.
      </p>
    </td>
  </tr>
</table>

@endsection
