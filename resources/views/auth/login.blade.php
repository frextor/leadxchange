{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.auth')

@section('title', 'Sign in — LeadXchange')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-semibold text-gray-900" style="letter-spacing:-0.025em;">Welcome back</h1>
        <p class="text-gray-500 mt-2" style="font-size:15px;">Sign in to continue exchanging leads.</p>
    </div>

    {{-- Session status --}}
    @if (session('status'))
        <div class="mb-4 p-3 rounded-xl text-sm" style="background:#E6F7F4;color:#1E8F88;border:1px solid #A8E2D9;">
            {{ session('status') }}
        </div>
    @endif

    <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
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

        {{-- Password --}}
        <div>
            <div class="relative">
                <input type="password" id="password-field" name="password"
                    placeholder="Password" autocomplete="current-password" required
                    class="lx-input px-4 py-3.5 pr-12 @error('password') lx-error @enderror">
                <button type="button" onclick="togglePassword('password-field')"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember + Forgot --}}
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}
                    class="w-4 h-4 rounded" style="accent-color:#2BB6A3;">
                <span class="text-sm text-gray-600">Remember me</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-medium" style="color:#1E8F88;">
                    Forgot password?
                </a>
            @endif
        </div>

        {{-- Submit --}}
        <button type="submit"
            class="gradient-button w-full text-white font-semibold py-4 rounded-xl uppercase tracking-wider transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
            Sign in
        </button>
    </form>

    {{-- Divider --}}
    <div style="display:flex;align-items:center;gap:12px;color:#9CA3AF;font-size:12px;text-transform:uppercase;letter-spacing:0.08em;margin:20px 0;">
        <span style="flex:1;height:1px;background:#E5E7EB;"></span>
        or
        <span style="flex:1;height:1px;background:#E5E7EB;"></span>
    </div>

    {{-- Social auth --}}
    <div>
        <a href="{{ Route::has('login.linkedin') ? route('login.linkedin') : '#' }}"
           class="flex items-center justify-center gap-2 py-3 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="#0A66C2">
                <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14ZM8.34 18.34V10H5.67v8.34h2.67Zm-1.34-9.5a1.55 1.55 0 1 0 0-3.09 1.55 1.55 0 0 0 0 3.09Zm12 9.5v-4.78c0-2.45-1.31-3.59-3.06-3.59-1.41 0-2.04.78-2.4 1.32V10h-2.66v8.34h2.66v-4.65c0-.25.02-.5.09-.68.2-.5.66-1.02 1.42-1.02 1 0 1.4.76 1.4 1.88v4.47H19Z"/>
            </svg>
            Continue with LinkedIn
        </a>
    </div>
@endsection

@section('below_card')
    <div class="text-center mt-6 text-sm text-gray-600">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-semibold" style="color:#1E8F88;">Sign up</a>
    </div>
@endsection

@push('scripts')
<script>
    function togglePassword(id) {
        const f = document.getElementById(id);
        f.type = f.type === 'password' ? 'text' : 'password';
    }
</script>
@endpush
