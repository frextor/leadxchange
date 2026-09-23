@props(['user', 'size' => 36])
{{-- Avatar lx2 (maquette : av()) — photo si disponible, sinon initiales (respecte member_name) --}}
@if($user?->profile?->avatar)
    <img class="av" src="{{ $user->profile->avatar_url }}" alt="" width="{{ $size }}" height="{{ $size }}" style="width:{{ $size }}px;height:{{ $size }}px">
@else
    <span class="av-fb" style="width:{{ $size }}px;height:{{ $size }}px;font-size:{{ round($size * .36) }}px">{{ $user ? member_name($user, true) : '?' }}</span>
@endif
