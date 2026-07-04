@extends('emails.layout')
@php $emailTitle = ($title ?? 'Notification') . ' — LeadXchange'; @endphp

@section('content')

{{-- Overline label --}}
<p style="font-family:'JetBrains Mono','Courier New',monospace;font-size:11px;font-weight:500;color:#14A98C;letter-spacing:0.1em;text-transform:uppercase;margin:0 0 16px 0;">
  Notification
</p>

{{-- Display heading --}}
<h1 style="font-family:'Newsreader',Georgia,'Times New Roman',serif;font-size:30px;font-weight:400;font-style:italic;color:#0F1623;line-height:1.25;margin:0 0 24px 0;">
  {{ $title ?? 'Notification LeadXchange' }}
</h1>

{{-- Greeting + body --}}
<p style="font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:15px;line-height:1.7;color:#2E3850;margin:0 0 6px 0;">
  Bonjour <strong>{{ $recipientName }}</strong>,
</p>
<p style="font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:15px;line-height:1.7;color:#2E3850;margin:0 0 28px 0;">
  {!! $body ?? '' !!}
</p>

{{-- CTA button (VML bulletproof + standard) --}}
@if(!empty($actionLabel) && !empty($actionUrl))
<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin:0 0 28px 0;">
  <tr>
    <td align="center">
      <!--[if mso]>
      <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word"
        href="{{ $actionUrl }}" style="height:48px;v-text-anchor:middle;width:240px;"
        arcsize="17%" stroke="f" fillcolor="#14A98C">
        <w:anchorlock/>
        <center style="color:#ffffff;font-family:'Segoe UI',sans-serif;font-size:14px;font-weight:600;">
          {{ $actionLabel }}
        </center>
      </v:roundrect>
      <![endif]-->
      <!--[if !mso]><!-->
      <a href="{{ $actionUrl }}" target="_blank"
         style="display:inline-block;background-color:#14A98C;color:#ffffff !important;text-decoration:none;font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:14px;font-weight:600;letter-spacing:-0.01em;padding:14px 32px;border-radius:8px;">
        {{ $actionLabel }}
      </a>
      <!--<![endif]-->
    </td>
  </tr>
</table>
@endif

{{-- Reassurance note --}}
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
