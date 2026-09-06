@extends('enterprise.layouts.enterprise')
@section('title', 'Membres — ' . $license->company_name)
@section('page-title', 'Membres & licences')

@section('content')

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 mb-6 flex-wrap">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Membres &amp; licences</h1>
            <p class="text-sm text-slate-500 mt-0.5">Gérez les licences de {{ $license->company_name }}.</p>
        </div>
    </div>

    {{-- ── Analytics ────────────────────────────────────────────────────────── --}}
    <div class="mb-6">
        <h2 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2"><path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-3"/></svg>
            Activité de l'équipe
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-4 py-3.5">
                <p class="text-2xl font-extrabold text-gray-900">{{ $analytics['leads_sent_total'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Leads envoyés</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-4 py-3.5">
                <p class="text-2xl font-extrabold text-teal-600">{{ $analytics['leads_converted'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Convertis</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-4 py-3.5">
                <p class="text-2xl font-extrabold text-gray-900">{{ $analytics['leads_recv_total'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Leads reçus</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-4 py-3.5">
                <p class="text-2xl font-extrabold text-indigo-600">{{ $analytics['connections_total'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Connexions</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-4 py-3.5">
                <p class="text-2xl font-extrabold text-amber-600">⭐ {{ $analytics['points_total'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Points cumulés</p>
            </div>
        </div>
    </div>

    {{-- ── Leaderboard ──────────────────────────────────────────────────────── --}}
    @if($leaderboard->count() > 1)
    <div class="mb-6 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-800">Classement de l'équipe</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-50">
                        <th class="text-left px-5 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Membre</th>
                        <th class="text-center px-3 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Envoyés</th>
                        <th class="text-center px-3 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Convertis</th>
                        <th class="text-center px-3 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Reçus</th>
                        <th class="text-center px-3 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Connexions</th>
                        <th class="text-right px-5 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Points</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($leaderboard as $i => $row)
                    <tr>
                        <td class="px-5 py-2.5">
                            <div class="flex items-center gap-2.5">
                                @if($i === 0)<span title="Meilleur contributeur">🥇</span>@endif
                                <span class="text-sm font-medium text-gray-700">{{ $row['user']->first_name }} {{ $row['user']->last_name }}</span>
                                @if($row['user']->id === $license->holder_user_id)
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-500">TITULAIRE</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-2.5 text-center font-semibold text-gray-700">{{ $row['leads_sent'] }}</td>
                        <td class="px-3 py-2.5 text-center font-semibold text-teal-600">{{ $row['converted'] }}</td>
                        <td class="px-3 py-2.5 text-center font-semibold text-gray-700">{{ $row['leads_recv'] }}</td>
                        <td class="px-3 py-2.5 text-center font-semibold text-indigo-600">{{ $row['connections'] }}</td>
                        <td class="px-5 py-2.5 text-right font-semibold text-amber-600">{{ $row['points'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Members list --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">Membres du pack</h2>
            <span class="text-xs text-gray-400">{{ $license->seats_total }} licences au total</span>
        </div>

        {{-- Holder row --}}
        <div class="flex items-center gap-4 px-6 py-4 border-b border-gray-50">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                 style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                <p class="text-xs text-gray-400">{{ auth()->user()->email }}</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold" style="background:#EEF2FF;color:#6366F1;">
                Titulaire
            </span>
        </div>

        @foreach($invitations as $inv)
        <div class="flex items-center gap-4 px-6 py-4 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">

            @if($inv->status === 'available')
            {{-- Available slot --}}
            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 flex-shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <form method="POST" action="{{ route('enterprise.invite') }}" class="flex items-center gap-2 flex-wrap">
                    @csrf
                    <input type="email" name="email" required placeholder="email@collaborateur.com"
                           class="flex-1 min-w-[200px] rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <button type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-white hover:opacity-90 transition"
                            style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                        Inviter
                    </button>
                </form>
                <p class="text-[10px] text-gray-400 mt-1">
                    Ou partagez le lien directement :
                    <button type="button"
                            onclick="copyLink('{{ route('enterprise.join', $inv->token) }}', this)"
                            class="text-indigo-500 underline hover:text-indigo-700 ml-0.5">
                        Copier le lien
                    </button>
                </p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700 flex-shrink-0">Disponible</span>

            @elseif($inv->status === 'active')
            {{-- Active member --}}
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0 text-white"
                 style="background:linear-gradient(135deg,#34d4bf,#1E8F88);">
                {{ strtoupper(substr($inv->user?->first_name ?? $inv->email, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                @if($inv->user)
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $inv->user->first_name }} {{ $inv->user->last_name }}</p>
                @endif
                <p class="text-xs text-gray-400 truncate">{{ $inv->email }}</p>
                @if($inv->accepted_at)
                <p class="text-[10px] text-gray-300 mt-0.5">Rejoint le {{ $inv->accepted_at->format('d/m/Y') }}</p>
                @endif
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 flex-shrink-0">Actif</span>

            @if($inv->user)
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <a href="{{ route('profile.show', $inv->user_id) }}" title="Voir le profil"
                   class="p-2 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
                <button type="button" title="Envoyer un message"
                        onclick="openMessageModal({{ $inv->user_id }}, '{{ addslashes($inv->user->first_name) }}')"
                        class="p-2 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </button>
            </div>
            @endif

            <form method="POST" action="{{ route('enterprise.revoke', $inv->id) }}"
                  onsubmit="return confirm('Révoquer la licence de {{ addslashes($inv->email) }} ? La licence sera libérée et le membre repassera en Basic.')">
                @csrf
                <button type="submit"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-red-200 text-red-500 hover:bg-red-50 transition">
                    Révoquer
                </button>
            </form>

            @elseif($inv->status === 'pending')
            {{-- Pending invitation --}}
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0 text-white"
                 style="background:linear-gradient(135deg,#FDE68A,#F59E0B);">
                {{ strtoupper(substr($inv->email, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 truncate">{{ $inv->email }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Invitation envoyée — en attente d'activation</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700 flex-shrink-0">En attente</span>
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <form method="POST" action="{{ route('enterprise.resend', $inv->id) }}">
                    @csrf
                    <button type="submit" title="Renvoyer l'invitation"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-indigo-200 text-indigo-600 hover:bg-indigo-50 transition flex items-center gap-1.5">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                        Renvoyer
                    </button>
                </form>
                <form method="POST" action="{{ route('enterprise.revoke', $inv->id) }}"
                      onsubmit="return confirm('Annuler cette invitation et libérer la licence ?')">
                    @csrf
                    <button type="submit"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
                        Annuler
                    </button>
                </form>
            </div>
            @endif

        </div>
        @endforeach

        @if($invitations->isEmpty())
        <div class="px-6 py-10 text-center">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" class="mx-auto mb-3"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <p class="text-sm text-gray-400">Aucune licence disponible dans ce pack.</p>
        </div>
        @endif
    </div>

    {{-- License info --}}
    <div class="mt-4 text-xs text-gray-400 text-center">
        @if($license->expires_at)
        Licences valides jusqu'au <strong>{{ $license->expires_at->format('d/m/Y') }}</strong>.
        @else
        Licences sans date d'expiration.
        @endif
        · Pour modifier votre quota, <a href="mailto:contact@leadxchange.com" class="underline hover:text-gray-600">contactez-nous</a>.
    </div>

</div>

{{-- Message modal --}}
<div id="messageModal" class="hidden fixed inset-0 z-[999] flex items-center justify-center p-4" style="background:rgba(15,23,42,.45);" onclick="if(event.target===this) closeMessageModal()">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-900">Message à <span id="msgRecipientName" class="text-indigo-600"></span></h3>
            <button onclick="closeMessageModal()" class="text-gray-300 hover:text-gray-500">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="messageForm" method="POST">
            @csrf
            <div class="px-6 py-4 space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Objet</label>
                    <input type="text" name="subject" required maxlength="150" placeholder="Ex : Point sur les leads du mois"
                           class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Message</label>
                    <textarea name="message" required maxlength="2000" rows="5" placeholder="Votre message…"
                              class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                </div>
                <p class="text-[11px] text-gray-400">Le membre recevra une notification dans l'app et un email.</p>
            </div>
            <div class="px-6 pb-5">
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-bold text-white hover:opacity-90 transition"
                        style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                    Envoyer le message
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function copyLink(url, btn) {
    navigator.clipboard.writeText(url).then(function() {
        const orig = btn.textContent;
        btn.textContent = 'Copié !';
        btn.style.color = '#059669';
        setTimeout(function() { btn.textContent = orig; btn.style.color = ''; }, 2000);
    }).catch(function() {
        prompt('Copiez ce lien :', url);
    });
}

function openMessageModal(userId, firstName) {
    document.getElementById('msgRecipientName').textContent = firstName;
    document.getElementById('messageForm').action = '/enterprise/team/message/' + userId;
    document.getElementById('messageModal').classList.remove('hidden');
}
function closeMessageModal() {
    document.getElementById('messageModal').classList.add('hidden');
}
</script>

@endsection
