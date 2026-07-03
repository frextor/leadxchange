@extends('admin.layouts.admin')
@section('title', 'Licences Entreprise')

@section('content')
<div class="p-6 max-w-6xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Licences Entreprise</h1>
            <p class="text-sm text-gray-500 mt-0.5">Attribution des packs multi-licences aux comptes entreprises.</p>
        </div>
        <a href="{{ route('admin.super.enterprise.create') }}"
           class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white"
           style="background:linear-gradient(135deg,#6366F1,#4338CA);">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            Nouvelle licence
        </a>
    </div>

    @if(session('success'))
    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-left">
                    <th class="px-5 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Titulaire</th>
                    <th class="px-5 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Licences</th>
                    <th class="px-5 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Expiration</th>
                    <th class="px-5 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-5 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($licenses as $license)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition {{ $loop->last ? 'border-0' : '' }}">
                    <td class="px-5 py-4">
                        <p class="font-semibold text-gray-900">{{ $license->holder?->first_name }} {{ $license->holder?->last_name }}</p>
                        <p class="text-xs text-gray-400">{{ $license->holder?->email }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold" style="color:#6366F1;">{{ $license->seats_used }}</span>
                            <span class="text-gray-300">/</span>
                            <span class="text-sm font-semibold text-gray-700">{{ $license->seats_total }}</span>
                        </div>
                        @php
                            $pct = $license->seats_total > 0 ? min(100, round($license->seats_used / $license->seats_total * 100)) : 0;
                            $barW = 'width:' . $pct . '%';
                            $barCls = $pct >= 90 ? 'bg-red-500' : 'bg-indigo-500';
                        @endphp
                        <div class="h-1.5 rounded-full bg-gray-100 mt-1 w-20 overflow-hidden">
                            <div class="h-full rounded-full {{ $barCls }}" style="{{ $barW }}"></div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-500">
                        {{ $license->expires_at ? $license->expires_at->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-5 py-4">
                        @if($license->isExpired())
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-600">Expiré</span>
                        @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">Actif</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.super.enterprise.edit', $license->id) }}"
                               class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                                Modifier
                            </a>
                            <form method="POST" action="{{ route('admin.super.enterprise.destroy', $license->id) }}"
                                  onsubmit="return confirm('Supprimer ce pack ? Tous les membres passeront en plan Basic.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-red-200 text-red-500 hover:bg-red-50 transition">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center text-gray-400 text-sm">
                        Aucune licence entreprise configurée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($licenses->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $licenses->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
