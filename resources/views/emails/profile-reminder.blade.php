@extends('emails.layout')

@php $emailTitle = 'Complétez votre profil LeadXchange'; @endphp

@section('content')

<p class="greeting">Bonjour {{ $user->first_name }} 👋</p>

<p class="text">
    Votre profil LeadXchange est <strong>incomplet</strong>. Un profil complet vous permet d'être mieux mis en avant dans le réseau,
    d'obtenir plus de connexions et d'échanger davantage de leads qualifiés.
</p>

{{-- Progress bar --}}
@php
    $pct = count(array_filter([
        !empty($user->first_name) && !empty($user->last_name),
        !empty($user->profile?->job_title),
        !is_null($user->company_id),
        !is_null($user->city_id),
        !empty($user->profile?->sector_ids),
        !empty($user->profile?->bio),
        !empty($user->phone),
        !empty($user->profile?->avatar),
        !empty($user->profile?->services_offered),
        !empty($user->profile?->looking_for),
    ])) * 10;
@endphp

<div style="background:#F3F4F6; border-radius:12px; padding:20px; margin:20px 0;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <span style="font-size:13px; font-weight:600; color:#374151;">Complétion du profil</span>
        <span style="font-size:20px; font-weight:800; color:#0D9488;">{{ $pct }}%</span>
    </div>
    <div style="background:#E5E7EB; border-radius:99px; height:8px; overflow:hidden;">
        <div style="background:linear-gradient(90deg,#2BB6A3,#0D9488); width:{{ $pct }}%; height:8px; border-radius:99px;"></div>
    </div>
</div>

{{-- Missing fields --}}
@if(count($missing) > 0)
<div class="info-card" style="background:#FFF7ED; border-color:#FED7AA;">
    <p style="font-size:13px; font-weight:700; color:#92400E; margin-bottom:12px; text-transform:uppercase; letter-spacing:0.05em;">
        Ce qui manque encore
    </p>
    @foreach($missing as $field)
    <div style="display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid #FED7AA;">
        <div style="width:28px; height:28px; background:#FFEDD5; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <span style="font-size:14px;">
                @switch($field['key'])
                    @case('name')        👤 @break
                    @case('job_title')   💼 @break
                    @case('company')     🏢 @break
                    @case('location')    📍 @break
                    @case('sector')      🏭 @break
                    @case('bio')         ✍️ @break
                    @case('phone')       📞 @break
                    @case('avatar')      📷 @break
                    @case('services')    🤝 @break
                    @case('looking_for') 🔍 @break
                    @default             ◉
                @endswitch
            </span>
        </div>
        <span style="font-size:14px; color:#374151; font-weight:500;">{{ $field['label'] }}</span>
    </div>
    @endforeach
</div>
@endif

<p class="text" style="margin-top:24px;">
    Cliquez ci-dessous pour compléter votre profil dès maintenant :
</p>

<div style="text-align:center; margin:28px 0;">
    <a href="{{ url('/profile/me') }}" class="btn btn-teal">
        Compléter mon profil →
    </a>
</div>

<div class="divider"></div>

<p style="font-size:13px; color:#9CA3AF; text-align:center;">
    Vous recevez cet email car vous êtes membre de LeadXchange.<br>
    <a href="{{ url('/') }}" style="color:#0D9488;">Visiter LeadXchange</a>
</p>

@endsection
