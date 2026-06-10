@extends('admin.layouts.admin')

@section('title', 'Modération vidéos')
@section('page-title', 'Vidéos de présentation')
@section('page-subtitle', 'File de modération')

@section('content')

{{-- Tabs --}}
<div class="flex gap-1 mb-6 bg-white rounded-xl border border-gray-200 p-1 w-fit">
    @foreach(['pending'=>['En attente','#F59E0B'],'approved'=>['Approuvées','#059669'],'rejected'=>['Rejetées','#DC2626']] as $tab=>[$label,$color])
    <a href="{{ request()->fullUrlWithQuery(['status' => $tab]) }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $status === $tab ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-700' }}">
        {{ $label }}
        <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full font-bold"
              style="{{ $status === $tab ? 'background:rgba(255,255,255,.2);color:white;' : 'background:#F3F4F6;color:#6B7280;' }}">
            {{ $counts[$tab] }}
        </span>
    </a>
    @endforeach
</div>

@if($profiles->isEmpty())
<div class="bg-white rounded-xl border border-gray-200 py-16 text-center">
    <svg class="mx-auto mb-3 text-gray-300" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
    <p class="text-sm text-gray-400">Aucune vidéo {{ $status === 'pending' ? 'en attente de modération' : ($status === 'approved' ? 'approuvée' : 'rejetée') }}.</p>
</div>
@else
<div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
    @foreach($profiles as $profile)
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        {{-- Video player --}}
        <div class="bg-black aspect-video">
            <video controls class="w-full h-full object-contain" preload="metadata"
                   src="{{ $profile->presentation_video_url }}">
            </video>
        </div>

        {{-- User info --}}
        <div class="p-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background:linear-gradient(135deg,#34d4bf,#1E8F88);">
                    {{ strtoupper(substr($profile->user->first_name,0,1).substr($profile->user->last_name,0,1)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $profile->user->first_name }} {{ $profile->user->last_name }}</p>
                    <a href="{{ route('admin.users.show', $profile->user) }}" class="text-xs text-teal-600 hover:underline">Voir le profil →</a>
                </div>
            </div>

            <p class="text-xs text-gray-400 mb-4">
                Envoyée le {{ $profile->presentation_video_uploaded_at?->format('d/m/Y à H:i') ?? '—' }}
            </p>

            @if($status === 'rejected' && $profile->presentation_video_rejection_reason)
            <div class="mb-3 px-3 py-2 rounded-lg text-xs" style="background:#FEF2F2;color:#991B1B;">
                Raison : {{ $profile->presentation_video_rejection_reason }}
            </div>
            @endif

            @if($status === 'pending')
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.videos.approve', $profile) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-2 rounded-lg text-sm font-semibold text-white" style="background:#059669;">
                        Approuver
                    </button>
                </form>
                <button onclick="openReject({{ $profile->id }})" class="flex-1 py-2 rounded-lg text-sm font-semibold border" style="border-color:#FECACA;color:#DC2626;background:#FEF2F2;">
                    Rejeter
                </button>
            </div>
            @elseif($status === 'approved')
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold" style="background:#ECFDF5;color:#065F46;">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                Approuvée le {{ $profile->presentation_video_reviewed_at?->format('d/m/Y') }}
            </span>
            @elseif($status === 'rejected')
            <form method="POST" action="{{ route('admin.videos.approve', $profile) }}">
                @csrf
                <button type="submit" class="w-full py-2 rounded-lg text-sm font-semibold text-white" style="background:#1E8F88;">
                    Réapprouver
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>

@if($profiles->hasPages())
<div class="mt-5">{{ $profiles->links() }}</div>
@endif
@endif

{{-- Reject modal --}}
<div id="rejectModal" class="fixed inset-0 z-50 hidden" style="background:rgba(0,0,0,.45);">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-base font-bold text-gray-900 mb-4">Rejeter la vidéo</h3>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Raison du rejet</label>
                    <textarea name="reason" rows="3" required maxlength="500"
                              placeholder="Expliquez pourquoi cette vidéo est rejetée…"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-red-400 resize-none"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#DC2626;">Confirmer le rejet</button>
                    <button type="button" onclick="closeReject()" class="flex-1 py-2.5 rounded-lg text-sm font-medium text-gray-500 border border-gray-200 hover:bg-gray-50">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const rejectRoutes = {
    @foreach($profiles as $profile)
    {{ $profile->id }}: '{{ route('admin.videos.reject', $profile) }}',
    @endforeach
};
function openReject(id) {
    document.getElementById('rejectForm').action = rejectRoutes[id];
    document.getElementById('rejectModal').classList.remove('hidden');
}
function closeReject() {
    document.getElementById('rejectModal').classList.add('hidden');
}
document.getElementById('rejectModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeReject(); });
</script>
@endpush
@endsection
