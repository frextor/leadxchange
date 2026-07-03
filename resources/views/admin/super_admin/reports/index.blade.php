@extends('admin.layouts.admin')
@section('title', 'Signalements')
@section('page-title', 'Modération')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900">Signalements (CGU §8.2)</h1>
        <p class="text-sm text-gray-400 mt-1">Comportements abusifs signalés par les membres. Traitement sous 10 jours ouvrés.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm font-medium">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- KPI + Tabs --}}
<div class="flex items-center gap-2 mb-5 bg-white rounded-2xl border border-gray-100 shadow-sm p-1.5 w-fit">
    @foreach(['pending'=>['En attente','bg-red-500'],'reviewed'=>['Examinés','bg-blue-500'],'actioned'=>['Traités','bg-emerald-500'],'dismissed'=>['Clôturés','bg-gray-400']] as $s=>[$label,$dot])
    <a href="{{ route('admin.super.reports.index', ['status'=>$s]) }}"
       class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition
              {{ $status===$s ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-50' }}">
        {{ $label }}
        @if($counts[$s]>0)
        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $status===$s ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $counts[$s] }}</span>
        @endif
    </a>
    @endforeach
</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    @forelse($reports as $report)
    <div class="border-b border-gray-50 last:border-0 p-5 hover:bg-gray-50/50 transition">
        <div class="flex items-start gap-4">

            {{-- Reported user --}}
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                 style="background:linear-gradient(135deg,#DC2626,#B91C1C);">
                {{ strtoupper(substr($report->reported?->first_name??'?',0,1)) }}
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-bold text-gray-900">{{ $report->reported?->first_name }} {{ $report->reported?->last_name }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-semibold">
                        {{ \App\Models\UserReport::REASONS[$report->reason] ?? $report->reason }}
                    </span>
                    <span class="text-[10px] text-gray-400">{{ $report->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">
                    Signalé par <strong>{{ $report->reporter?->first_name }} {{ $report->reporter?->last_name }}</strong>
                    ({{ $report->reporter?->email }})
                </p>
                @if($report->details)
                <p class="text-xs text-gray-600 mt-2 bg-gray-50 rounded-xl px-3 py-2 border border-gray-100">
                    {{ $report->details }}
                </p>
                @endif
                @if($report->admin_note)
                <p class="text-xs text-blue-600 mt-1.5 italic">Note admin : {{ $report->admin_note }}</p>
                @endif
            </div>

            {{-- Actions --}}
            @if($report->status === 'pending')
            <div class="flex-shrink-0">
                <button onclick="document.getElementById('action-{{ $report->id }}').classList.toggle('hidden')"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                    Traiter →
                </button>
            </div>
            @else
            <div class="flex-shrink-0 text-right">
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full
                    {{ $report->status==='actioned' ? 'bg-emerald-100 text-emerald-700' :
                       ($report->status==='dismissed' ? 'bg-gray-100 text-gray-500' : 'bg-blue-100 text-blue-700') }}">
                    {{ ['reviewed'=>'Examiné','actioned'=>'Traité','dismissed'=>'Clôturé'][$report->status] ?? $report->status }}
                </span>
                @if($report->reviewed_at)
                <p class="text-[10px] text-gray-400 mt-1">{{ $report->reviewed_at->format('d/m/Y') }}</p>
                @endif
            </div>
            @endif
        </div>

        {{-- Action form --}}
        @if($report->status === 'pending')
        <div id="action-{{ $report->id }}" class="hidden mt-4 ml-14">
            <form method="POST" action="{{ route('admin.super.reports.action', $report) }}"
                  class="flex flex-wrap gap-3 items-end p-4 bg-gray-50 rounded-2xl border border-gray-100">
                @csrf
                <div class="flex-1 min-w-48">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Note admin (optionnelle)</label>
                    <input type="text" name="admin_note" maxlength="500" placeholder="Observation interne…"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-gray-400 transition">
                </div>
                <input type="hidden" name="action" id="action-val-{{ $report->id }}" value="">
                <div class="flex gap-2">
                    <button type="submit" onclick="document.getElementById('action-val-{{ $report->id }}').value='reviewed'"
                            class="px-3 py-2 rounded-xl text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100 hover:bg-blue-100 transition">
                        ✓ Examiné
                    </button>
                    <button type="submit" onclick="document.getElementById('action-val-{{ $report->id }}').value='actioned'"
                            class="px-3 py-2 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100 hover:bg-emerald-100 transition">
                        ⚡ Traité
                    </button>
                    <button type="submit" onclick="document.getElementById('action-val-{{ $report->id }}').value='dismissed'"
                            class="px-3 py-2 rounded-xl text-xs font-semibold bg-gray-100 text-gray-500 border border-gray-200 hover:bg-gray-200 transition">
                        ✕ Clôturer
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
    @empty
    <div class="px-5 py-16 text-center">
        <p class="text-sm font-semibold text-gray-400">Aucun signalement {{ $status==='pending' ? 'en attente' : $status }}.</p>
    </div>
    @endforelse
</div>

@if($reports->hasPages())
<div class="mt-4">{{ $reports->links() }}</div>
@endif

@endsection
