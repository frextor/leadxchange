@extends('layouts.dashboard')

@section('title', $lead->company_name . ' — LeadXchange')

@push('styles')
<style>
    .info-label { font-size: 11px; font-weight: 700; color: #9CA3AF; letter-spacing: .07em; text-transform: uppercase; }
    .info-value { font-size: 14px; color: #111827; }
    .section-card { background: white; border-radius: 16px; border: 1px solid #E5E7EB; overflow: hidden; }
    .star-row-btn { font-size: 22px; color: #D1D5DB; cursor: pointer; transition: color .1s; line-height: 1; }
    .star-row-btn:hover { color: #F59E0B; }
</style>
@endpush

@section('content')
@php
    $sc    = $lead->status_config;
    $qc    = $lead->qualification_config;
    $other = $isSent ? $lead->receiver : $lead->sender;
    $me    = $isSent ? $lead->sender   : $lead->receiver;

    $deadlinePassed = $lead->deadline && $lead->deadline->isPast();
    $daysLeft       = $deadlinePassed ? 0 : (int) now()->diffInDays($lead->deadline, false);
    $jLabel         = $deadlinePassed ? 'J+' . now()->diffInDays($lead->deadline) : 'J-' . $daysLeft;
    $jClass         = $deadlinePassed ? 'bg-red-100 text-red-600' : ($daysLeft <= 4 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500');

    $canRate    = !$isSent && ($lead->isAccepted() || $lead->isConverted()) && !$lead->hasRatingBy($user->id);
    $myRating   = !$isSent ? $lead->ratings->firstWhere('rater_id', $user->id) : null;
    $avgRating  = $lead->average_rating;
    $canConvert = $isSent && $lead->isAccepted();
    $canAct     = !$isSent && $lead->isNew();
    $canReport  = !$isSent && !$lead->isNew() && !$lead->isRejected() && !$lead->isFraudReported();

    // Badge config for the sender sidebar
    $badgeCfg = ['bronze' => ['icon' => '🏆', 'label' => 'Bronze', 'class' => 'text-yellow-700'],
                 'argent' => ['icon' => '🏆', 'label' => 'Argent', 'class' => 'text-gray-500'],
                 'or'     => ['icon' => '🥇', 'label' => 'Or',     'class' => 'text-amber-500']];
    $senderBadge = $badgeCfg[$other->badge_level ?? 'bronze'];

    // Timeline
    $timeline = [];
    $senderName = ($lead->sender->first_name ?? '') . ' ' . ($lead->sender->last_name ?? '');
    $receiverName = ($lead->receiver->first_name ?? '') . ' ' . ($lead->receiver->last_name ?? '');

    $timeline[] = [
        'date'  => $lead->created_at,
        'text'  => $isSent ? "Vous avez envoyé ce lead à {$receiverName}" : "{$senderName} vous a envoyé ce lead",
        'color' => 'gray',
    ];
    if ($lead->status !== 'new') {
        $actionLabels = ['accepted' => 'accepté', 'rejected' => 'refusé', 'converted' => 'converti'];
        $action = $actionLabels[$lead->status] ?? $lead->status;
        $timeline[] = [
            'date'  => $lead->updated_at,
            'text'  => $isSent ? "Le destinataire a {$action} le lead" : "Vous avez {$action} le lead",
            'color' => $lead->status === 'rejected' ? 'red' : 'teal',
        ];
    }
    if ($lead->ratings->isNotEmpty()) {
        $timeline[] = [
            'date'  => $lead->ratings->first()->rated_at ?? $lead->updated_at,
            'text'  => $isSent ? 'Votre lead a été noté' : 'Vous avez noté ce lead',
            'color' => 'amber',
        ];
    }
    usort($timeline, fn($a, $b) => $a['date'] <=> $b['date']);
@endphp

{{-- ── Breadcrumb / status bar ── --}}
<div class="bg-white border-b border-gray-100">
    <div class="max-w-5xl mx-auto px-6 lg:px-8 py-4 flex items-center gap-4">
        <a href="{{ route('leads.index') }}" class="text-sm text-gray-400 hover:text-gray-700 transition flex items-center gap-1.5">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Retour aux échanges
        </a>
        <span class="text-gray-200">·</span>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 text-sm font-semibold {{ $sc['classes'] }}">
                <span class="w-2 h-2 rounded-full {{ $sc['dot'] }}"></span>
                {{ $sc['label'] }}
            </span>
            <span class="text-sm text-gray-400">
                {{ $isSent ? 'envoyé à' : 'reçu de' }}
                <span class="font-medium text-gray-700">{{ $other->first_name ?? '' }} {{ $other->last_name ?? '' }}</span>
                @if($other->company)
                · <span class="text-gray-500">{{ $other->company->name }}</span>
                @endif
            </span>
        </div>

        {{-- Actions in header --}}
        <div class="ml-auto flex items-center gap-2">
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
        </div>
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="max-w-5xl mx-auto px-6 lg:px-8 mt-4">
    <div class="px-4 py-3 rounded-xl text-sm font-medium text-teal-700 bg-teal-50 border border-teal-100">{{ session('success') }}</div>
</div>
@endif
@if($errors->has('error'))
<div class="max-w-5xl mx-auto px-6 lg:px-8 mt-4">
    <div class="px-4 py-3 rounded-xl text-sm font-medium text-red-600 bg-red-50">{{ $errors->first('error') }}</div>
</div>
@endif

{{-- ── Lead title / qual row ── --}}
<div class="max-w-5xl mx-auto px-6 lg:px-8 mt-6 mb-6">
    <div class="flex items-center gap-3 flex-wrap">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold {{ $qc['classes'] }}">
            {{ $qc['icon'] }} {{ $qc['label'] }}
        </span>
        <h1 class="playfair text-3xl font-normal text-gray-900">{{ $lead->company_name }}</h1>
        @if($lead->sector)
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">{{ $lead->sector->name }}</span>
        @endif
        @if($lead->isFraudReported())
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700">⚠️ Signalé comme frauduleux</span>
        @endif
        <span class="text-xs font-mono text-gray-300 ml-1">L-{{ $lead->id }}</span>
    </div>
</div>

{{-- ── Main 2-col layout ── --}}
<div class="max-w-5xl mx-auto px-6 lg:px-8 pb-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── LEFT COLUMN (2/3) ── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Contact card --}}
            <div class="section-card">
                <div class="px-5 py-3 border-b border-gray-100">
                    <p class="info-label">Contact</p>
                </div>
                <div class="px-5 py-4 grid grid-cols-2 gap-y-4 gap-x-8">
                    <div>
                        <p class="info-label mb-0.5">Personne</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $lead->contact_name }}</p>
                        @if($lead->contact_position)
                        <p class="text-sm text-gray-500">{{ $lead->contact_position }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="info-label mb-0.5">Entreprise</p>
                        <p class="text-sm text-gray-800">{{ $lead->company_name }}</p>
                    </div>
                    @if($lead->contact_email)
                    <div>
                        <p class="info-label mb-0.5">Email</p>
                        <a href="mailto:{{ $lead->contact_email }}" class="text-sm text-teal-600 hover:underline flex items-center gap-1.5">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/></svg>
                            {{ $lead->contact_email }}
                        </a>
                    </div>
                    @endif
                    @if($lead->contact_phone)
                    <div>
                        <p class="info-label mb-0.5">Téléphone</p>
                        <p class="text-sm text-gray-700 tracking-wide">{{ $lead->contact_phone }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Notes card --}}
            @if($lead->description)
            <div class="section-card">
                <div class="px-5 py-3 border-b border-gray-100">
                    <p class="info-label">Notes de l'expéditeur</p>
                </div>
                <div class="px-5 py-4">
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $lead->description }}</p>
                </div>
            </div>
            @endif

            {{-- Rating card --}}
            @if($canRate)
            <div class="section-card border-l-4 border-teal-500" style="border-left-width:4px;">
                <div class="px-6 py-5">
                    <h3 class="playfair text-xl font-normal text-gray-900 mb-1">Notation du lead</h3>
                    <p class="text-sm text-gray-500 mb-5">Notez ce lead sur 3 critères. Au-delà de 4★ de moyenne, l'expéditeur reçoit un bonus.</p>
                    <form method="POST" action="{{ route('leads.rate', $lead->id) }}" id="showRateForm">
                        @csrf
                        <div class="space-y-4 border-t border-gray-100 pt-4">
                            @foreach(['quality' => 'Qualité', 'relevance' => 'Pertinence', 'reactivity' => 'Réactivité'] as $field => $label)
                            <div class="flex items-center justify-between">
                                <span class="info-label w-28">{{ strtoupper($label) }}</span>
                                <div class="flex items-center gap-1" id="show-stars-{{ $field }}">
                                    @for($s = 1; $s <= 5; $s++)
                                    <button type="button"
                                            class="star-row-btn"
                                            data-field="{{ $field }}"
                                            data-val="{{ $s }}"
                                            onclick="showRateStar('{{ $field }}', {{ $s }})">★</button>
                                    @endfor
                                    <input type="hidden" name="{{ $field }}" id="showVal-{{ $field }}" value="">
                                    <span class="text-sm text-gray-400 ml-2" id="showScore-{{ $field }}">— / 5</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="flex items-center justify-between mt-5 pt-4 border-t border-gray-100">
                            <div>
                                <span class="info-label">Moyenne</span>
                                <span class="text-sm font-semibold text-gray-700 ml-2" id="showAvg">—</span>
                            </div>
                            <button type="button" id="submitRateBtn" onclick="submitShowRating()"
                                    class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-gray-400 transition" disabled>
                                Soumettre la notation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @elseif($myRating)
            <div class="section-card">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                    <p class="info-label">Votre notation</p>
                    <div class="flex items-center gap-1">
                        @for($i = 1; $i <= 5; $i++)
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="{{ $i <= round($myRating->average_note) ? '#F59E0B' : 'none' }}" stroke="#F59E0B" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        @endfor
                        <span class="text-sm font-semibold text-gray-700 ml-1">{{ number_format($myRating->average_note, 1) }}/5</span>
                    </div>
                </div>
                <div class="px-5 py-4 grid grid-cols-3 gap-4">
                    @foreach(['quality' => 'Qualité', 'relevance' => 'Pertinence', 'reactivity' => 'Réactivité'] as $field => $label)
                    <div class="text-center">
                        <p class="info-label mb-1">{{ $label }}</p>
                        <div class="flex justify-center gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="{{ $i <= $myRating->$field ? '#F59E0B' : 'none' }}" stroke="#F59E0B" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                            @endfor
                        </div>
                        <span class="text-xs text-gray-500 mt-0.5 block">{{ $myRating->$field }}/5</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Report fraud (receiver, not yet reported) --}}
            @if($canReport)
            <div class="section-card">
                <div class="px-5 py-4 flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-700">Signaler ce lead comme frauduleux</p>
                        <p class="text-xs text-gray-400 mt-0.5">Une pénalité de −1 point sera appliquée à l'expéditeur.</p>
                    </div>
                    <button type="button" onclick="document.getElementById('reportFraudForm').classList.toggle('hidden')"
                            class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-orange-600 border border-orange-200 hover:bg-orange-50 transition">
                        ⚠️ Signaler
                    </button>
                </div>
                <div id="reportFraudForm" class="hidden px-5 pb-4">
                    <form method="POST" action="{{ route('leads.report', $lead->id) }}"
                          class="flex items-center gap-2">
                        @csrf
                        <select name="fraud_reason" required
                                class="flex-1 h-9 px-3 rounded-lg border border-gray-200 text-sm text-gray-700 outline-none focus:border-gray-400">
                            <option value="">— Motif du signalement —</option>
                            <option value="fausses_coordonnees">Fausses coordonnées</option>
                            <option value="besoin_inexistant">Besoin inexistant</option>
                            <option value="doublon">Doublon</option>
                        </select>
                        <button type="submit"
                                class="px-4 py-2 rounded-lg text-xs font-semibold text-white bg-orange-500 hover:bg-orange-600 transition flex-shrink-0">
                            Confirmer le signalement
                        </button>
                    </form>
                </div>
            </div>
            @endif

        </div>

        {{-- ── RIGHT COLUMN (1/3) ── --}}
        <div class="space-y-4">

            {{-- Échéance card --}}
            <div class="section-card px-5 py-5">
                <p class="info-label mb-3">Échéance</p>
                <div class="flex items-baseline gap-3">
                    <span class="playfair text-3xl font-normal text-gray-900 italic">{{ $lead->deadline->isoFormat('D MMM YY') }}</span>
                    <span class="text-sm px-2 py-0.5 rounded font-semibold {{ $jClass }}">{{ $jLabel }}</span>
                </div>
            </div>

            {{-- Sender / Receiver card --}}
            <div class="section-card px-5 py-5">
                <p class="info-label mb-3">{{ $isSent ? 'Destinataire' : 'Expéditeur' }}</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                         style="background:linear-gradient(135deg,#1E8F88,#34d4bf);">
                        {{ strtoupper(substr($other->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($other->last_name ?? '', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $other->first_name ?? '' }} {{ $other->last_name ?? '' }}</p>
                        @if($other->company)
                        <p class="text-xs text-gray-500">{{ $other->company->name ?? '' }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100">
                    <span class="text-sm {{ $senderBadge['class'] }}">{{ $senderBadge['icon'] }} {{ $senderBadge['label'] }}</span>
                    <span class="text-xs text-gray-400">·</span>
                    <span class="text-sm font-bold text-gray-700">{{ $other->points_balance ?? 0 }} <span class="text-xs font-medium text-gray-400">PTS</span></span>
                </div>
            </div>

            {{-- Historique card --}}
            <div class="section-card px-5 py-5">
                <p class="info-label mb-4">Historique</p>
                <div class="space-y-4">
                    @foreach($timeline as $event)
                    @php
                        $dotColor = match($event['color']) {
                            'teal'  => 'bg-teal-500',
                            'red'   => 'bg-red-400',
                            'amber' => 'bg-amber-400',
                            default => 'bg-gray-300',
                        };
                    @endphp
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center flex-shrink-0">
                            <div class="w-2.5 h-2.5 rounded-full mt-0.5 {{ $dotColor }}"></div>
                            @if(!$loop->last)
                            <div class="w-px flex-1 bg-gray-200 mt-1.5 mb-0"></div>
                            @endif
                        </div>
                        <div class="pb-2">
                            <p class="text-xs text-gray-400">{{ $event['date']->isoFormat('D MMM YY') }}</p>
                            <p class="text-sm text-gray-700 mt-0.5">{{ $event['text'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const rateValues = {};

    function showRateStar(field, val) {
        rateValues[field] = val;
        document.getElementById('showVal-' + field).value = val;
        document.getElementById('showScore-' + field).textContent = val + ' / 5';

        document.getElementById('show-stars-' + field).querySelectorAll('.star-row-btn').forEach(btn => {
            btn.style.color = parseInt(btn.dataset.val) <= val ? '#F59E0B' : '#D1D5DB';
        });

        // Update average
        const fields = ['quality', 'relevance', 'reactivity'];
        const filled = fields.filter(f => rateValues[f]);
        if (filled.length === fields.length) {
            const avg = (rateValues.quality + rateValues.relevance + rateValues.reactivity) / 3;
            document.getElementById('showAvg').textContent = avg.toFixed(1) + ' / 5';
            const btn = document.getElementById('submitRateBtn');
            btn.disabled = false;
            btn.className = 'px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-gray-900 hover:bg-gray-700 transition';
        }
    }

    function submitShowRating() {
        const fields = ['quality', 'relevance', 'reactivity'];
        if (!fields.every(f => rateValues[f])) {
            alert('Veuillez noter les 3 critères.');
            return;
        }
        document.getElementById('showRateForm').submit();
    }
</script>
@endpush
