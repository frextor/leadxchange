@extends('admin.layouts.admin')

@section('title', 'Feedbacks')
@section('page-title', 'Feedbacks utilisateurs')
@section('page-subtitle', $total . ' message(s) reçu(s)')

@section('content')

{{-- KPIs --}}
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
        <p class="text-xs font-medium text-gray-400">Total</p>
        <p class="text-2xl font-bold mt-1" style="color:#2F44E0;">{{ $total }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
        <p class="text-xs font-medium text-gray-400">En attente</p>
        <p class="text-2xl font-bold mt-1" style="color:#D97706;">{{ $pending }}</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-48">
        <label class="block text-xs font-semibold text-gray-500 mb-1">Recherche</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Message, nom, email…"
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Statut</label>
        <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
            <option value="">Tous</option>
            <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>En attente</option>
            <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Traité</option>
            <option value="closed"   {{ request('status') === 'closed'   ? 'selected' : '' }}>Fermé</option>
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#2F44E0;">Filtrer</button>
        @if(request()->hasAny(['search','status']))
        <a href="{{ route('admin.feedbacks.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-500 border border-gray-200 hover:bg-gray-50">Reset</a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                <th class="px-4 py-3 text-left">Utilisateur</th>
                <th class="px-4 py-3 text-left">Message</th>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-left">Statut</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($feedbacks as $feedback)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    @if($feedback->user)
                    <a href="{{ route('admin.users.show', $feedback->user) }}" class="font-semibold text-gray-800 hover:text-teal-600">
                        {{ $feedback->user->first_name }} {{ $feedback->user->last_name }}
                    </a>
                    <p class="text-xs text-gray-400">{{ $feedback->user->email }}</p>
                    @else
                    <span class="text-gray-400 italic">Utilisateur supprimé</span>
                    @endif
                </td>
                <td class="px-4 py-3 max-w-xs">
                    <p class="text-gray-700 text-sm line-clamp-2 whitespace-pre-wrap">{{ $feedback->message }}</p>
                    @if(strlen($feedback->message) > 120)
                    <button onclick="openModal('{{ $feedback->id }}')"
                            class="mt-1 text-xs font-semibold hover:underline" style="color:#2F44E0;">
                        Voir tout →
                    </button>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">
                    {{ $feedback->created_at->format('d/m/Y H:i') }}
                </td>
                <td class="px-4 py-3">
                    @php
                        $statusConfig = [
                            'pending'  => ['label' => 'En attente', 'color' => '#D97706', 'bg' => '#FEF3C7'],
                            'reviewed' => ['label' => 'Traité',     'color' => '#059669', 'bg' => '#D1FAE5'],
                            'closed'   => ['label' => 'Fermé',      'color' => '#6B7280', 'bg' => '#F3F4F6'],
                        ];
                        $s = $statusConfig[$feedback->status] ?? $statusConfig['pending'];
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                          style="color:{{ $s['color'] }};background:{{ $s['bg'] }};">
                        {{ $s['label'] }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('admin.feedbacks.status', $feedback) }}">
                        @csrf @method('PATCH')
                        <select name="status" onchange="this.form.submit()"
                                class="border border-gray-200 rounded-lg px-2 py-1 text-xs text-gray-600 focus:outline-none focus:border-teal-400">
                            <option value="pending"  {{ $feedback->status === 'pending'  ? 'selected' : '' }}>En attente</option>
                            <option value="reviewed" {{ $feedback->status === 'reviewed' ? 'selected' : '' }}>Traité</option>
                            <option value="closed"   {{ $feedback->status === 'closed'   ? 'selected' : '' }}>Fermé</option>
                        </select>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-12 text-center text-gray-400 text-sm">Aucun feedback reçu pour le moment.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($feedbacks->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">
        {{ $feedbacks->links() }}
    </div>
    @endif
</div>
{{-- Message modals --}}
@foreach($feedbacks as $feedback)
@if(strlen($feedback->message) > 120)
<div id="modal-{{ $feedback->id }}"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     style="background:rgba(0,0,0,.45);"
     onclick="if(event.target===this) closeModal('{{ $feedback->id }}')">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                @if($feedback->user)
                <p class="font-bold text-gray-800">{{ $feedback->user->first_name }} {{ $feedback->user->last_name }}</p>
                <p class="text-xs text-gray-400">{{ $feedback->user->email }} · {{ $feedback->created_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
            <button onclick="closeModal('{{ $feedback->id }}')" class="text-gray-400 hover:text-gray-600 text-xl leading-none ml-4">&times;</button>
        </div>
        <p class="text-gray-700 text-sm whitespace-pre-wrap leading-relaxed">{{ $feedback->message }}</p>
    </div>
</div>
@endif
@endforeach

<script>
function openModal(id) {
    const m = document.getElementById('modal-' + id);
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function closeModal(id) {
    const m = document.getElementById('modal-' + id);
    m.classList.add('hidden');
    m.classList.remove('flex');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('[id^="modal-"]').forEach(m => {
            m.classList.add('hidden');
            m.classList.remove('flex');
        });
    }
});
</script>
@endsection
