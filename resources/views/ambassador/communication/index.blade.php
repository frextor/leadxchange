@extends('ambassador.layouts.ambassador')
@section('title', 'Communication')
@section('page-title', 'Communication')
@section('page-subtitle', $regionName . ' · ' . $membersCount . ' membre(s) dans la région')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Compose announcement ─────────────────────────────────────────────── --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-5">
            <h3 class="text-sm font-bold text-slate-800 mb-5">Nouvelle annonce régionale</h3>

            <form method="POST" action="{{ route('ambassador.communication.announce') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Type d'annonce</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @php $types = [
                            'general'       => ['label' => 'Général', 'emoji' => '📢'],
                            'event_reminder'=> ['label' => 'Rappel événement', 'emoji' => '📅'],
                            'welcome'       => ['label' => 'Bienvenue', 'emoji' => '👋'],
                            'networking'    => ['label' => 'Networking', 'emoji' => '🤝'],
                        ]; @endphp
                        @foreach($types as $value => $type)
                        <label class="flex items-center gap-2 p-3 rounded-xl border border-gray-200 cursor-pointer hover:border-teal-300 transition has-[:checked]:border-teal-400 has-[:checked]:bg-teal-50">
                            <input type="radio" name="type" value="{{ $value }}" class="accent-teal-500"
                                   @if(old('type', 'general') === $value) checked @endif>
                            <span class="text-xs font-semibold text-slate-700">{{ $type['emoji'] }} {{ $type['label'] }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Objet *</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required
                           placeholder="Ex : Événement networking ce jeudi !"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
                    @error('subject')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Message *</label>
                    <textarea name="body" rows="6" required
                              placeholder="Rédigez votre message pour les membres de votre région..."
                              class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300 resize-none">{{ old('body') }}</textarea>
                    @error('body')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center justify-between pt-2">
                    <p class="text-xs text-slate-400">
                        <svg class="inline w-3.5 h-3.5 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        Sera envoyé à <strong>{{ $membersCount }}</strong> membre(s) de {{ $regionName }}
                    </p>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl text-sm font-bold text-white"
                            style="background:linear-gradient(135deg,#14B8A6,#0F766E);"
                            onclick="return confirm('Envoyer cette annonce à {{ $membersCount }} membre(s) ?')">
                        Envoyer l'annonce
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Info sidebar ─────────────────────────────────────────────────────── --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wide mb-3">À propos</h4>
            <p class="text-sm text-slate-600 leading-relaxed">
                Les annonces sont envoyées uniquement aux membres de votre région. Utilisez-les avec parcimonie pour
                ne pas saturer les notifications de vos membres.
            </p>
            <div class="mt-4 p-3 rounded-xl bg-amber-50 border border-amber-200">
                <p class="text-xs text-amber-700 font-semibold">⚠ Bonne pratique</p>
                <p class="text-xs text-amber-600 mt-1">Maximum 2-3 annonces par semaine. Privilégiez des messages ciblés et personnalisés.</p>
            </div>
            <div class="mt-3 p-3 rounded-xl bg-blue-50 border border-blue-200">
                <p class="text-xs text-blue-700 font-semibold">🔔 Push Notifications</p>
                <p class="text-xs text-blue-600 mt-1">Les membres qui ont autorisé les notifications reçoivent une alerte instantanée dans leur navigateur dès l'envoi de l'annonce.</p>
            </div>
        </div>
    </div>
</div>

{{-- Announcements history ─────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm mt-5">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="text-sm font-bold text-slate-800">Historique des annonces</h3>
    </div>
    @if($announcements->count())
    <div class="divide-y divide-gray-50">
        @foreach($announcements as $ann)
        @php $typeLabels = ['general'=>'📢 Général','event_reminder'=>'📅 Rappel','welcome'=>'👋 Bienvenue','networking'=>'🤝 Networking']; @endphp
        <div class="px-5 py-4">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[11px] text-slate-500">{{ $typeLabels[$ann->type] ?? $ann->type }}</span>
                        <span class="text-slate-200">·</span>
                        <span class="text-[11px] text-slate-400">{{ $ann->sent_at?->format('d/m/Y H:i') }}</span>
                    </div>
                    <p class="text-sm font-bold text-slate-800">{{ $ann->subject }}</p>
                    <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $ann->body }}</p>
                </div>
                <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-teal-50 text-teal-700 flex-shrink-0">
                    {{ $ann->recipients_count }} destinataire(s)
                </span>
            </div>
        </div>
        @endforeach
    </div>
    @if($announcements->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $announcements->links() }}</div>
    @endif
    @else
    <div class="py-12 text-center">
        <p class="text-slate-400 text-sm">Aucune annonce envoyée pour le moment.</p>
    </div>
    @endif
</div>

@endsection
