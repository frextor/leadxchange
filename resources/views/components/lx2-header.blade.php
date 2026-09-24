@props(['title', 'sub' => null, 'back' => null])
{{-- En-tête de page lx2 (maquette : header(title, sub, {back})) — titre, sous-titre, retour, icônes --}}
<div class="ph">
    @if($back)
    <a class="back" href="{{ $back }}" aria-label="Retour"><x-lx2-icon name="arrow-left" /></a>
    @endif
    <div class="t">
        @if($title !== '')<h1>{{ $title }}</h1>@endif
        @if($sub)<div class="sub">{{ $sub }}</div>@endif
    </div>
    {{ $slot }}
    @include('layouts.partials.lx2-header-icons')
</div>
