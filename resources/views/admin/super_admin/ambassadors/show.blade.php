@extends('admin.layouts.admin')
@section('title', 'Profil — ' . $user->first_name . ' ' . $user->last_name)
@section('page-title', $user->first_name . ' ' . $user->last_name)
@section('page-subtitle', 'Dossier Ambassadeur')

@section('content')
<div class="py-6 max-w-3xl space-y-5">

    <a href="{{ route('admin.super.ambassadors.manage') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 transition">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Retour
    </a>

    {{-- Identity --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-xl font-bold flex-shrink-0"
                 style="background:linear-gradient(135deg,#34d4bf,#1E8F88);">
                {{ strtoupper(substr($user->first_name, 0, 1)) }}
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-lg font-bold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</h2>
                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                        {{ $user->ambassador_status === 'pending' ? 'bg-amber-50 text-amber-700' : ($user->ambassador_status === 'approved' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600') }}">
                        {{ ['none'=>'Aucun','pending'=>'⏳ En attente','approved'=>'✓ Approuvé','rejected'=>'✗ Refusé'][$user->ambassador_status] }}
                    </span>
                </div>
                <p class="text-sm text-gray-400 mt-0.5">{{ $user->email }}</p>
                @if($user->profile?->job_title)
                <p class="text-sm text-gray-500 mt-1">{{ $user->profile->job_title }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Details grid --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Informations</h3>
            @foreach([
                ['Région',       $user->region?->name ?? '—'],
                ['Inscrit le',   $user->created_at->format('d/m/Y')],
                ['Points',       $user->points_balance . ' pts'],
                ['Badge',        ucfirst($user->badge_level)],
                ['Entreprise',   $user->company?->name ?? '—'],
            ] as [$label, $value])
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">{{ $label }}</span>
                <span class="font-medium text-gray-900">{{ $value }}</span>
            </div>
            @endforeach
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Dossier Ambassador</h3>
            @foreach([
                ['Statut',        ['none'=>'Aucun','pending'=>'En attente','approved'=>'Approuvé','rejected'=>'Refusé'][$user->ambassador_status]],
                ['Demande le',    $user->ambassador_requested_at?->format('d/m/Y') ?? '—'],
                ['Traité le',     $user->ambassador_reviewed_at?->format('d/m/Y') ?? '—'],
                ['Traité par',    $user->ambassadorReviewer?->first_name . ' ' . $user->ambassadorReviewer?->last_name ?: '—'],
            ] as [$label, $value])
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">{{ $label }}</span>
                <span class="font-medium text-gray-900">{{ $value }}</span>
            </div>
            @endforeach
            @if($user->ambassador_status === 'rejected' && $user->ambassador_rejection_reason)
            <div class="mt-2 pt-2 border-t border-gray-100">
                <p class="text-xs text-gray-400">Motif du refus :</p>
                <p class="text-sm text-red-600 mt-0.5">{{ $user->ambassador_rejection_reason }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Bio --}}
    @if($user->profile?->bio)
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Bio</h3>
        <p class="text-sm text-gray-600 leading-relaxed">{{ $user->profile->bio }}</p>
    </div>
    @endif

    {{-- Actions --}}
    @if($user->ambassador_status === 'pending')
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-3">
        <h3 class="text-sm font-semibold text-gray-700">Actions</h3>
        <div class="flex gap-3">
            <form method="POST" action="{{ route('admin.super.ambassadors.promote', $user) }}">
                @csrf
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                        style="background:#10B981;">
                    ✓ Approuver
                </button>
            </form>
        </div>
        <form method="POST" action="{{ route('admin.super.ambassadors.reject', $user) }}" class="flex gap-2">
            @csrf
            <input type="text" name="reason" placeholder="Motif du refus (obligatoire)" required
                   class="flex-1 h-10 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-red-400 transition">
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-red-500 hover:bg-red-600 transition">
                Refuser
            </button>
        </form>
    </div>
    @endif

</div>
@endsection
