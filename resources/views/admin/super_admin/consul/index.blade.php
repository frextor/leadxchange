@extends('admin.layouts.admin')
@section('title', 'Demandes Ambassadeur')
@section('page-title', 'Ambassadeurs')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Administration</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Demandes de rôle Ambassadeur</h1>
        <p class="text-sm text-gray-400 mt-1">Historique de toutes les demandes. Les demandes en attente sont traitées depuis la page <a href="{{ route('admin.super.consuls.manage') }}" class="text-indigo-600 hover:underline font-medium">Consuls</a>.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-2xl px-5 py-3 text-sm">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
    {{ session('error') }}
</div>
@endif

{{-- Tabs --}}
<div class="flex items-center gap-1 mb-5 bg-white rounded-2xl border border-gray-100 shadow-sm p-1.5 w-fit">
    @foreach(['pending' => 'En attente', 'approved' => 'Approuvées', 'rejected' => 'Refusées'] as $s => $label)
    <a href="{{ route('admin.super.consul.index', ['status' => $s]) }}"
       class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition
              {{ $status === $s ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-50' }}">
        {{ $label }}
        @if($counts[$s] > 0)
        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full
                     {{ $status === $s ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">
            {{ $counts[$s] }}
        </span>
        @endif
    </a>
    @endforeach
</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    @forelse($requests as $req)
    <div class="flex items-start gap-4 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">

        {{-- Avatar --}}
        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
             style="background:linear-gradient(135deg,#6366F1,#4338CA);">
            {{ strtoupper(substr($req->user->first_name, 0, 1)) }}
        </div>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <p class="font-semibold text-gray-900">{{ $req->user->first_name }} {{ $req->user->last_name }}</p>
                <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 font-semibold">
                    {{ $req->user->subscription?->plan?->label ?? 'Sans plan' }}
                </span>
                @if($req->user->city)
                <span class="text-xs text-gray-400">📍 {{ $req->user->city->name }}</span>
                @endif
            </div>
            <p class="text-xs text-gray-400 mt-0.5">{{ $req->user->email }} · Demande le {{ $req->created_at->format('d/m/Y') }}</p>
            @if($req->isRejected() && $req->rejection_reason)
            <p class="text-xs text-red-500 mt-1">Raison du refus : {{ $req->rejection_reason }}</p>
            @endif
            @if($req->isApproved())
            <p class="text-xs text-emerald-600 mt-1">Approuvé par {{ $req->validator?->first_name }} le {{ $req->validated_at?->format('d/m/Y') }}</p>
            @endif
        </div>

        {{-- Status badge --}}
        <div class="flex-shrink-0">
            @if($req->isPending())
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> En attente
            </span>
            @elseif($req->isApproved())
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approuvée
            </span>
            @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-600">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Refusée
            </span>
            @endif
        </div>

        {{-- Actions --}}
        @if($req->isPending())
        <div class="flex items-center gap-2 flex-shrink-0">
            <form method="POST" action="{{ route('admin.super.consul.approve', $req) }}">
                @csrf
                <button type="submit"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold text-white hover:opacity-90 transition"
                        style="background:#059669;">
                    ✓ Approuver
                </button>
            </form>
            <button type="button"
                    onclick="openRejectModal({{ $req->id }})"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold border border-red-200 text-red-600 hover:bg-red-50 transition">
                ✕ Refuser
            </button>
        </div>
        @endif

    </div>
    @empty
    <div class="px-5 py-16 text-center">
        <p class="text-sm text-gray-400 font-medium">Aucune demande {{ $status === 'pending' ? 'en attente' : ($status === 'approved' ? 'approuvée' : 'refusée') }}.</p>
    </div>
    @endforelse
</div>

@if($requests->hasPages())
<div class="mt-4">{{ $requests->links() }}</div>
@endif

{{-- Reject modal --}}
<div id="reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <h3 class="text-base font-bold text-gray-900 mb-4">Refuser la demande</h3>
        <form id="reject-form" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Raison (optionnelle)</label>
                <textarea name="reason" rows="3"
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-red-300 focus:ring-2 focus:ring-red-50 transition resize-none"
                          placeholder="Expliquez la raison du refus…"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 transition"
                        style="background:#DC2626;">Confirmer le refus</button>
                <button type="button" onclick="closeRejectModal()"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">Annuler</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const rejectUrlTemplate = '{{ route("admin.super.consul.reject", ":id") }}';
function openRejectModal(id) {
    document.getElementById('reject-form').action = rejectUrlTemplate.replace(':id', id);
    const m = document.getElementById('reject-modal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeRejectModal() {
    const m = document.getElementById('reject-modal');
    m.classList.add('hidden'); m.classList.remove('flex');
}
document.getElementById('reject-modal').addEventListener('click', function(e) { if(e.target===this) closeRejectModal(); });
</script>
@endpush
