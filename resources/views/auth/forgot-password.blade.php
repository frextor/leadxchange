{{-- resources/views/auth/forgot-password.blade.php --}}
@extends('layouts.auth')

@section('title', 'Forgot Password — LeadXchange')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-semibold text-gray-900" style="letter-spacing:-0.025em;">Forgot your password?</h1>
        <p class="text-gray-500 mt-2" style="font-size:15px;">Enter your email and we'll send you a reset link.</p>
    </div>

    {{-- Success status --}}
    @if (session('status'))
        <div class="mb-6 p-4 rounded-xl flex items-start gap-3" style="background:#E6F7F4;color:#1E8F88;border:1px solid #A8E2D9;">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-medium">{{ session('status') }}</span>
        </div>
    @endif

    <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
        @csrf

        {{-- Email --}}
        <div>
            <input type="email" name="email" value="{{ old('email') }}"
                placeholder="Work Email" autocomplete="email" autofocus required
                class="lx-input px-4 py-3.5 @error('email') lx-error @enderror">
            @error('email')
                <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <button type="submit"
            class="gradient-button w-full text-white font-semibold py-4 rounded-xl uppercase tracking-wider transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
            Send Reset Link
        </button>
    </form>
@endsection

@section('below_card')
    <div class="text-center mt-6 text-sm text-gray-600">
        Remember your password?
        <a href="{{ route('login') }}" class="font-semibold" style="color:#3C55FD;">Sign in</a>
    </div>
@endsection
