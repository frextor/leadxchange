@extends('admin.layouts.admin')
@section('title', 'Ambassadeurs')
@section('page-title', 'Gestion des Ambassadeurs')
@section('page-subtitle', 'Approbation et suivi des demandes')

@section('content')
<div class="py-6 space-y-4">

    {{-- Status tabs --}}
    <div class="flex gap-2">
        @foreach(['pending' => ['Pending', '#F59E0B', '#FFFBEB'], 'approved' => ['Approuvés', '#10B981', '#ECFDF5'], 'rejected' => ['Refusés', '#EF4444', '#FEF2F2']] as $s => [$label, $color, $bg])
        <a href="{{ route('admin.super.ambassadors.index', ['status' => $s]) }}"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition border"
           style="{{ $status === $s ? "background:{$bg};color:{$color};border-color:{$color};" : 'background:white;color:#6B7280;border-color:#E5E7EB;' }}">
            {{ $label }}
            <span class="text-xs font-bold px-1.5 py-0.5 rounded-full"
                  style="{{ $status === $s ? "background:{$color};color:white;" : 'background:#F3F4F6;color:#6B7280;' }}">
                {{ $counts[$s] }}
            </span>
        </a>
        @endforeach

        @if(request('region_id'))
        <a href="{{ route('admin.super.ambassadors.index', ['status' => $status]) }}"
           class="ml-auto px-3 py-2 rounded-xl text-xs text-gray-400 border border-gray-200 hover:bg-gray-50 transition">
            Effacer filtre
        </a>
        @endif
    </div>

    {{-- Region filter --}}
    <form method="GET" action="{{ route('admin.super.ambassadors.index') }}" class="flex items-center gap-3">
        <input type="hidden" name="status" value="{{ $status }}">
        <select name="region_id" onchange="this.form.submit()"
                class="h-9 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-amber-400 transition" style="appearance:none;">
            <option value="">Toutes les régions</option>
            @foreach($regions as $r)
            <option value="{{ $r->id }}" {{ request('region_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
            @endforeach
        </select>
    </form>

    {{-- List --}}
    <div class="space-y-3">
        @forelse($applicants as $user)
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#34d4bf,#1E8F88);">
                    {{ strtoupper(substr($user->first_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-semibold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</p>
                        @if($user->region)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-600">{{ $user->region->name }}</span>
                        @endif
                        @if($user->ambassador_status === 'pending')
                        <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-semibold">⏳ En attente</span>
                        @elseif($user->ambassador_status === 'approved')
                        <span class="text-xs px-2 py-0.5 rounded-full bg-green-50 text-green-700 font-semibold">✓ Approuvé</span>
                        @elseif($user->ambassador_status === 'rejected')
                        <span class="text-xs px-2 py-0.5 rounded-full bg-red-50 text-red-600 font-semibold">✗ Refusé</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $user->email }}</p>
                    @if($user->ambassador_requested_at)
                    <p class="text-xs text-gray-400 mt-1">Demande : {{ $user->ambassador_requested_at->format('d/m/Y à H:i') }}</p>
                    @endif
                    @if($user->ambassador_status === 'rejected' && $user->ambassador_rejection_reason)
                    <p class="text-xs text-red-500 mt-1">Motif : {{ $user->ambassador_rejection_reason }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('admin.super.ambassadors.show', $user) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                        Voir profil
                    </a>
                    @if($user->ambassador_status === 'pending')
                    <form method="POST" action="{{ route('admin.super.ambassadors.approve', $user) }}">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition"
                                style="background:#10B981;">
                            Approuver
                        </button>
                    </form>
                    <button type="button"
                            onclick="document.getElementById('reject-{{ $user->id }}').classList.toggle('hidden')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition">
                        Refuser
                    </button>
                    @endif
                </div>
            </div>

            {{-- Reject form --}}
            @if($user->ambassador_status === 'pending')
            <div id="reject-{{ $user->id }}" class="hidden mt-4 pt-4 border-t border-gray-100">
                <form method="POST" action="{{ route('admin.super.ambassadors.reject', $user) }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="reason" placeholder="Motif du refus (obligatoire)" required
                           class="flex-1 h-9 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-red-400 transition">
                    <button type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-red-500 hover:bg-red-600 transition">
                        Confirmer le refus
                    </button>
                </form>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-gray-200 p-10 text-center text-gray-400">
            Aucune demande {{ $status === 'pending' ? 'en attente' : ($status === 'approved' ? 'approuvée' : 'refusée') }}.
        </div>
        @endforelse
    </div>

    @if($applicants->hasPages())
    <div>{{ $applicants->withQueryString()->links() }}</div>
    @endif

</div>
@endsection
