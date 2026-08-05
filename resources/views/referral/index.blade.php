@extends('layouts.app')
@section('title', 'Parrainage — LeadXchange')

@section('content')

<div class="max-w-3xl mx-auto px-4 py-8 space-y-6">

    {{-- Header --}}
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Votre réseau</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Parrainage</h1>
        <p class="text-sm text-gray-400 mt-1">Invitez vos contacts à rejoindre LeadXchange et gagnez des points.</p>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-2xl px-5 py-4 text-sm">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Récompense --}}
    <div class="rounded-2xl overflow-hidden border border-teal-100" style="background:linear-gradient(135deg,#F0FDFA,#CCFBF1);">
        <div class="p-6 flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:#14B8A6;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <div>
                <p class="font-bold text-teal-900 text-base">Gagnez 5 points par filleul inscrit</p>
                <p class="text-sm text-teal-700 mt-1">Chaque contact qui crée un compte via votre lien ou votre invitation vous rapporte <strong>5 points</strong> crédités automatiquement.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Lien de parrainage --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Votre lien de parrainage</p>
            <p class="text-xs text-gray-400">Partagez ce lien sur vos réseaux, par message, ou par email.</p>

            <div class="flex items-center gap-2">
                <input id="referralLinkInput" type="text" readonly
                       value="{{ $referralLink }}"
                       class="flex-1 h-10 px-3 rounded-xl border border-gray-200 text-xs text-gray-600 bg-gray-50 outline-none select-all cursor-pointer">
                <button onclick="copyLink()" id="copyBtn"
                        class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold text-white transition hover:opacity-90 flex-shrink-0"
                        style="background:#14B8A6;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    Copier
                </button>
            </div>
        </div>

        {{-- Inviter par email --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Inviter par email</p>
            <p class="text-xs text-gray-400">Un email d'invitation personnalisé sera envoyé à votre contact.</p>

            <form method="POST" action="{{ route('referral.send') }}" class="flex items-center gap-2">
                @csrf
                <input type="email" name="email" required placeholder="contact@exemple.com"
                       value="{{ old('email') }}"
                       class="flex-1 h-10 px-3 rounded-xl border border-gray-200 text-xs outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 transition placeholder-gray-300">
                <button type="submit"
                        class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold text-white transition hover:opacity-90 flex-shrink-0"
                        style="background:#14B8A6;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Envoyer
                </button>
            </form>
            @error('email')
            <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Historique --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Mes invitations</p>
            <span class="text-xs text-gray-400">{{ $referrals->where('status','registered')->count() }} inscrit(s) / {{ $referrals->whereNotIn('referred_email', ['share:'.$user->id])->count() }} envoyée(s)</span>
        </div>

        @php $emailReferrals = $referrals->where('referred_email', '!=', 'share:'.$user->id); @endphp

        @if($emailReferrals->isEmpty())
        <div class="px-6 py-10 text-center">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.5" class="mx-auto mb-3"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <p class="text-sm text-gray-400">Aucune invitation envoyée pour l'instant.</p>
        </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($emailReferrals->sortByDesc('created_at') as $referral)
            <div class="px-6 py-3.5 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                         style="background:{{ $referral->status === 'registered' ? '#14B8A6' : '#9CA3AF' }};">
                        {{ strtoupper(substr($referral->referred_email, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $referral->referred_email }}</p>
                        <p class="text-[10px] text-gray-400">Invité le {{ $referral->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
                @if($referral->status === 'registered')
                <span class="flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold" style="background:#F0FDFA;color:#0D9488;">
                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 12 5 5L20 7"/></svg>
                    Inscrit · +5 pts
                </span>
                @else
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold" style="background:#F9FAFB;color:#9CA3AF;">
                    En attente
                </span>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

@endsection

@push('scripts')
<script>
function copyLink() {
    const input = document.getElementById('referralLinkInput');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(function() {
        const btn = document.getElementById('copyBtn');
        btn.textContent = '✓ Copié !';
        setTimeout(() => { btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> Copier'; }, 2000);
    });
}
</script>
@endpush
