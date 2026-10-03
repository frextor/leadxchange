@props(['title', 'sub' => null, 'back' => null, 'tag' => null, 'tagClass' => 'b-plain'])
{{-- En-tête de page lx2 (maquette : header(title, sub, {back})) — titre, ville active, sous-titre, retour, icônes --}}
@php
    $lxCityId = auth()->check() ? session('selected_city_id', auth()->user()->city_id) : null;
    $lxCity   = $lxCityId ? \App\Models\City::find($lxCityId, ['id', 'name']) : null;
@endphp
<div class="ph">
    @if($back)
    <a class="back" href="{{ $back }}" aria-label="Retour"><x-lx2-icon name="arrow-left" /></a>
    @endif
    <div class="t">
        @if($title !== '')
        <h1>{{ $title }}@if($tag) <span class="badge {{ $tagClass }}" style="vertical-align:middle;margin-left:4px">{{ $tag }}</span>@endif
            @if(auth()->check())
                @if($lxCity)
                <a class="badge b-soft" href="{{ route('dashboard') }}" title="Changer de ville" style="vertical-align:middle;margin-left:6px;text-decoration:none">
                    <x-lx2-icon name="map-pin" />{{ $lxCity->name }}
                </a>
                @elseif($lxCityId === null)
                <a class="badge b-plain" href="{{ route('dashboard') }}" title="Changer de ville" style="vertical-align:middle;margin-left:6px;text-decoration:none">
                    <x-lx2-icon name="map-pin" />Toutes les villes
                </a>
                @endif
            @endif
        </h1>@endif
        @if($sub)<div class="sub">{{ $sub }}</div>@endif
    </div>
    {{ $slot }}
    @include('layouts.partials.lx2-header-icons')
</div>
