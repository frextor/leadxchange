@php
    $sc             = $lead->status_config;
    $qc             = $lead->qualification_config;
    $isSent         = $mode === 'sent';
    $other          = $isSent ? $lead->receiver : $lead->sender;
    $otherName      = $other ? $other->first_name . ' ' . $other->last_name : 'Inconnu';
    $canAct         = !$isSent && $lead->isNew();
    $canConvert     = !$isSent && $lead->isAccepted();
    $canRate        = !$isSent && ($lead->isAccepted() || $lead->isConverted())
                      && !$lead->hasRatingBy($currentUser->id);
    $canReport      = !$isSent && !$lead->isNew() && !$lead->isRejected() && !$lead->isFraudReported();
    $rating         = !$isSent ? $lead->ratings->firstWhere('rater_id', $currentUser->id) : null;
    $avgRating      = $lead->average_rating;
    $deadlinePassed = $lead->deadline && $lead->deadline->isPast();
    $daysLeft       = $deadlinePassed ? 0 : (int) now()->diffInDays($lead->deadline, false);
    $fraudLabels    = ['faux_profil' => 'Faux profil', 'lead_frauduleux' => 'Lead frauduleux', 'spam' => 'Spam', 'comportement_inapproprie' => 'Comportement inapproprié', 'autre' => 'Autre'];

    // J-N badge classes
    $jBadgeClass = $deadlinePassed
        ? 'bg-red-100 text-red-600'
        : ($daysLeft <= 4 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500');
    $jLabel = $deadlinePassed ? 'J+' . now()->diffInDays($lead->deadline) : 'J-' . $daysLeft;
@endphp

<div class="lead-card-wrapper" data-status="{{ $lead->status }}" data-search="{{ strtolower($lead->company_name . ' ' . ($lead->contact_name ?? '')) }}">
<div class="lead-card">

    {{-- Left qualification color bar --}}
    <div class="w-1.5 flex-shrink-0 {{ $qc['barClass'] ?? 'bg-gray-300' }}"></div>

    {{-- Main content --}}
    <div class="flex-1 p-5 min-w-0">

        {{-- Header row --}}
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-2.5 flex-wrap min-w-0">
                <span class="text-xs font-mono text-gray-400 flex-shrink-0">L-{{ $lead->id }}</span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold flex-shrink-0 {{ $qc['classes'] }}">
                    {{ $qc['icon'] }} {{ $qc['label'] }}
                </span>
                <h3 class="text-lg font-bold text-gray-900 truncate">{{ $lead->company_name }}</h3>
                @if($lead->sector)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 flex-shrink-0">{{ $lead->sector->name }}</span>
                @endif
                @if($lead->lead_type)
                @php $typeColors = ['MQL' => 'bg-indigo-100 text-indigo-700', 'SQL' => 'bg-teal-100 text-teal-700', 'SP' => 'bg-amber-100 text-amber-700']; @endphp
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold flex-shrink-0 {{ $typeColors[$lead->lead_type] ?? 'bg-gray-100 text-gray-500' }}">{{ $lead->lead_type }}</span>
                @endif
                @if($lead->isFraudReported())
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-700 flex-shrink-0">⚠️ Signalé</span>
                @endif
            </div>
            {{-- Status badge --}}
            <span class="flex-shrink-0 sdot text-xs font-semibold {{ $sc['classes'] }}" style="gap:5px;">
                <span class="w-2 h-2 rounded-full inline-block {{ $sc['dot'] }}"></span>
                {{ $sc['label'] }}
            </span>
        </div>

        {{-- Contact line --}}
        <div class="flex items-center flex-wrap gap-x-3 gap-y-1 mt-2">
            <span class="text-sm font-semibold text-gray-700">{{ $lead->contact_name }}</span>
            @if($lead->contact_position)
            <span class="text-sm text-gray-400">{{ $lead->contact_position }}</span>
            @endif
            @if($lead->contact_email)
            <a href="mailto:{{ $lead->contact_email }}" class="inline-flex items-center gap-1 text-sm text-teal-600 hover:underline">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/></svg>
                {{ $lead->contact_email }}
            </a>
            @endif
            @if($lead->contact_phone)
            <a href="tel:{{ $lead->contact_phone }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                {{ $lead->contact_phone }}
            </a>
            @endif
        </div>

        {{-- Description --}}
        @if($lead->description)
        <p class="mt-2.5 text-sm text-gray-500 leading-relaxed line-clamp-2">{{ $lead->description }}</p>
        @endif

        {{-- Average rating --}}
        @if($avgRating !== null)
        <div class="flex items-center gap-1 mt-2.5">
            @for($i = 1; $i <= 5; $i++)
            <svg width="12" height="12" viewBox="0 0 24 24" fill="{{ $i <= round($avgRating) ? '#F59E0B' : 'none' }}" stroke="#F59E0B" stroke-width="2">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            @endfor
            <span class="text-xs text-gray-400 ml-0.5">{{ number_format($avgRating, 1) }}</span>
        </div>
        @endif

        {{-- Actions --}}
        <div class="flex items-center gap-3 mt-4 flex-wrap">

            @if($canAct)
            <form method="POST" action="{{ route('leads.accept', $lead->id) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold text-white bg-gray-900 hover:bg-gray-700 transition">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Accepter
                </button>
            </form>
            <form method="POST" action="{{ route('leads.reject', $lead->id) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Refuser
                </button>
            </form>
            @endif

            @if($canConvert)
            <form method="POST" action="{{ route('leads.convert', $lead->id) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 transition">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                    Marquer converti
                </button>
            </form>
            @endif

            <a href="{{ route('leads.show', $lead->id) }}"
               class="inline-flex items-center gap-1 text-sm text-gray-400 hover:text-gray-700 transition">
                → Détail
            </a>

            @if($canRate)
            <button type="button" onclick="document.getElementById('ratePanel-{{ $lead->id }}').classList.toggle('hidden')"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 hover:text-amber-700 transition">
                ★ Noter ce lead
            </button>
            @elseif($rating)
            <span class="text-xs text-gray-400 flex items-center gap-1">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="#10B981" stroke="none"><path d="M20 6L9 17l-5-5"/></svg>
                Noté · {{ number_format($rating->average_note, 1) }}/5
            </span>
            @endif

            @if($canReport)
            <button type="button" onclick="document.getElementById('reportPanel-{{ $lead->id }}').classList.toggle('hidden')"
                    class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-orange-500 transition ml-auto">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                Signaler
            </button>
            @endif
        </div>

        {{-- Rating panel (inline) --}}
        @if($canRate)
        <div id="ratePanel-{{ $lead->id }}" class="hidden mt-3 rounded-xl border border-amber-200 bg-amber-50 p-3">
            <p class="text-xs font-semibold text-amber-700 mb-2">Notez ce lead <span class="font-normal text-amber-600">(30 jours)</span></p>
            <form method="POST" action="{{ route('leads.rate', $lead->id) }}" id="rateForm-{{ $lead->id }}">
                @csrf
                <div class="space-y-1.5">
                    @foreach(['quality' => 'Qualité', 'relevance' => 'Pertinence', 'reactivity' => 'Réactivité'] as $field => $label)
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600 w-24">{{ $label }}</span>
                        <div class="flex gap-0.5" id="stars-{{ $lead->id }}-{{ $field }}">
                            @for($s = 1; $s <= 5; $s++)
                            <button type="button" class="star-btn text-xl leading-none"
                                    data-lead="{{ $lead->id }}" data-field="{{ $field }}" data-val="{{ $s }}"
                                    style="color:#D1D5DB;"
                                    onclick="rateStar('{{ $lead->id }}','{{ $field }}',{{ $s }})">★</button>
                            @endfor
                            <input type="hidden" name="{{ $field }}" id="val-{{ $lead->id }}-{{ $field }}" value="">
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="flex gap-2 mt-2">
                    @foreach(['MQL' => 'MQL', 'SQL' => 'SQL', 'SP' => 'SP'] as $typeKey => $typeLabel)
                    <label class="flex-1 text-center cursor-pointer">
                        <input type="radio" name="lead_type" value="{{ $typeKey }}" class="sr-only card-type-radio" data-lead="{{ $lead->id }}">
                        <span class="block border border-gray-200 rounded-lg py-1 text-[10px] font-bold text-gray-500 card-type-label transition cursor-pointer">{{ $typeLabel }}</span>
                    </label>
                    @endforeach
                </div>
                <button type="button" onclick="submitRating('{{ $lead->id }}')"
                        class="mt-2 w-full py-1.5 rounded-lg text-xs font-semibold text-white bg-amber-500 hover:bg-amber-600 transition">
                    Valider la notation
                </button>
            </form>
        </div>
        @endif

        {{-- Report panel (inline) --}}
        @if($canReport)
        <div id="reportPanel-{{ $lead->id }}" class="hidden mt-2">
            <form method="POST" action="{{ route('leads.report', $lead->id) }}"
                  class="flex items-center gap-2">
                @csrf
                <select name="fraud_reason" required
                        class="flex-1 h-8 px-2 rounded-lg border border-orange-200 bg-white text-xs text-gray-700 outline-none focus:border-orange-400">
                    <option value="">— Motif du signalement —</option>
                    <option value="faux_profil">Faux profil</option>
                    <option value="lead_frauduleux">Lead frauduleux</option>
                    <option value="spam">Spam</option>
                    <option value="comportement_inapproprie">Comportement inapproprié</option>
                    <option value="autre">Autre</option>
                </select>
                <button type="submit"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-orange-500 hover:bg-orange-600 transition flex-shrink-0">
                    Confirmer
                </button>
            </form>
        </div>
        @endif

    </div>

    {{-- Right sidebar --}}
    <div class="w-44 flex-shrink-0 border-l border-gray-100 p-4 space-y-4">

        {{-- Sender / Receiver --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{{ $isSent ? 'À' : 'De' }}</p>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#1E8F88,#34d4bf);">
                    {{ strtoupper(substr($other->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($other->last_name ?? '', 0, 1)) }}
                </div>
                <span class="text-sm font-medium text-gray-800 leading-tight">{{ $otherName }}</span>
            </div>
        </div>

        {{-- Deadline --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Échéance</p>
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-sm text-gray-700">{{ $lead->deadline->isoFormat('D MMM') }}</span>
                <span class="text-xs px-1.5 py-0.5 rounded font-semibold {{ $jBadgeClass }}">{{ $jLabel }}</span>
            </div>
        </div>

        {{-- Sent date --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Envoyé</p>
            <span class="text-sm text-gray-600">{{ $lead->created_at->isoFormat('D MMM') }}</span>
        </div>

    </div>

</div>
</div>

<script>
(function() {
    function rateStar(leadId, field, val) {
        document.getElementById('val-' + leadId + '-' + field).value = val;
        document.getElementById('stars-' + leadId + '-' + field).querySelectorAll('.star-btn').forEach(function(btn) {
            btn.style.color = parseInt(btn.dataset.val) <= val ? '#F59E0B' : '#D1D5DB';
        });
    }
    window.rateStar = window.rateStar || rateStar;

    // Lead type radio
    document.querySelectorAll('.card-type-radio').forEach(function(r){
        r.addEventListener('change',function(){
            const leadId = this.dataset.lead;
            document.querySelectorAll('.card-type-radio[data-lead="'+leadId+'"]').forEach(function(x){
                x.nextElementSibling.style.cssText='';
            });
            this.nextElementSibling.style.cssText='background:#FEF3C7;border-color:#D97706;color:#92400E;';
        });
    });
})();
</script>
