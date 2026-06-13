@extends('emails.layout')
@php $emailTitle = ($title ?? 'Notification') . ' — LeadXchange'; @endphp

@section('content')
<p class="greeting">Bonjour {{ $recipientName }},</p>

<p class="text">{{ $body }}</p>

@if(!empty($actionLabel) && !empty($actionUrl))
<div style="text-align:center;">
    <a href="{{ $actionUrl }}" class="btn">{{ $actionLabel }}</a>
</div>
@endif

<div class="divider"></div>
<p style="font-size:12px; color:#9CA3AF;">Cet email a été envoyé automatiquement par la plateforme LeadXchange.</p>
@endsection
