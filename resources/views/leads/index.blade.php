@extends('layouts.dashboard')

@section('title', 'Exchanges — LeadXchange')

@push('styles')
<style>
    .ld-tab { padding: 8px 20px; border-radius: 10px; font-size: 13px; font-weight: 600;
              cursor: pointer; border: none; background: transparent; color: #6B7280;
              transition: all .15s; display: flex; align-items: center; gap: 6px; }
    .ld-tab:hover  { background: #F3F4F6; color: #111827; }
    .ld-tab.active { background: #111827; color: white; }
    .ld-tab .badge { padding: 1px 7px; border-radius: 20px; font-size: 11px; font-weight: 700; }
    .ld-tab.active .badge { background: rgba(255,255,255,.2); color: white; }
    .ld-tab:not(.active) .badge { background: #F3F4F6; color: #6B7280; }

    .lead-card { background: white; border-radius: 16px; border: 1px solid #E5E7EB;
                 padding: 20px; transition: box-shadow .2s, transform .15s; }
    .lead-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.07); transform: translateY(-1px); }

    .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px;
                    border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: .02em; }
    .status-dot   { width: 6px; height: 6px; border-radius: 50%; }

    .lx-input { height: 40px; padding: 0 14px; border-radius: 10px; border: 1.5px solid #E5E7EB;
                background: white; font-size: 13px; color: #111827; outline: none; width: 100%;
                font-family: inherit; transition: border-color .15s, box-shadow .15s; }
    .lx-input:focus { border-color: #111827; box-shadow: 0 0 0 3px rgba(17,24,39,.08); }
    .lx-input.error { border-color: #EF4444; }
    .lx-textarea { padding: 10px 14px; border-radius: 10px; border: 1.5px solid #E5E7EB;
                   background: white; font-size: 13px; color: #111827; outline: none; width: 100%;
                   font-family: inherit; transition: border-color .15s, box-shadow .15s; resize: none; }
    .lx-textarea:focus { border-color: #111827; box-shadow: 0 0 0 3px rgba(17,24,39,.08); }

    .qual-btn { flex: 1; padding: 10px 8px; border-radius: 12px; border: 2px solid #E5E7EB;
                cursor: pointer; text-align: center; transition: all .15s; background: white; }
    .qual-btn.selected-chaud  { border-color: #DC2626; background: #FEE2E2; }
    .qual-btn.selected-tiede  { border-color: #D97706; background: #FEF3C7; }
    .qual-btn.selected-froid  { border-color: #3B82F6; background: #EFF6FF; }

    .badge-pill { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px;
                  border-radius: 20px; font-size: 11px; font-weight: 700; }
</style>
@endpush

@section('content')
@php
    $pendingCount  = $received->where('status', 'new')->count();
    $userPoints    = $currentUser->points_balance ?? 0;
    $badgeConfig   = ['bronze' => ['label'=>'Bronze','classes'=>'bg-yellow-100 text-yellow-900'],
                      'argent' => ['label'=>'Argent','classes'=>'bg-gray-100 text-gray-700'],
                      'or'     => ['label'=>'Or',    'classes'=>'bg-amber-100 text-amber-900']];
    $badge         = $badgeConfig[$currentUser->badge_level ?? 'bronze'];
@endphp

<div class="max-w-5xl mx-auto px-4 lg:px-8 py-8">

    {{-- ── Page header ── --}}
    <div class="flex items-start justify-between mb-8 flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Exchanges</h1>
            <p class="text-sm text-gray-400 mt-1">Partagez et recevez des leads qualifiés avec votre réseau</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Points + Badge display --}}
            <div class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white shadow-sm">
                <span class="badge-pill {{ $badge['classes'] }}">
                    @if($currentUser->badge_level === 'or') 🥇
                    @elseif($currentUser->badge_level === 'argent') 🥈
                    @else 🥉
                    @endif
                    {{ $badge['label'] }}
                </span>
                <span class="text-sm font-bold text-gray-900">{{ $userPoints }}</span>
                <span class="text-xs text-gray-400">pts</span>
            </div>
            <button onclick="document.getElementById('sendLeadModal').classList.remove('hidden')"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                    style="background:#111827;" onmouseover="this.style.background='#1F2937'" onmouseout="this.style.background='#111827'">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                Envoyer un Lead
            </button>
        </div>
    </div>

    {{-- ── Stats row ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        @php
            $stats = [
                ['label' => 'Envoyés',    'value' => $sent->count(),                              'numClass' => 'text-indigo-500', 'bgClass' => 'bg-indigo-50'],
                ['label' => 'Reçus',      'value' => $received->count(),                          'numClass' => 'text-teal-600',   'bgClass' => 'bg-teal-50'],
                ['label' => 'En attente', 'value' => $pendingCount,                               'numClass' => 'text-amber-500',  'bgClass' => 'bg-amber-50'],
                ['label' => 'Convertis',  'value' => $sent->where('status','converted')->count(), 'numClass' => 'text-emerald-500','bgClass' => 'bg-emerald-50'],
            ];
        @endphp
        @foreach($stats as $s)
        <div class="rounded-2xl border border-gray-100 p-4 shadow-sm {{ $s['bgClass'] }}">
            <p class="text-2xl font-extrabold {{ $s['numClass'] }}">{{ $s['value'] }}</p>
            <p class="text-xs font-semibold text-gray-500 mt-0.5">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── Flash messages ── --}}
    @if(session('success'))
    <div class="mb-5 px-4 py-3 rounded-xl text-sm font-medium" style="background:#E6F7F4;color:#1E8F88;">
        {{ session('success') }}
    </div>
    @endif
    @if($errors->has('error'))
    <div class="mb-5 px-4 py-3 rounded-xl text-sm font-medium bg-red-50 text-red-600">
        {{ $errors->first('error') }}
    </div>
    @endif

    {{-- ── Tabs ── --}}
    <div class="flex items-center gap-2 mb-6">
        <button onclick="switchTab('received')" id="tab-received" class="ld-tab active">
            Reçus
            <span class="badge">{{ $received->count() }}</span>
            @if($pendingCount > 0)
            <span class="w-2 h-2 rounded-full bg-amber-400 ml-0.5"></span>
            @endif
        </button>
        <button onclick="switchTab('sent')" id="tab-sent" class="ld-tab">
            Envoyés
            <span class="badge">{{ $sent->count() }}</span>
        </button>
    </div>

    {{-- ── Received panel ── --}}
    <div id="panel-received">
        @if($received->isEmpty())
        <div class="text-center py-16 bg-white rounded-2xl border border-gray-100">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#E6F7F4;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="1.5">
                    <path d="M20 12V22H4V12"/><path d="M22 7H2v5h20V7z"/><path d="M12 22V7"/>
                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                </svg>
            </div>
            <p class="font-semibold text-gray-600">Aucun lead reçu</p>
            <p class="text-sm text-gray-400 mt-1">Les leads envoyés par votre réseau apparaîtront ici</p>
        </div>
        @else
        <div class="space-y-4">
            @foreach($received as $lead)
                @include('leads._card', ['lead' => $lead, 'mode' => 'received', 'currentUser' => $currentUser])
            @endforeach
        </div>
        @endif
    </div>

    {{-- ── Sent panel ── --}}
    <div id="panel-sent" class="hidden">
        @if($sent->isEmpty())
        <div class="text-center py-16 bg-white rounded-2xl border border-gray-100">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#EEF2FF;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.5">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </div>
            <p class="font-semibold text-gray-600">Aucun lead envoyé</p>
            <p class="text-sm text-gray-400 mt-1">Partagez une opportunité commerciale avec votre réseau</p>
            <button onclick="document.getElementById('sendLeadModal').classList.remove('hidden')"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white"
                    style="background:#111827;">
                Envoyer votre premier lead
            </button>
        </div>
        @else
        <div class="space-y-4">
            @foreach($sent as $lead)
                @include('leads._card', ['lead' => $lead, 'mode' => 'sent', 'currentUser' => $currentUser])
            @endforeach
        </div>
        @endif
    </div>

</div>

{{-- ════════════════════════════════════════════════════
     SEND LEAD MODAL
════════════════════════════════════════════════════ --}}
<div id="sendLeadModal"
     class="{{ $errors->any() && !session('success') ? '' : 'hidden' }} fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,.5);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[94vh] overflow-y-auto">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
            <div>
                <h2 class="font-bold text-gray-900">Envoyer un Lead</h2>
                <p class="text-xs text-gray-400 mt-0.5">Partagez une opportunité commerciale qualifiée</p>
            </div>
            <button type="button" onclick="document.getElementById('sendLeadModal').classList.add('hidden')"
                    class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 transition">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('leads.store') }}" class="px-6 py-5 space-y-4">
            @csrf

            {{-- ── Recipient ── --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    Envoyer à <span class="text-red-400">*</span>
                </label>
                <input type="hidden" name="receiver_id" id="receiverId" value="{{ old('receiver_id') }}" required>
                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <input type="text" id="receiverSearch"
                           placeholder="Chercher une connexion…"
                           autocomplete="off"
                           value="{{ old('receiver_id') ? (($u2=$connections->firstWhere('id',old('receiver_id'))) ? $u2->first_name.' '.$u2->last_name : '') : '' }}"
                           class="lx-input pl-9 @error('receiver_id') error @enderror"
                           oninput="filterMembers(this.value)"
                           onfocus="showMemberDropdown()"
                           onblur="setTimeout(hideMemberDropdown, 150)">
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                    <div id="memberDropdown"
                         class="hidden absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden"
                         style="max-height:220px;overflow-y:auto;">
                        @if($connections->isEmpty())
                        <div class="px-4 py-5 text-center text-sm text-gray-400">
                            Connectez-vous avec des membres pour leur envoyer des leads
                        </div>
                        @else
                        @foreach($connections as $u)
                        <button type="button"
                                class="member-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition
                                       {{ ($u->points_balance ?? 0) < 1 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                data-id="{{ $u->id }}"
                                data-name="{{ $u->first_name }} {{ $u->last_name }}"
                                data-balance="{{ $u->points_balance ?? 0 }}"
                                onclick="selectMember(this)">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                 style="background:linear-gradient(135deg,#1E8F88,#34d4bf);">
                                {{ strtoupper(substr($u->first_name, 0, 1)) }}{{ strtoupper(substr($u->last_name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $u->first_name }} {{ $u->last_name }}</p>
                                @if(($u->points_balance ?? 0) < 1)
                                <p class="text-xs text-red-400">Solde insuffisant pour recevoir</p>
                                @else
                                <p class="text-xs text-gray-400">{{ $u->points_balance }} pts</p>
                                @endif
                            </div>
                        </button>
                        @endforeach
                        @endif
                        <div id="noMemberResult" class="hidden px-4 py-4 text-sm text-gray-400 text-center">Aucun résultat</div>
                    </div>
                </div>
                @error('receiver_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- ── Contact info section ── --}}
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 space-y-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Contact à recommander</p>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Entreprise <span class="text-red-400">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}"
                               placeholder="Nom de l'entreprise" required maxlength="150"
                               class="lx-input @error('company_name') error @enderror">
                        @error('company_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Contact <span class="text-red-400">*</span></label>
                        <input type="text" name="contact_name" value="{{ old('contact_name') }}"
                               placeholder="Nom du contact" required maxlength="100"
                               class="lx-input @error('contact_name') error @enderror">
                        @error('contact_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email') }}"
                               placeholder="email@exemple.com" maxlength="150"
                               class="lx-input @error('contact_email') error @enderror">
                        @error('contact_email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Téléphone</label>
                        <input type="tel" name="contact_phone" value="{{ old('contact_phone') }}"
                               placeholder="+212 6XX XXX XXX" maxlength="30"
                               class="lx-input">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Poste / Fonction</label>
                    <input type="text" name="contact_position" value="{{ old('contact_position') }}"
                           placeholder="Ex: Directeur Commercial" maxlength="100"
                           class="lx-input">
                </div>
            </div>

            {{-- ── Deadline + Qualification ── --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                        Deadline <span class="text-red-400">*</span>
                    </label>
                    <input type="date" name="deadline" value="{{ old('deadline') }}"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" required
                           class="lx-input @error('deadline') error @enderror">
                    @error('deadline') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                        Qualification <span class="text-red-400">*</span>
                    </label>
                    <input type="hidden" name="qualification" id="qualInput" value="{{ old('qualification', '') }}" required>
                    <div class="flex gap-2">
                        @foreach(\App\Models\Lead::$qualificationConfig as $key => $qc)
                        <button type="button"
                                class="qual-btn {{ old('qualification') === $key ? 'selected-'.$key : '' }}"
                                data-qual="{{ $key }}"
                                onclick="selectQual('{{ $key }}')"
                                title="{{ $qc['label'] }}">
                            <div class="text-lg">{{ $qc['icon'] }}</div>
                            <div class="text-xs font-semibold mt-0.5 {{ $qc['textClass'] }}">{{ $qc['label'] }}</div>
                        </button>
                        @endforeach
                    </div>
                    @error('qualification') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ── Notes / Description ── --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Notes</label>
                <textarea name="description" rows="3" maxlength="2000"
                          placeholder="Contexte, besoins spécifiques, historique de la relation…"
                          class="lx-textarea">{{ old('description') }}</textarea>
            </div>

            {{-- ── Actions ── --}}
            <div class="flex gap-3 pt-2">
                <button type="button"
                        onclick="document.getElementById('sendLeadModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition">
                    Annuler
                </button>
                <button type="submit"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition flex items-center justify-center gap-2"
                        style="background:#111827;" onmouseover="this.style.background='#1F2937'" onmouseout="this.style.background='#111827'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                    Envoyer le Lead
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    /* ── Tab switching ── */
    function switchTab(tab) {
        ['received', 'sent'].forEach(t => {
            document.getElementById('panel-' + t).classList.toggle('hidden', t !== tab);
            document.getElementById('tab-' + t).classList.toggle('active', t === tab);
        });
    }

    /* ── Member autocomplete ── */
    function showMemberDropdown() {
        document.getElementById('memberDropdown').classList.remove('hidden');
    }
    function hideMemberDropdown() {
        document.getElementById('memberDropdown').classList.add('hidden');
    }
    function filterMembers(q) {
        const term     = q.toLowerCase().trim();
        const options  = document.querySelectorAll('.member-option');
        const noResult = document.getElementById('noMemberResult');
        let found = 0;
        options.forEach(opt => {
            const visible = !term || opt.dataset.name.toLowerCase().includes(term);
            opt.classList.toggle('hidden', !visible);
            if (visible) found++;
        });
        noResult.classList.toggle('hidden', found > 0);
        showMemberDropdown();
        if (!term) document.getElementById('receiverId').value = '';
    }
    function selectMember(btn) {
        const id      = btn.dataset.id;
        const name    = btn.dataset.name;
        const balance = parseInt(btn.dataset.balance, 10);
        if (balance < 1) {
            alert('Ce membre ne peut pas recevoir de leads pour le moment (solde insuffisant).');
            return;
        }
        document.getElementById('receiverId').value     = id;
        document.getElementById('receiverSearch').value = name;
        hideMemberDropdown();
        const input = document.getElementById('receiverSearch');
        input.style.borderColor = '#1E8F88';
        input.style.boxShadow   = '0 0 0 3px rgba(30,143,136,.1)';
        setTimeout(() => { input.style.borderColor = ''; input.style.boxShadow = ''; }, 1500);
    }
    document.getElementById('sendLeadModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });

    /* ── Qualification selector ── */
    function selectQual(key) {
        document.getElementById('qualInput').value = key;
        document.querySelectorAll('.qual-btn').forEach(btn => {
            btn.className = 'qual-btn';
            if (btn.dataset.qual === key) btn.classList.add('selected-' + key);
        });
    }
    // Restore on validation error
    const savedQual = document.getElementById('qualInput').value;
    if (savedQual) selectQual(savedQual);

    /* ── Rating star widgets (activated per card) ── */
    function submitRating(leadId) {
        const form = document.getElementById('rateForm-' + leadId);
        const qEl  = form.querySelector('input[name="quality"]:checked');
        const rlEl = form.querySelector('input[name="relevance"]:checked');
        const rxEl = form.querySelector('input[name="reactivity"]:checked');
        const quality    = qEl  ? qEl.value  : null;
        const relevance  = rlEl ? rlEl.value : null;
        const reactivity = rxEl ? rxEl.value : null;

        if (!quality || !relevance || !reactivity) {
            alert('Veuillez noter les 3 critères avant de valider.');
            return;
        }
        form.submit();
    }
</script>
@endpush
