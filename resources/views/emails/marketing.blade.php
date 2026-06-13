@extends('emails.layout')
@php $emailTitle = ($headline ?? $subject) . ' — LeadXchange'; @endphp

@section('content')
<p class="greeting">{{ $recipientName }},</p>

@if(!empty($headline))
<p style="font-size:22px; font-weight:800; color:#111827; margin-bottom:12px;">{{ $headline }}</p>
@endif

<p class="text">{{ $body }}</p>

@if(!empty($ctaLabel) && !empty($ctaUrl))
<div style="text-align:center;">
    <a href="{{ $ctaUrl }}" class="btn">{{ $ctaLabel }}</a>
</div>
@endif

<div class="divider"></div>
<p style="font-size:11px; color:#9CA3AF; text-align:center;">
    Vous recevez cet email car vous êtes abonné(e) aux communications LeadXchange.<br>
    <a href="{{ config('app.url') }}/unsubscribe" style="color:#6366F1;">Se désabonner</a>
</p>
@endsection
