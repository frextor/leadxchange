@extends('emails.layout')
@php $emailTitle = ($title ?? 'Notification') . ' — LeadXchange'; @endphp

@section('content')
<p class="greeting">Bonjour {{ $recipientName }},</p>

<p class="text">{{ $body }}</p>

@if(!empty($actionLabel) && !empty($actionUrl))
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 8px 0 24px;">
  <tr>
    <td align="center">
      <a href="{{ $actionUrl }}"
         target="_blank"
         style="display:inline-block; background-color:#4338CA; color:#ffffff !important; text-decoration:none; font-size:14px; font-weight:700; padding:14px 32px; border-radius:10px; mso-padding-alt:0; letter-spacing:0.3px;">
        <!--[if mso]><i style="letter-spacing:32px; mso-font-width:-100%; mso-text-raise:30pt;">&nbsp;</i><![endif]-->
        {{ $actionLabel }}
        <!--[if mso]><i style="letter-spacing:32px; mso-font-width:-100%;">&nbsp;</i><![endif]-->
      </a>
    </td>
  </tr>
</table>
@endif
@endsection
