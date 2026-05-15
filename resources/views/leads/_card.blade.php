@php
    $sc        = $lead->status_config;
    $qc        = $lead->qualification_config;
    $isSent    = $mode === 'sent';
    $other     = $isSent ? $lead->receiver : $lead->sender;
    $otherName = $other ? $other->first_name . ' ' . $other->last_name : 'Unknown';
    $canAct    = !$isSent && $lead->isNew();
    $canConvert= $isSent  && $lead->isAccepted();
    $canRate   = !$isSent && ($lead->isAccepted() || $lead->isConverted())
                 && !$lead->hasRatingBy($currentUser->id);
    $rating    = !$isSent ? $lead->ratings->firstWhere('rater_id', $currentUser->id) : null;
    $avgRating = $lead->average_rating;
    $deadlinePassed = $lead->deadline && $lead->deadline->isPast();
@endphp

<div class="lead-card">
    <div class="flex items-start gap-4">

        {{-- Avatar ──────────────────────────────────── --}}
        <div class="w-11 h-11 rounded-xl flex-shrink-0 flex items-center justify-center text-white font-bold text-sm"
             style="background:linear-gradient(135deg,#1E8F88,#34d4bf);">
            {{ strtoupper(substr($other->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($other->last_name ?? '', 0, 1)) }}
        </div>

        {{-- Content ─────────────────────────────────── --}}
        <div class="flex-1 min-w-0">

            {{-- Top row --}}
            <div class="flex items-start justify-between gap-3 flex-wrap">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-bold text-gray-900 text-sm">{{ $lead->company_name }}</h3>
                        {{-- Qualification badge --}}
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold {{ $qc['classes'] }}">
                            {{ $qc['icon'] }} {{ $qc['label'] }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $isSent ? 'À' : 'De' }}
                        <span class="font-medium text-gray-600">{{ $otherName }}</span>
                        · {{ $lead->created_at->diffForHumans() }}
                    </p>
                </div>
                <span class="status-badge flex-shrink-0 {{ $sc['classes'] }}">
                    <span class="status-dot {{ $sc['dot'] }}"></span>
                    {{ $sc['label'] }}
                </span>
            </div>

            {{-- Contact info row --}}
            <div class="mt-2.5 flex flex-wrap gap-x-4 gap-y-1">
                <span class="text-xs text-gray-600">
                    <span class="text-gray-400">Contact ·</span>
                    <span class="font-medium">{{ $lead->contact_name }}</span>
                    @if($lead->contact_position)
                    <span class="text-gray-400">, {{ $lead->contact_position }}</span>
                    @endif
                </span>
                @if($lead->contact_email)
                <a href="mailto:{{ $lead->contact_email }}"
                   class="text-xs text-teal-600 hover:underline">{{ $lead->contact_email }}</a>
                @endif
                @if($lead->contact_phone)
                <a href="tel:{{ $lead->contact_phone }}"
                   class="text-xs text-gray-500 hover:text-gray-700">{{ $lead->contact_phone }}</a>
                @endif
            </div>

            {{-- Deadline + description --}}
            <div class="flex items-center gap-3 mt-2">
                <span class="inline-flex items-center gap-1 text-xs {{ $deadlinePassed ? 'text-red-500 font-semibold' : 'text-gray-400' }}">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Deadline : {{ $lead->deadline->isoFormat('D MMM YYYY') }}
                    @if($deadlinePassed) (dépassée) @endif
                </span>
            </div>

            @if($lead->description)
            <p class="mt-2 text-sm text-gray-500 leading-relaxed line-clamp-2">{{ $lead->description }}</p>
            @endif

            {{-- Average rating display (if already rated) --}}
            @if($avgRating !== null)
            <div class="flex items-center gap-1.5 mt-2">
                @for($i = 1; $i <= 5; $i++)
                <svg width="13" height="13" viewBox="0 0 24 24"
                     fill="{{ $i <= round($avgRating) ? '#F59E0B' : 'none' }}"
                     stroke="#F59E0B" stroke-width="2">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
                @endfor
                <span class="text-xs text-gray-400 ml-1">{{ number_format($avgRating, 1) }}</span>
            </div>
            @endif

            {{-- ── Actions ──────────────────────────── --}}

            {{-- Accept / Decline (receiver, new lead) --}}
            @if($canAct)
            <div class="flex items-center gap-2 mt-4">
                <form method="POST" action="{{ route('leads.accept', $lead->id) }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white transition"
                            style="background:#10B981;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10B981'">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Accepter
                    </button>
                </form>
                <form method="POST" action="{{ route('leads.reject', $lead->id) }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Refuser
                    </button>
                </form>
            </div>
            @endif

            {{-- Convert (sender, accepted lead) --}}
            @if($canConvert)
            <div class="mt-4">
                <form method="POST" action="{{ route('leads.convert', $lead->id) }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white transition"
                            style="background:#1E8F88;" onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                        Marquer comme Converti
                    </button>
                </form>
            </div>
            @endif

            {{-- Rate lead (receiver, accepted/converted, not yet rated) --}}
            @if($canRate)
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3">
                <p class="text-xs font-semibold text-amber-700 mb-2">Notez ce lead <span class="font-normal text-amber-600">(30 jours pour noter)</span></p>
                <form method="POST" action="{{ route('leads.rate', $lead->id) }}" id="rateForm-{{ $lead->id }}">
                    @csrf
                    <div class="space-y-2">
                        @foreach(['quality' => 'Qualité', 'relevance' => 'Pertinence', 'reactivity' => 'Réactivité'] as $field => $label)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-600 w-24">{{ $label }}</span>
                            <div class="flex gap-1" id="stars-{{ $lead->id }}-{{ $field }}">
                                @for($s = 1; $s <= 5; $s++)
                                <button type="button"
                                        class="star-btn text-xl leading-none transition-colors"
                                        data-lead="{{ $lead->id }}"
                                        data-field="{{ $field }}"
                                        data-val="{{ $s }}"
                                        style="color:#D1D5DB;"
                                        onclick="rateStar('{{ $lead->id }}', '{{ $field }}', {{ $s }})">★</button>
                                @endfor
                                <input type="hidden" name="{{ $field }}" id="val-{{ $lead->id }}-{{ $field }}" value="">
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <button type="button"
                            onclick="submitRating('{{ $lead->id }}')"
                            class="mt-3 w-full py-2 rounded-xl text-xs font-semibold text-white transition"
                            style="background:#F59E0B;" onmouseover="this.style.background='#D97706'" onmouseout="this.style.background='#F59E0B'">
                        Valider ma notation
                    </button>
                </form>
            </div>
            @elseif($rating)
            <div class="mt-3 text-xs text-gray-400 flex items-center gap-1">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="#10B981" stroke="none"><path d="M20 6L9 17l-5-5"/></svg>
                <span>Noté · moyenne {{ number_format($rating->average_note, 1) }}/5</span>
            </div>
            @endif

        </div>
    </div>
</div>

<script>
(function() {
    function rateStar(leadId, field, val) {
        document.getElementById('val-' + leadId + '-' + field).value = val;
        const container = document.getElementById('stars-' + leadId + '-' + field);
        container.querySelectorAll('.star-btn').forEach(function(btn) {
            btn.style.color = parseInt(btn.dataset.val) <= val ? '#F59E0B' : '#D1D5DB';
        });
    }
    // Expose to global scope for onclick
    window.rateStar = window.rateStar || rateStar;
})();
</script>
