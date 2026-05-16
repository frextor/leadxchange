{-- resources/views/auth/register.blade.php --}}
@extends('layouts.auth')

@section('title', 'Create your account — LeadXchange')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-semibold text-gray-900" style="letter-spacing:-0.025em;">Create your account</h1>
        <p class="text-gray-500 mt-2" style="font-size:15px;">Join 1,500+ Business Developers exchanging qualified leads.</p>
    </div>

    {{-- Progress indicator --}}
    <div class="flex items-center justify-center mb-8">
        <div class="flex items-center">
            <div id="step1-indicator"
                 class="flex items-center justify-center w-8 h-8 rounded-full text-white text-sm font-semibold transition-all"
                 style="background:#2BB6A3;">1</div>
            <div class="w-16 h-1 mx-2 rounded-full overflow-hidden" style="background:#E5E7EB;">
                <div id="progress-bar" class="h-1 transition-all duration-300" style="width:0%;background:#2BB6A3;"></div>
            </div>
            <div id="step2-indicator"
                 class="flex items-center justify-center w-8 h-8 rounded-full text-gray-500 text-sm font-semibold transition-all"
                 style="background:#E5E7EB;">2</div>
        </div>
    </div>

    <form id="registrationForm" action="{{ route('register') }}" method="POST">
        @csrf

        {{-- ─────────── STEP 1 ─────────── --}}
        <div id="step1" class="space-y-4 lx-fade-in">

            {{-- First Name --}}
            <div>
                <input type="text" id="first_name" name="first_name" placeholder="First Name"
                    value="{{ old('first_name') }}" required
                    class="lx-input px-4 py-3.5 @error('first_name') lx-error @enderror">
                @error('first_name') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Last Name --}}
            <div>
                <input type="text" id="last_name" name="last_name" placeholder="Last Name"
                    value="{{ old('last_name') }}" required
                    class="lx-input px-4 py-3.5 @error('last_name') lx-error @enderror">
                @error('last_name') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div>
                <input type="email" id="email" name="email" placeholder="Work Email"
                    value="{{ old('email') }}" required
                    class="lx-input px-4 py-3.5 @error('email') lx-error @enderror">
                @error('email') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Password --}}
            <div>
                <div class="relative">
                    <input type="password" id="password" name="password" placeholder="Create Password" required
                        class="lx-input px-4 py-3.5 pr-12 @error('password') lx-error @enderror"
                        oninput="updateStrength()">
                    <button type="button" onclick="togglePassword('password')"
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
                @error('password') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <div class="relative">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        placeholder="Confirm Password" required
                        class="lx-input px-4 py-3.5 pr-12">
                    <button type="button" onclick="togglePassword('password_confirmation')"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="button" onclick="goToStep2()"
                class="gradient-button w-full text-white font-semibold py-4 rounded-xl uppercase tracking-wider transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                Next
            </button>
        </div>

        {{-- ─────────── STEP 2 ─────────── --}}
        <div id="step2" class="space-y-4 hidden">

            {{-- Gender --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Gender</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative cursor-pointer">
                        <input type="radio" name="gender" value="male"
                            {{ old('gender') == 'male' ? 'checked' : '' }} class="peer sr-only">
                        <div class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl text-center text-gray-700 font-medium transition-all peer-checked:border-teal-500 peer-checked:text-teal-700 peer-checked:bg-teal-50"
                             style="background:#F9FAFB;">
                            Male
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="gender" value="female"
                            {{ old('gender') == 'female' ? 'checked' : '' }} class="peer sr-only">
                        <div class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl text-center text-gray-700 font-medium transition-all peer-checked:border-teal-500 peer-checked:text-teal-700"
                             style="background:#F9FAFB;">
                            Female
                        </div>
                    </label>
                </div>
                @error('gender') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Phone --}}
            <div>
                <x-phone-input
                    codeId="phone_country_code"
                    codeName="phone_country_code"
                    codeValue="{{ old('phone_country_code', '+212') }}"
                    phoneId="phone"
                    phoneName="phone"
                    phoneValue="{{ old('phone') }}"
                    inputClass="{{ $errors->has('phone') ? 'border-red-400' : '' }}"
                />
                @error('phone_country_code') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                @error('phone') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Nationality --}}
            <div>
                <select name="nationality_id"
                    class="lx-input px-4 py-3.5 @error('nationality_id') lx-error @enderror">
                    <option value="">Nationality</option>
                    @foreach($nationalities as $n)
                        <option value="{{ $n->id }}" {{ old('nationality_id') == $n->id ? 'selected' : '' }}>
                            {{ $n->flag }} {{ $n->country }}
                        </option>
                    @endforeach
                </select>
                @error('nationality_id') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- City of Living (searchable) --}}
            <div class="relative">
                <input type="text" id="city_search" placeholder="City of Residence" autocomplete="off"
                    value="{{ old('city_id') ? $cities->firstWhere('id', old('city_id'))?->name : '' }}"
                    class="lx-input px-4 py-3.5 @error('city_id') lx-error @enderror"
                    oninput="filterCities(this.value)" onfocus="showCityDropdown()" onblur="hideCityDropdown()">
                <input type="hidden" id="city_id" name="city_id" value="{{ old('city_id') }}">
                <div id="city_dropdown"
                     class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto hidden">
                    @foreach($cities as $city)
                        <button type="button"
                            class="city-option w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700 transition-colors"
                            data-id="{{ $city->id }}" data-name="{{ $city->name }}"
                            onmousedown="selectCity({{ $city->id }}, '{{ addslashes($city->name) }}')">
                            {{ $city->name }}
                        </button>
                    @endforeach
                </div>
                @error('city_id') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Birthday --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Birthday</label>
                <input type="date" name="birthday" value="{{ old('birthday') }}"
                    class="lx-input px-4 py-3.5 @error('birthday') lx-error @enderror">
                @error('birthday') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Terms --}}
            <div>
                <label class="flex items-start gap-3 cursor-pointer group">
                    <input type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }}
                        class="mt-1 w-5 h-5 rounded flex-shrink-0" style="accent-color:#2BB6A3;">
                    <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">
                        I agree to the
                        <a href="#" class="font-medium" style="color:#1E8F88;">Terms &amp; Conditions</a>
                        and
                        <a href="#" class="font-medium" style="color:#1E8F88;">Privacy Policy</a>
                    </span>
                </label>
                @error('terms') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="goToStep1()"
                    class="flex-1 font-semibold py-4 rounded-xl uppercase tracking-wider transition-all border border-gray-200 text-gray-700 hover:bg-gray-100"
                    style="background:#F3F4F6;">
                    Back
                </button>
                <button type="submit"
                    class="flex-1 gradient-button text-white font-semibold py-4 rounded-xl uppercase tracking-wider transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                    Submit
                </button>
            </div>
        </div>
    </form>
@endsection

@section('below_card')
    <div class="text-center mt-6 text-sm text-gray-600">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold" style="color:#1E8F88;">Sign in</a>
    </div>
@endsection

@push('scripts')
<div id="_reg_err" data-errors="{{ json_encode($errors->keys()) }}" hidden></div>
<script>
    window._registerErrors = JSON.parse(document.getElementById('_reg_err').dataset.errors || '[]');
</script>
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
        const pw    = document.getElementById('password').value;
        const score = passwordScore(pw);
        const bgMap = { 0:'#E5E7EB', 1:'#EF4444', 2:'#F59E0B', 3:'#EAB308', 4:'#2BB6A3' };
        const labels = ['', 'Too weak', 'Weak', 'Good', 'Strong'];
        for (let i = 1; i <= 4; i++) {
            document.getElementById('s-' + i).style.background = i <= score ? bgMap[score] : bgMap[0];
        }
        document.getElementById('s-label').textContent = pw ? labels[score] : '';
    }

    function goToStep2() {
        const fields = ['first_name', 'last_name', 'email', 'password', 'password_confirmation'];
        let ok = true;
        fields.forEach(id => {
            const el = document.getElementById(id);
            if (!el.value.trim()) { el.classList.add('lx-error'); ok = false; }
            else el.classList.remove('lx-error');
        });
        if (!ok) return;

        const pw  = document.getElementById('password').value;
        const pwc = document.getElementById('password_confirmation').value;
        if (pw !== pwc) {
            document.getElementById('password_confirmation').classList.add('lx-error');
            alert('Passwords do not match');
            return;
        }
        if (pw.length < 8) {
            document.getElementById('password').classList.add('lx-error');
            alert('Password must be at least 8 characters');
            return;
        }

        document.getElementById('step1').classList.add('hidden');
        const s2 = document.getElementById('step2');
        s2.classList.remove('hidden');
        s2.classList.add('lx-fade-in');

        document.getElementById('step2-indicator').style.background = '#2BB6A3';
        document.getElementById('step2-indicator').style.color = '#fff';
        document.getElementById('progress-bar').style.width = '100%';

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function goToStep1() {
        document.getElementById('step2').classList.add('hidden');
        const s1 = document.getElementById('step1');
        s1.classList.remove('hidden');
        s1.classList.add('lx-fade-in');
        setTimeout(() => s1.classList.remove('lx-fade-in'), 350);

        document.getElementById('step2-indicator').style.background = '#E5E7EB';
        document.getElementById('step2-indicator').style.color = '#6B7280';
        document.getElementById('progress-bar').style.width = '0%';

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function filterCities(q) {
        const lower = q.toLowerCase();
        document.querySelectorAll('.city-option').forEach(btn => {
            btn.style.display = btn.dataset.name.toLowerCase().includes(lower) ? '' : 'none';
        });
        document.getElementById('city_dropdown').classList.remove('hidden');
        if (!q) document.getElementById('city_id').value = '';
    }

    function showCityDropdown() {
        document.getElementById('city_dropdown').classList.remove('hidden');
    }

    function hideCityDropdown() {
        setTimeout(() => document.getElementById('city_dropdown').classList.add('hidden'), 150);
    }

    function selectCity(id, name) {
        document.getElementById('city_id').value = id;
        document.getElementById('city_search').value = name;
        document.getElementById('city_dropdown').classList.add('hidden');
    }

    // Auto-jump to step 2 on server-side validation errors
    if (window._registerErrors && window._registerErrors.length) {
        document.addEventListener('DOMContentLoaded', function () {
            const step2Fields = ['phone', 'gender', 'nationality_id', 'city_id', 'birthday', 'terms'];
            const hasStep2Errors = step2Fields.some(f => window._registerErrors.includes(f));
            const hasStep1Errors = ['first_name', 'last_name', 'email', 'password'].some(f => window._registerErrors.includes(f));
            if (hasStep2Errors && !hasStep1Errors) goToStep2();
        });
    }
</script>
@endpush
