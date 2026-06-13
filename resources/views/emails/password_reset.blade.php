@extends('emails.layout')
@php $emailTitle = 'Réinitialisation de mot de passe — LeadXchange'; @endphp

@section('content')
<p class="greeting">Bonjour {{ $recipientName }},</p>
<p class="text">Nous avons reçu une demande de réinitialisation de mot de passe pour votre compte LeadXchange. Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe.</p>

<div style="text-align:center;">
    <a href="{{ $resetUrl }}" class="btn">Réinitialiser mon mot de passe</a>
</div>

<p class="text" style="font-size:13px; color:#6B7280;">Ce lien expirera dans <strong>60 minutes</strong>. Si vous n'avez pas demandé de réinitialisation, ignorez cet email — votre mot de passe actuel reste inchangé.</p>

<div class="divider"></div>
<p style="font-size:12px; color:#9CA3AF;">Si le bouton ne fonctionne pas :<br>
<a href="{{ $resetUrl }}" style="color:#6366F1; word-break:break-all;">{{ $resetUrl }}</a></p>
@endsection
