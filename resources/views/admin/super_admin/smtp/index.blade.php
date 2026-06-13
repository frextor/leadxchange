@extends('admin.layouts.admin')
@section('title', 'Configuration Email / SMTP')
@section('page-title', 'Email / SMTP')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Email / SMTP</h1>
        <p class="text-sm text-gray-400 mt-1">Configurez le serveur d'envoi et testez la livraison des emails.</p>
    </div>

    <div class="flex items-center gap-2">
        {{-- Test connection button --}}
        <form method="POST" action="{{ route('admin.super.smtp.test-connection') }}">
            @csrf
            <button type="submit"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2a10 10 0 1 0 10 10H12V2z"/><path d="M12 2a10 10 0 0 1 10 10"/>
                </svg>
                Tester la connexion
            </button>
        </form>
    </div>
</div>

{{-- ── Flash messages ───────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="mb-5 flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
    <svg class="flex-shrink-0 mt-0.5" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
    </svg>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-2xl px-5 py-4 text-sm">
    <svg class="flex-shrink-0 mt-0.5" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="mb-5 bg-red-50 border border-red-200 rounded-2xl px-5 py-4 text-sm text-red-800">
    <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $err)
        <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- ── Tabs ─────────────────────────────────────────────────────────────── --}}
<div class="flex gap-1 mb-6 bg-gray-100 p-1 rounded-xl w-fit">
    @foreach([['id'=>'settings','label'=>'Paramètres SMTP'],['id'=>'test','label'=>'Envoyer un test'],['id'=>'diagnostics','label'=>'Diagnostics & Logs']] as $t)
    <button type="button" onclick="switchTab('{{ $t['id'] }}')"
            id="tab-btn-{{ $t['id'] }}"
            class="tab-btn px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === $t['id'] ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
        {{ $t['label'] }}
    </button>
    @endforeach
</div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- TAB: Settings                                                           --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div id="tab-settings" class="tab-pane {{ $tab !== 'settings' ? 'hidden' : '' }}">

    <form method="POST" action="{{ route('admin.super.smtp.update') }}">
        @csrf @method('PUT')

        <div class="grid grid-cols-3 gap-6">

            {{-- ── Connection ── --}}
            <div class="col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-sm font-bold text-gray-700 mb-5 flex items-center gap-2">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                    Connexion SMTP
                </h2>

                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Serveur SMTP (host)</label>
                        <input type="text" name="host" value="{{ old('host', $setting->host ?? 'smtp.ionos.com') }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition"
                               placeholder="smtp.ionos.com" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Port</label>
                        <input type="number" name="port" value="{{ old('port', $setting->port ?? 587) }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition"
                               placeholder="587" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Chiffrement</label>
                        <select name="encryption"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition bg-white">
                            @foreach(['tls'=>'TLS (recommandé)', 'ssl'=>'SSL', 'none'=>'Aucun'] as $val => $label)
                            <option value="{{ $val }}" {{ old('encryption', $setting->encryption ?? 'tls') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Nom d'utilisateur (email IONOS)</label>
                        <input type="text" name="username" value="{{ old('username', $setting->username ?? '') }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition"
                               placeholder="noreply@votredomaine.com" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                            Mot de passe
                            @if($setting->exists && $setting->password)
                            <span class="text-emerald-500 font-normal">(défini — laisser vide pour ne pas changer)</span>
                            @endif
                        </label>
                        <input type="password" name="password" value=""
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition"
                               placeholder="{{ $setting->exists && $setting->password ? '••••••••' : 'Mot de passe SMTP' }}"
                               {{ $setting->exists && $setting->password ? '' : 'required' }}>
                    </div>
                </div>
            </div>

            {{-- ── Expéditeur ── --}}
            <div class="col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-sm font-bold text-gray-700 mb-5 flex items-center gap-2">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    Expéditeur par défaut
                </h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Adresse expéditeur</label>
                        <input type="email" name="from_address" value="{{ old('from_address', $setting->from_address ?? '') }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition"
                               placeholder="noreply@votredomaine.com" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Nom de l'expéditeur</label>
                        <input type="text" name="from_name" value="{{ old('from_name', $setting->from_name ?? 'LeadXchange') }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition"
                               placeholder="LeadXchange" required>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2.5">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                               {{ old('is_active', $setting->is_active ?? true) ? 'checked' : '' }}>
                        <div class="w-9 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-500"></div>
                    </label>
                    <span class="text-sm text-gray-600 font-medium">Configuration active</span>
                </div>
            </div>

            {{-- ── Save ── --}}
            <div class="col-span-3 flex justify-end">
                <button type="submit"
                        class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 active:scale-[.98]"
                        style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    Enregistrer la configuration
                </button>
            </div>

        </div>
    </form>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- TAB: Test email                                                         --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div id="tab-test" class="tab-pane {{ $tab !== 'test' ? 'hidden' : '' }}">

    <div class="max-w-lg">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-sm font-bold text-gray-700 mb-1">Envoyer un email de test</h2>
            <p class="text-xs text-gray-400 mb-5">Envoie un email de notification via la configuration SMTP active pour vérifier la livraison.</p>

            <form method="POST" action="{{ route('admin.super.smtp.send-test') }}">
                @csrf
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Adresse de destination</label>
                <div class="flex gap-2">
                    <input type="email" name="test_email"
                           value="{{ old('test_email', auth()->user()->email) }}"
                           class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition"
                           placeholder="admin@exemple.com" required>
                    <button type="submit"
                            class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white whitespace-nowrap transition hover:opacity-90 active:scale-[.98]"
                            style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                        Envoyer
                    </button>
                </div>
            </form>

            @if(!$setting->exists || !$setting->host)
            <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
                ⚠️ Aucune configuration SMTP enregistrée. Configurez le SMTP dans l'onglet "Paramètres" d'abord.
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- TAB: Diagnostics & Logs                                                 --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div id="tab-diagnostics" class="tab-pane {{ $tab !== 'diagnostics' ? 'hidden' : '' }}">

    {{-- ── Stats 30 days ── --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        @php $stats = $diagnostics['stats']; @endphp

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#EEF2FF;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Emails (30j)</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="1.8"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['sent'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Envoyés</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['failed'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Échoués</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['pending'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">En attente</p>
            </div>
        </div>
    </div>

    {{-- ── Current config status ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <h2 class="text-sm font-bold text-gray-700 mb-4">Configuration active</h2>
        @php $activeSetting = $diagnostics['setting']; @endphp
        @if($activeSetting)
        <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Serveur</p>
                <p class="font-semibold text-gray-800">{{ $activeSetting->host }}:{{ $activeSetting->port }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Chiffrement</p>
                <p class="font-semibold text-gray-800 uppercase">{{ $activeSetting->encryption }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Expéditeur</p>
                <p class="font-semibold text-gray-800">{{ $activeSetting->from_name }} &lt;{{ $activeSetting->from_address }}&gt;</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Utilisateur</p>
                <p class="font-semibold text-gray-800">{{ $activeSetting->username }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Dernier envoi réussi</p>
                <p class="font-semibold text-gray-800">
                    {{ $diagnostics['lastSent'] ? $diagnostics['lastSent']->sent_at?->diffForHumans() : 'Aucun' }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Statut</p>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold {{ $activeSetting->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $activeSetting->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                    {{ $activeSetting->is_active ? 'Actif' : 'Inactif' }}
                </span>
            </div>
        </div>
        @else
        <p class="text-sm text-gray-400">Aucune configuration SMTP active. <a href="{{ route('admin.super.smtp.index', ['tab'=>'settings']) }}" class="text-indigo-600 underline">Configurer maintenant</a>.</p>
        @endif
    </div>

    {{-- ── Last error ── --}}
    @if($diagnostics['lastError'])
    <div class="bg-red-50 border border-red-200 rounded-2xl p-5 mb-6">
        <p class="text-sm font-bold text-red-700 mb-1">Dernière erreur SMTP</p>
        <p class="text-xs text-red-600">{{ $diagnostics['lastError']->error_message }}</p>
        <p class="text-xs text-red-400 mt-1">
            {{ $diagnostics['lastError']->recipient_email }} · {{ $diagnostics['lastError']->created_at->diffForHumans() }}
        </p>
    </div>
    @endif

    {{-- ── Log table ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-700">Historique des emails</h2>

            <form method="POST" action="{{ route('admin.super.smtp.clear-logs') }}"
                  onsubmit="return confirm('Supprimer les logs de plus de 30 jours ?')">
                @csrf @method('DELETE')
                <input type="hidden" name="days" value="30">
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium transition">
                    Purger (> 30j)
                </button>
            </form>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Destinataire</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Objet</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-3.5">
                        <p class="text-xs font-semibold text-gray-800">{{ $log->recipient_name ?? $log->recipient_email }}</p>
                        @if($log->recipient_name)
                        <p class="text-xs text-gray-400">{{ $log->recipient_email }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-3.5 text-xs text-gray-600 max-w-xs truncate">{{ $log->subject }}</td>
                    <td class="px-6 py-3.5">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-600 uppercase">
                            {{ $log->type }}
                        </span>
                    </td>
                    <td class="px-6 py-3.5">
                        @php
                            $badges = [
                                'sent'    => 'bg-emerald-100 text-emerald-700',
                                'failed'  => 'bg-red-100 text-red-700',
                                'pending' => 'bg-amber-100 text-amber-700',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $badges[$log->status] ?? 'bg-gray-100 text-gray-600' }} uppercase">
                            {{ $log->status }}
                        </span>
                        @if($log->error_message)
                        <p class="text-[10px] text-red-400 mt-0.5 truncate max-w-[180px]" title="{{ $log->error_message }}">{{ Str::limit($log->error_message, 40) }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-3.5 text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-14 text-center text-sm text-gray-400">Aucun email enregistré.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $logs->appends(['tab' => 'diagnostics'])->links() }}
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
function switchTab(id) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
        b.classList.add('text-gray-500');
    });
    document.getElementById('tab-' + id).classList.remove('hidden');
    const btn = document.getElementById('tab-btn-' + id);
    btn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
    btn.classList.remove('text-gray-500');

    // Update URL without reload
    const url = new URL(window.location);
    url.searchParams.set('tab', id);
    window.history.replaceState({}, '', url);
}
</script>
@endpush
