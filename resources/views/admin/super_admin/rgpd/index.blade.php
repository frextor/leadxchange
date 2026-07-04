@extends('admin.layouts.admin')
@section('title', 'Demandes RGPD')

@section('content')
<div class="p-6 max-w-6xl mx-auto">

    <div class="mb-6">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900">Demandes RGPD</h1>
        <p class="text-sm text-gray-500 mt-1">Exercice des droits — Art. 15 à 21 du RGPD. Réponse obligatoire sous 1 mois (Art. 12).</p>
    </div>

    @if(session('success'))
    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Status tabs --}}
    <div class="flex gap-1 mb-5 bg-gray-100 rounded-xl p-1 w-fit">
        @foreach(\App\Models\RgpdRequest::STATUSES as $key => $meta)
        <a href="{{ request()->fullUrlWithQuery(['status' => $key]) }}"
           class="px-4 py-1.5 rounded-lg text-xs font-semibold transition
                  {{ $status === $key ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
            {{ $meta['label'] }}
            @if($counts[$key] > 0)
            <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold
                  {{ $status === $key ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-200 text-gray-500' }}">
                {{ $counts[$key] }}
            </span>
            @endif
        </a>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse($requests as $rgpd)
        @php $meta = \App\Models\RgpdRequest::STATUSES[$rgpd->status]; @endphp
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold
                            bg-{{ $meta['color'] }}-100 text-{{ $meta['color'] }}-700">
                            {{ $meta['label'] }}
                        </span>
                        <span class="text-xs font-semibold text-gray-700">
                            {{ \App\Models\RgpdRequest::RIGHTS[$rgpd->right_type] ?? $rgpd->right_type }}
                        </span>
                        <span class="text-xs text-gray-400">{{ $rgpd->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <p class="text-sm font-semibold text-gray-900">{{ $rgpd->user?->first_name }} {{ $rgpd->user?->last_name }}</p>
                        <span class="text-gray-300">·</span>
                        <p class="text-sm text-gray-500">{{ $rgpd->user?->email }}</p>
                    </div>
                    @if($rgpd->details)
                    <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2">{{ $rgpd->details }}</p>
                    @endif
                    @if($rgpd->admin_notes)
                    <p class="text-xs text-indigo-600 mt-2 italic">Note admin : {{ $rgpd->admin_notes }}</p>
                    @endif
                    @if($rgpd->processedBy)
                    <p class="text-xs text-gray-400 mt-1">Traité par {{ $rgpd->processedBy->first_name }} {{ $rgpd->processedBy->last_name }} le {{ $rgpd->processed_at?->format('d/m/Y') }}</p>
                    @endif
                </div>

                {{-- Action form --}}
                <div class="flex-shrink-0 w-52">
                    <form method="POST" action="{{ route('admin.super.rgpd.update', $rgpd->id) }}" class="space-y-2">
                        @csrf @method('PUT')
                        <select name="status"
                                class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-xs focus:outline-none focus:ring-2"
                                style="--tw-ring-color:#6366F1;">
                            @foreach(\App\Models\RgpdRequest::STATUSES as $key => $m)
                            <option value="{{ $key }}" {{ $rgpd->status === $key ? 'selected' : '' }}>{{ $m['label'] }}</option>
                            @endforeach
                        </select>
                        <textarea name="admin_notes" rows="2" placeholder="Note interne…"
                                  class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-xs focus:outline-none focus:ring-2 resize-none"
                                  style="--tw-ring-color:#6366F1;">{{ $rgpd->admin_notes }}</textarea>
                        <button type="submit"
                                class="w-full py-1.5 rounded-lg text-xs font-semibold text-white hover:opacity-90 transition"
                                style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                            Mettre à jour
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-gray-200 text-center py-16 text-gray-400 text-sm">
            Aucune demande {{ \App\Models\RgpdRequest::STATUSES[$status]['label'] ?? $status }}.
        </div>
        @endforelse
    </div>

    @if($requests->hasPages())
    <div class="mt-5">{{ $requests->links() }}</div>
    @endif

</div>
@endsection
