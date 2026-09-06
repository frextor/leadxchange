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
        <div class="px-8 pt-8 pb-6 text-center" style="background:linear-gradient(135deg,#1D4ED8,#1E40AF);">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4"
                 style="background:rgba(255,255,255,.18);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>
                </svg>
            </div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold mb-3"
                 style="background:rgba(255,255,255,.2);color:rgba(255,255,255,.9);">
                Pack Entreprise LeadXchange
            </div>
            <h1 class="text-xl font-bold text-white mb-1">{{ $invitation->license->company_name }}</h1>
            <p class="text-sm text-blue-200">
                <strong class="text-white">{{ $invitation->license->holder?->first_name }} {{ $invitation->license->holder?->last_name }}</strong>
                vous invite à rejoindre son équipe.
            </p>
        </div>

        {{-- Body --}}
        <div class="px-8 py-7">

            @if($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
            @endif

            @if($confirmOnly)
            {{-- ── Existing user: must authenticate before activation ── --}}
            <div class="mb-5 flex items-center gap-3 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                     style="background:linear-gradient(135deg,#1D4ED8,#1E40AF);">
                    {{ strtoupper(substr($existingUser->first_name, 0, 1)) }}{{ strtoupper(substr($existingUser->last_name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $existingUser->first_name }} {{ $existingUser->last_name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $existingUser->email }}</p>
                </div>
            </div>

            @if($alreadyAuthenticated)
            <p class="text-sm text-gray-600 mb-6 leading-relaxed">
                Vous êtes déjà connecté à ce compte. En confirmant, votre licence <strong>Premium Entreprise</strong>
                sera activée immédiatement — aucun paiement requis.
            </p>

            <form method="POST" action="{{ route('enterprise.join.process', $invitation->token) }}">
                @csrf
                <button type="submit"
                        class="w-full py-3 rounded-xl text-sm font-bold text-white transition hover:opacity-90"
                        style="background:linear-gradient(135deg,#1D4ED8,#1E40AF);">
                    Activer ma licence Premium →
                </button>
            </form>
            @else
            <p class="text-sm text-gray-600 mb-5 leading-relaxed">
                Ce compte existe déjà. Connectez-vous avec votre mot de passe pour activer votre licence
                <strong>Premium Entreprise</strong> — aucun paiement requis.
            </p>

            <form method="POST" action="{{ route('enterprise.join.process', $invitation->token) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Mot de passe</label>
                    <input type="password" name="password" required autofocus
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Votre mot de passe">
                </div>
                <button type="submit"
                        class="w-full py-3 rounded-xl text-sm font-bold text-white transition hover:opacity-90"
                        style="background:linear-gradient(135deg,#1D4ED8,#1E40AF);">
                    Se connecter et activer ma licence →
                </button>
                <p class="text-center text-xs text-gray-400">
                    Mot de passe oublié ?
                    <a href="{{ route('password.request') }}" class="text-blue-600 hover:underline">Réinitialisez-le</a>,
                    puis revenez sur ce lien.
                </p>
            </form>
            @endif

            @else
            {{-- ── New user: registration form ── --}}
            <p class="text-xs text-gray-500 mb-5 leading-relaxed">
                @if($invitation->email)
                    Créez votre compte pour activer votre licence Premium.
                @else
                    Renseignez votre email et créez votre compte pour activer votre licence.
                @endif
            </p>

            <form method="POST" action="{{ route('enterprise.join.process', $invitation->token) }}" class="space-y-4">
                @csrf

                {{-- Email: editable if no email pre-set, read-only otherwise --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Adresse e-mail</label>
                    @if($invitation->email)
                    <input type="email" value="{{ $invitation->email }}" readonly
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 cursor-not-allowed">
                    @else
                    <input type="email" name="email" required
                           value="{{ old('email') }}"
                           placeholder="votre@email.com"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Prénom</label>
                        <input type="text" name="first_name" required
                               value="{{ old('first_name') }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom</label>
                        <input type="text" name="last_name" required
                               value="{{ old('last_name') }}"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Choisir un mot de passe</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="8 caractères minimum">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <button type="submit"
                        class="w-full py-3 rounded-xl text-sm font-bold text-white transition hover:opacity-90 mt-2"
                        style="background:linear-gradient(135deg,#1D4ED8,#1E40AF);">
                    Créer mon compte et activer ma licence →
                </button>
            </form>
            @endif

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
