@extends('admin.layouts.admin')
@section('title', 'Pages légales')

@section('content')
<div class="p-6 max-w-4xl mx-auto">

    <div class="mb-6">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900">Pages légales</h1>
        <p class="text-sm text-gray-400 mt-1">Modifiez le contenu des pages CGU et Politique de confidentialité. Les modifications sont publiées immédiatement.</p>
    </div>

    @if(session('success'))
    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4">
        @foreach($pages as $page)
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:linear-gradient(135deg,#EEF2FF,#E0E7FF);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">{{ $page->title }}</p>
                    <div class="flex items-center gap-3 mt-0.5">
                        <span class="text-xs text-gray-400">Slug : <code class="bg-gray-100 px-1 rounded">{{ $page->slug }}</code></span>
                        <span class="text-xs text-gray-400">Modifié le {{ $page->updated_at->format('d/m/Y à H:i') }}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ url('/legal/' . $page->slug) }}" target="_blank"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Voir
                </a>
                <a href="{{ route('admin.super.pages.edit', $page) }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white hover:opacity-90 transition"
                   style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Modifier
                </a>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
