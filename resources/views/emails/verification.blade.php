@extends('emails.layout')
@php $emailTitle = 'Vérifiez votre email — LeadXchange'; @endphp

@section('content')
<p class="greeting">Bonjour {{ $recipientName }} 👋</p>
<p class="text">Merci de vous être inscrit(e) sur <strong>LeadXchange</strong> ! Pour activer votre compte, veuillez vérifier votre adresse email en cliquant sur le bouton ci-dessous.</p>

<div style="text-align:center;">
    <a href="{{ $verificationUrl }}" class="btn">Vérifier mon adresse email</a>
</div>

<p class="text" style="font-size:13px; color:#6B7280;">Ce lien est valable pendant <strong>60 minutes</strong>. Si vous n'avez pas créé de compte, vous pouvez ignorer cet email.</p>

<div class="divider"></div>
<p style="font-size:12px; color:#9CA3AF;">Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
<a href="{{ $verificationUrl }}" style="color:#6366F1; word-break:break-all;">{{ $verificationUrl }}</a></p>
@endsection
