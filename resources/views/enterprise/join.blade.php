<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rejoindre {{ $invitation->license->company_name }} — LeadXchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4" style="background:linear-gradient(135deg,#EEF2FF 0%,#F0FDF4 100%);">

<div class="w-full max-w-md">

    <div class="bg-white rounded-3xl shadow-xl overflow-hidden">

        {{-- Header --}}
        <div class="px-8 pt-8 pb-6 text-center" style="background:linear-gradient(135deg,#6366F1,#4338CA);">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4"
                 style="background:rgba(255,255,255,.18);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold mb-3"
                 style="background:rgba(255,255,255,.2);color:rgba(255,255,255,.9);">
                Pack Entreprise LeadXchange
            </div>
            <h1 class="text-xl font-bold text-white mb-1">{{ $invitation->license->company_name }}</h1>
            <p class="text-sm text-indigo-200">
                <strong class="text-white">{{ $invitation->license->holder?->first_name }} {{ $invitation->license->holder?->last_name }}</strong>
                vous invite à rejoindre son équipe.
            </p>
        </div>

        {{-- Form --}}
        <div class="px-8 py-7">

            @if($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
            @endif

            <p class="text-xs text-gray-500 mb-5 leading-relaxed">
                Finalisez votre compte pour activer votre licence Premium.
                @if($existingUser)
                    Votre adresse <strong>{{ $invitation->email }}</strong> a déjà un compte — choisissez un nouveau mot de passe pour le sécuriser.
                @endif
            </p>

            <form method="POST" action="{{ route('enterprise.join.process', $invitation->token) }}" class="space-y-4">
                @csrf

                {{-- Email field: editable if no email pre-set, read-only otherwise --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Adresse e-mail</label>
                    @if($invitation->email)
                    <input type="email" value="{{ $invitation->email }}" readonly
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 cursor-not-allowed">
                    @else
                    <input type="email" name="email" required
                           value="{{ old('email') }}"
                           placeholder="votre@email.com"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @endif
                </div>

                {{-- Name row --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Prénom</label>
                        <input type="text" name="first_name" required
                               value="{{ old('first_name', $existingUser?->first_name) }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom</label>
                        <input type="text" name="last_name" required
                               value="{{ old('last_name', $existingUser?->last_name) }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        {{ $existingUser ? 'Nouveau mot de passe' : 'Choisir un mot de passe' }}
                    </label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="8 caractères minimum">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <button type="submit"
                        class="w-full py-3 rounded-xl text-sm font-bold text-white transition hover:opacity-90 mt-2"
                        style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                    Activer ma licence Premium →
                </button>
            </form>

            <p class="text-center text-xs text-gray-400 mt-5">
                En acceptant, vous rejoignez l'équipe
                <strong>{{ $invitation->license->company_name }}</strong>.
                Votre accès est géré par le titulaire du pack.
            </p>
        </div>
    </div>

    <p class="text-center text-xs text-gray-400 mt-6">
        © {{ date('Y') }} X-tensia SAS — LeadXchange
    </p>
</div>

</body>
</html>
