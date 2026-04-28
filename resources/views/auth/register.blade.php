<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create your account - LeadXchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-button {
            background: linear-gradient(135deg, #2BB6A3 0%, #1E8F88 100%);
        }
        .gradient-button:hover {
            background: linear-gradient(135deg, #1E8F88 0%, #176D67 100%);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-teal-400 to-teal-600 rounded-2xl mb-4">
                <span class="text-white text-2xl font-bold">LX</span>
            </div>
            <h2 class="text-gray-800 text-xl font-semibold">LeadXchange</h2>
        </div>

        <!-- Card Container -->
        <div class="bg-white rounded-3xl shadow-xl p-8">
            <h1 class="text-2xl font-bold text-gray-900 text-center mb-8">Create your account</h1>

            <!-- Progress Indicator -->
            <div class="flex items-center justify-center mb-8">
                <div class="flex items-center">
                    <div id="step1-indicator" class="flex items-center justify-center w-8 h-8 rounded-full bg-teal-500 text-white text-sm font-semibold">
                        1
                    </div>
                    <div class="w-16 h-1 bg-gray-200 mx-2">
                        <div id="progress-bar" class="h-1 bg-teal-500 transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <div id="step2-indicator" class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-500 text-sm font-semibold">
                        2
                    </div>
                </div>
            </div>

            <form id="registrationForm" action="{{ route('register') }}" method="POST">
                @csrf

                <!-- STEP 1: Account Information -->
                <div id="step1" class="step-content">
                    <!-- First Name -->
                    <div class="mb-4">
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            placeholder="First Name" 
                            value="{{ old('first_name') }}"
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all text-gray-700 placeholder-gray-400"
                        >
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div class="mb-4">
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name" 
                            placeholder="Last Name" 
                            value="{{ old('last_name') }}"
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all text-gray-700 placeholder-gray-400"
                        >
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="Work Email" 
                            value="{{ old('email') }}"
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all text-gray-700 placeholder-gray-400"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4 relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Create Password" 
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all text-gray-700 placeholder-gray-400 pr-12"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword('password')" 
                            class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        @error('password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6 relative">
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            placeholder="Confirm Password" 
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all text-gray-700 placeholder-gray-400 pr-12"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword('password_confirmation')" 
                            class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Next Button -->
                    <button 
                        type="button" 
                        onclick="goToStep2()" 
                        class="w-full gradient-button text-white font-semibold py-4 rounded-xl transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl"
                    >
                        NEXT
                    </button>
                </div>

                <!-- STEP 2: Profile Information -->
                <div id="step2" class="step-content hidden">
                    <!-- Gender -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Gender</label>
                        <div class="flex gap-4">
                            <label class="flex-1 relative">
                                <input 
                                    type="radio" 
                                    name="gender" 
                                    value="male" 
                                    {{ old('gender') == 'male' ? 'checked' : '' }}
                                    class="peer sr-only"
                                >
                                <div class="w-full px-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-xl cursor-pointer transition-all peer-checked:border-teal-500 peer-checked:bg-teal-50 text-center text-gray-700 font-medium">
                                    Male
                                </div>
                            </label>
                            <label class="flex-1 relative">
                                <input 
                                    type="radio" 
                                    name="gender" 
                                    value="female" 
                                    {{ old('gender') == 'female' ? 'checked' : '' }}
                                    class="peer sr-only"
                                >
                                <div class="w-full px-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-xl cursor-pointer transition-all peer-checked:border-teal-500 peer-checked:bg-teal-50 text-center text-gray-700 font-medium">
                                    Female
                                </div>
                            </label>
                        </div>
                        @error('gender')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- City of Birth -->
                    <div class="mb-4">
                        <input 
                            type="text" 
                            id="city_birth" 
                            name="city_birth" 
                            placeholder="City of Birth" 
                            value="{{ old('city_birth') }}"
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all text-gray-700 placeholder-gray-400"
                        >
                        @error('city_birth')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- City of Living -->
                    <div class="mb-4">
                        <input 
                            type="text" 
                            id="city_living" 
                            name="city_living" 
                            placeholder="City of Living" 
                            value="{{ old('city_living') }}"
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all text-gray-700 placeholder-gray-400"
                        >
                        @error('city_living')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Birthday -->
                    <div class="mb-6">
                        <input 
                            type="date" 
                            id="birthday" 
                            name="birthday" 
                            value="{{ old('birthday') }}"
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all text-gray-700"
                        >
                        @error('birthday')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="mb-6">
                        <label class="flex items-start cursor-pointer group">
                            <input 
                                type="checkbox" 
                                name="terms" 
                                value="1"
                                {{ old('terms') ? 'checked' : '' }}
                                class="mt-1 w-5 h-5 text-teal-500 border-gray-300 rounded focus:ring-teal-500 focus:ring-2"
                            >
                            <span class="ml-3 text-sm text-gray-600 group-hover:text-gray-900">
                                I agree to the <a href="#" class="text-teal-500 hover:text-teal-600 font-medium">Terms & Conditions</a> and <a href="#" class="text-teal-500 hover:text-teal-600 font-medium">Privacy Policy</a>
                            </span>
                        </label>
                        @error('terms')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Back & Submit Buttons -->
                    <div class="flex gap-3">
                        <button 
                            type="button" 
                            onclick="goToStep1()" 
                            class="flex-1 bg-gray-100 text-gray-700 font-semibold py-4 rounded-xl transition-all duration-300 hover:bg-gray-200 border border-gray-200"
                        >
                            BACK
                        </button>
                        <button 
                            type="submit" 
                            class="flex-1 gradient-button text-white font-semibold py-4 rounded-xl transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl"
                        >
                            SUBMIT
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer Links -->
        <div class="text-center mt-8 text-sm text-gray-500">
            Need help? • <a href="#" class="text-teal-500 hover:text-teal-600">Support</a> • <a href="#" class="text-teal-500 hover:text-teal-600">FAQ</a>
        </div>

        <!-- Already have account -->
        <div class="text-center mt-4 text-sm text-gray-600">
            Already have an account? <a href="{{ route('login') }}" class="text-teal-500 hover:text-teal-600 font-semibold">Sign in</a>
        </div>
    </div>

    <script>
        // Store form data temporarily
        let formData = {};

        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            field.type = field.type === 'password' ? 'text' : 'password';
        }

        // Save step 1 data and go to step 2
        function goToStep2() {
            // Get values from step 1
            formData.first_name = document.getElementById('first_name').value;
            formData.last_name = document.getElementById('last_name').value;
            formData.email = document.getElementById('email').value;
            formData.password = document.getElementById('password').value;
            formData.password_confirmation = document.getElementById('password_confirmation').value;

            // Basic validation
            if (!formData.first_name || !formData.last_name || !formData.email || !formData.password || !formData.password_confirmation) {
                alert('Please fill in all fields');
                return;
            }

            if (formData.password !== formData.password_confirmation) {
                alert('Passwords do not match');
                return;
            }

            if (formData.password.length < 8) {
                alert('Password must be at least 8 characters');
                return;
            }

            // Switch to step 2
            document.getElementById('step1').classList.add('hidden');
            document.getElementById('step2').classList.remove('hidden');
            
            // Update progress indicator
            document.getElementById('step1-indicator').classList.remove('bg-teal-500', 'text-white');
            document.getElementById('step1-indicator').classList.add('bg-teal-500', 'text-white');
            document.getElementById('step2-indicator').classList.remove('bg-gray-200', 'text-gray-500');
            document.getElementById('step2-indicator').classList.add('bg-teal-500', 'text-white');
            document.getElementById('progress-bar').style.width = '100%';

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Go back to step 1
        function goToStep1() {
            // Switch to step 1
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('step1').classList.remove('hidden');
            
            // Update progress indicator
            document.getElementById('step2-indicator').classList.remove('bg-teal-500', 'text-white');
            document.getElementById('step2-indicator').classList.add('bg-gray-200', 'text-gray-500');
            document.getElementById('progress-bar').style.width = '0%';

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // If there are validation errors, determine which step to show
        @if($errors->any())
            window.addEventListener('DOMContentLoaded', function() {
                const step2Fields = ['gender', 'city_birth', 'city_living', 'birthday', 'terms'];
                const hasStep2Errors = step2Fields.some(field => 
                    document.querySelector(`[name="${field}"]`)?.parentElement?.querySelector('.text-red-500')
                );

                if (hasStep2Errors) {
                    // Prepopulate step 1 data from old values
                    document.getElementById('first_name').value = "{{ old('first_name') }}";
                    document.getElementById('last_name').value = "{{ old('last_name') }}";
                    document.getElementById('email').value = "{{ old('email') }}";
                    
                    // Show step 2
                    goToStep2();
                }
            });
        @endif
    </script>
</body>
</html>
