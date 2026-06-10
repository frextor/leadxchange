<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration — LeadXchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body style="background:#0F172A;" class="min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-sm">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center text-white font-bold text-xl"
             style="background:linear-gradient(135deg,#34d4bf,#1E8F88);">LX</div>
        <h1 class="text-xl font-semibold text-white">Espace Administration</h1>
        <p class="text-sm mt-1" style="color:#64748B;">Accès réservé aux administrateurs</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="px-3 py-2 border-b border-gray-100 flex items-center gap-1.5">
            <div class="w-3 h-3 rounded-full bg-red-400"></div>
            <div class="w-3 h-3 rounded-full bg-amber-400"></div>
            <div class="w-3 h-3 rounded-full bg-green-400"></div>
        </div>

        <form method="POST" action="{{ route('admin.login.post') }}" class="p-6 space-y-4">
            @csrf

            @if($errors->any())
            <div class="px-4 py-3 rounded-xl text-sm font-medium bg-red-50 text-red-600 border border-red-100">
                {{ $errors->first() }}
            </div>
            @endif

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="admin@x-tensia.com"
                       class="w-full h-11 px-4 rounded-xl border border-gray-200 text-sm text-gray-900 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition"
                       style="{{ $errors->has('email') ? 'border-color:#EF4444;' : '' }}">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Mot de passe</label>
                <input type="password" name="password" required
                       placeholder="••••••••••"
                       class="w-full h-11 px-4 rounded-xl border border-gray-200 text-sm text-gray-900 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded text-teal-600">
                <label for="remember" class="text-sm text-gray-500">Se souvenir de moi</label>
            </div>

            <button type="submit"
                    class="w-full py-3 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 active:scale-[.98]"
                    style="background:linear-gradient(135deg,#34d4bf,#1E8F88);">
                Connexion
            </button>
        </form>
    </div>

    <p class="text-center text-xs mt-6" style="color:#334155;">
        <a href="{{ url('/') }}" class="hover:text-teal-400 transition">← Retour au site</a>
    </p>
</div>

</body>
</html>
