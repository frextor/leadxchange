{{-- resources/views/auth/reset-password.blade.php --}}
@extends('layouts.auth')

@section('title', 'Reset Password — LeadXchange')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-semibold text-gray-900" style="letter-spacing:-0.025em;">Set new password</h1>
        <p class="text-gray-500 mt-2" style="font-size:15px;">Choose a strong password for your account.</p>
    </div>

    <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        {{-- Email --}}
        <div>
            <input type="email" name="email" value="{{ old('email', $email) }}"
                placeholder="Work Email" autocomplete="email" required
                class="lx-input px-4 py-3.5 @error('email') lx-error @enderror">
            @error('email')
                <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- New Password --}}
        <div>
            <div class="relative">
                <input type="password" id="password-field" name="password"
                    placeholder="New Password" autocomplete="new-password" required
                    class="lx-input px-4 py-3.5 pr-12 @error('password') lx-error @enderror"
                    oninput="updateStrength()">
                <button type="button" onclick="togglePassword('password-field')"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
            {{-- Strength meter --}}
            <div class="mt-2 flex gap-1">
                <div id="s-1" class="flex-1 h-1 rounded-full transition-all" style="background:#E5E7EB;"></div>
                <div id="s-2" class="flex-1 h-1 rounded-full transition-all" style="background:#E5E7EB;"></div>
                <div id="s-3" class="flex-1 h-1 rounded-full transition-all" style="background:#E5E7EB;"></div>
                <div id="s-4" class="flex-1 h-1 rounded-full transition-all" style="background:#E5E7EB;"></div>
            </div>
            <p id="s-label" class="mt-1 text-xs text-gray-400 h-4"></p>
            @error('password')
                <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div>
            <div class="relative">
                <input type="password" id="password-confirm" name="password_confirmation"
                    placeholder="Confirm New Password" autocomplete="new-password" required
                    class="lx-input px-4 py-3.5 pr-12">
                <button type="button" onclick="togglePassword('password-confirm')"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit"
            class="gradient-button w-full text-white font-semibold py-4 rounded-xl uppercase tracking-wider transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
            Reset Password
        </button>
    </form>
@endsection

@section('below_card')
    <div class="text-center mt-6 text-sm text-gray-600">
        Remember your password?
        <a href="{{ route('login') }}" class="font-semibold" style="color:#1E8F88;">Sign in</a>
    </div>
@endsection

@push('scripts')
<script>
    function togglePassword(id) {
        const f = document.getElementById(id);
        f.type = f.type === 'password' ? 'text' : 'password';
    }

    function passwordScore(pw) {
        if (!pw) return 0;
        let s = 0;
        if (pw.length >= 8)  s++;
        if (pw.length >= 12) s++;
        if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) s++;
        if (/[0-9]/.test(pw) && /[^A-Za-z0-9]/.test(pw)) s++;
        return Math.min(s, 4);
    }

    function updateStrength() {
        const pw    = document.getElementById('password-field').value;
        const score = passwordScore(pw);
        const bgMap = { 0:'#E5E7EB', 1:'#EF4444', 2:'#F59E0B', 3:'#EAB308', 4:'#2BB6A3' };
        const labels = ['', 'Too weak', 'Weak', 'Good', 'Strong'];
        for (let i = 1; i <= 4; i++) {
            document.getElementById('s-' + i).style.background = i <= score ? bgMap[score] : bgMap[0];
        }
        document.getElementById('s-label').textContent = pw ? labels[score] : '';
    }
</script>
@endpush
