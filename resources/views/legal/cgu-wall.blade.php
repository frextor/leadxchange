@extends('layouts.app')

@section('title', 'Accepter les CGU — LeadXchange')

@push('styles')
<style>
.cgu-wall-card {
    max-width: 680px;
    margin: 0 auto;
}
.cgu-scroll-box {
    max-height: 320px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1rem;
    background: #f8fafc;
    font-size: 0.875rem;
    line-height: 1.6;
    color: #374151;
}
.cgu-scroll-box h2, .cgu-scroll-box h3 { font-weight: 600; margin-top: 0.75rem; }
</style>
@endpush

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="cgu-wall-card w-full">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="LeadXchange" class="h-10 mx-auto mb-3">
            <h1 class="text-2xl font-bold text-gray-800">Conditions Générales d'Utilisation</h1>
            <p class="text-gray-500 mt-1">Version {{ $version }} — Veuillez lire et accepter avant de continuer.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8">

            {{-- CGU content (loaded from Pages table or fallback) --}}
            @php
                $cguPage = \App\Models\Page::where('slug', 'cgu')->first();
            @endphp

            @if ($cguPage)
                <div class="cgu-scroll-box prose prose-sm max-w-none mb-6">
                    {!! $cguPage->content !!}
                </div>
            @else
                <div class="cgu-scroll-box mb-6">
                    <p>Les conditions générales d'utilisation de LeadXchange (version {{ $version }}) s'appliquent à l'utilisation de la plateforme.
                    Vous pouvez les consulter à tout moment depuis le menu <strong>Pages légales → CGU</strong>.</p>
                </div>
            @endif

            {{-- Acceptance form --}}
            <form method="POST" action="{{ route('cgu.accept') }}" id="cgu-form">
                @csrf

                <div class="flex items-start gap-3 mb-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <input type="checkbox" id="cgu_check" name="cgu_check" value="1"
                           class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 flex-shrink-0"
                           onchange="document.getElementById('cgu-submit').disabled = !this.checked">
                    <label for="cgu_check" class="text-sm text-gray-700 leading-relaxed cursor-pointer">
                        J'ai lu et j'accepte les
                        <a href="{{ route('legal.show', 'cgu') }}" target="_blank" class="text-blue-600 hover:underline font-medium">
                            Conditions Générales d'Utilisation
                        </a>
                        de LeadXchange (v{{ $version }}).
                    </label>
                </div>

                <button type="submit" id="cgu-submit" disabled
                        class="w-full py-3 px-6 rounded-xl font-semibold text-white transition-all
                               bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed">
                    ✓ J'accepte et je continue
                </button>
            </form>

            <p class="text-center text-xs text-gray-400 mt-4">
                En acceptant, vous confirmez avoir pris connaissance de nos conditions.
                <br>Pour toute question : <a href="mailto:contact@leadxchange.com" class="hover:underline">contact@leadxchange.com</a>
            </p>
        </div>

        {{-- Logout option --}}
        <div class="text-center mt-6">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 transition">
                    Se déconnecter
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
