<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LeadXchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-2xl w-full">

            <!-- Success Message -->
            @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-lg shadow-lg mb-8">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-green-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-green-800">Succès !</h3>
                        <p class="text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Info Message -->
            @if(session('info'))
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg shadow-lg mb-8">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-blue-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-blue-800">Information</h3>
                        <p class="text-blue-700">{{ session('info') }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg shadow-lg mb-8">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-red-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-red-800">Erreur</h3>
                        <p class="text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Email Verification Notice -->
            @if(!auth()->user()->hasVerifiedEmail())
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-lg shadow-lg mb-8">
                <div class="flex items-start">
                    <svg class="w-8 h-8 text-yellow-500 mr-4 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-yellow-800 mb-2">Vérifiez votre email</h3>
                        <p class="text-yellow-700 mb-4">
                            Un email de vérification a été envoyé à <strong>{{ auth()->user()->email }}</strong>.
                            Veuillez cliquer sur le lien dans l'email pour vérifier votre compte.
                        </p>
                        <form action="{{ route('verification.send') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded transition-colors">
                                Renvoyer l'email
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            <!-- Welcome Card -->
            <div class="bg-white rounded-3xl shadow-xl p-8">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-teal-400 to-teal-600 rounded-2xl mb-4">
                        <span class="text-white text-3xl font-bold">LX</span>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Bienvenue, {{ auth()->user()->first_name }} !</h1>
                    <p class="text-gray-600">Votre compte a été créé avec succès</p>
                </div>

                <!-- User Info -->
                <div class="bg-gray-50 rounded-2xl p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations du compte</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nom complet:</span>
                            <span class="font-semibold text-gray-900">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Email:</span>
                            <span class="font-semibold text-gray-900">{{ auth()->user()->email }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Email vérifié:</span>
                            @if(auth()->user()->hasVerifiedEmail())
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Vérifié
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                En attente
                            </span>
                            @endif
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Ville:</span>
                            <span class="font-semibold text-gray-900">{{ auth()->user()->city_living }}</span>
                        </div>
                        @if(auth()->user()->subscription)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Plan:</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-teal-100 text-teal-800">
                                {{ ucfirst(auth()->user()->subscription->plan->name) }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="bg-teal-50 rounded-2xl p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Prochaines étapes</h3>
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-teal-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-700">Compte créé avec succès ✓</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-teal-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-700">Profil complété ✓</span>
                        </li>
                        <li class="flex items-start">
                            @if(auth()->user()->hasVerifiedEmail())
                            <svg class="w-5 h-5 text-teal-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-700">Email vérifié ✓</span>
                            @else
                            <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-600">Vérifier votre email</span>
                            @endif
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-600">Créer votre entreprise</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4">
                    <a href="{{ route('company.create') }}" class="flex-1 bg-gradient-to-r from-teal-500 to-teal-600 text-white text-center font-semibold py-4 rounded-xl transition-all duration-300 transform hover:scale-[1.02] shadow-lg hover:shadow-xl">
                        Créer mon entreprise
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full bg-gray-100 text-gray-700 font-semibold py-4 rounded-xl transition-all duration-300 hover:bg-gray-200 border border-gray-200">
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</body>

</html>