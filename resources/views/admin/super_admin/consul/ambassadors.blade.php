@extends('admin.layouts.admin')
@section('title', 'Gestion Ambassadeurs')
@section('page-title', 'Ambassadeurs')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Administration</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Gestion des Ambassadeurs</h1>
        <p class="text-sm text-gray-400 mt-1">Nommez ou retirez le rôle Ambassadeur aux membres Premium.</p>
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

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Ambassadeurs actuels --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">Ambassadeurs actuels</h2>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $current->total() }}</span>
        </div>
        @forelse($current as $user)
        <div class="flex items-center gap-3 px-5 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                 style="background:linear-gradient(135deg,#F59E0B,#D97706);">
                {{ strtoupper(substr($user->first_name,0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $user->first_name }} {{ $user->last_name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $user->subscription?->plan?->label ?? '—' }} · {{ $user->city?->name ?? '—' }}</p>
            </div>
            <form method="POST" action="{{ route('admin.super.ambassadors.revoke', $user) }}"
                  onsubmit="return confirm('Retirer le rôle Ambassadeur à {{ $user->first_name }} ?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold border border-red-200 text-red-600 hover:bg-red-50 transition">
                    Retirer
                </button>
            </form>
        </div>
        @empty
        <p class="px-5 py-10 text-center text-sm text-gray-400">Aucun ambassadeur actuellement.</p>
        @endforelse
        @if($current->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $current->links() }}</div>
        @endif
    </div>

    {{-- Membres éligibles --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">Membres Premium éligibles</h2>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">{{ $eligible->total() }}</span>
        </div>
        @forelse($eligible as $user)
        <div class="flex items-center gap-3 px-5 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                 style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                {{ strtoupper(substr($user->first_name,0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $user->first_name }} {{ $user->last_name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $user->subscription?->plan?->label ?? '—' }} · {{ $user->city?->name ?? '—' }}</p>
            </div>
            <form method="POST" action="{{ route('admin.super.ambassadors.promote', $user) }}">
                @csrf
                <button type="submit"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold text-white hover:opacity-90 transition"
                        style="background:#F59E0B;">
                    ★ Nommer
                </button>
            </form>
        </div>
        @empty
        <p class="px-5 py-10 text-center text-sm text-gray-400">Aucun membre Premium disponible.</p>
        @endforelse
        @if($eligible->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $eligible->links() }}</div>
        @endif
    </div>

</div>

@endsection
