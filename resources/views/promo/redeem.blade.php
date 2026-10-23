@extends('layouts.app2')

@section('title', 'Code promo — LeadXchange')

{{-- Écran « J'ai un code promo » — design lx2. Saisie d'un code offert par l'admin pour profiter
     de Premium gratuitement pendant une durée définie. --}}

@section('content')
<x-lx2-header title="Code promo" sub="Activez un accès Premium offert" :back="route('dashboard')" />

@if (session('success'))
<div class="lx2-flash b-ok" role="status" style="margin-top:18px"><span>{{ session('success') }}</span></div>
@endif

<div class="card card-pad" style="max-width:440px;margin:24px auto 0;text-align:center">
    <span class="ring" style="width:56px;height:56px;margin:0 auto 16px;background:var(--primary-soft);color:var(--primary);display:grid;place-items:center;border-radius:999px">
        <x-lx2-icon name="gem" />
    </span>
    <h2 style="margin:0 0 6px;font-size:16px;font-weight:600">Vous avez un code promo ?</h2>
    <p style="margin:0 0 20px;font-size:13px;color:var(--muted-fg);line-height:1.6">
        Saisissez-le ci-dessous pour profiter de l'offre Premium pendant la durée offerte.
    </p>

    <form method="POST" action="{{ route('promo.redeem.submit') }}">
        @csrf
        <div class="field" style="text-align:left">
            <input class="input" type="text" name="code" id="code" maxlength="20" autocomplete="off"
                   placeholder="EX. AB12CD34" value="{{ old('code') }}"
                   style="text-transform:uppercase;text-align:center;letter-spacing:.1em;font-weight:600;font-size:16px"
                   @error('code') class="input lx-error" @enderror>
            @error('code')<p class="help" style="color:var(--destructive);margin-top:6px">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn btn-primary btn-block" style="margin-top:14px">
            <x-lx2-icon name="check" />Activer mon code
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('code').addEventListener('input', function () {
        this.value = this.value.toUpperCase();
    });
</script>
@endpush
