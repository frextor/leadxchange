@extends('admin.layouts.admin')
@section('title', 'Templates Email')
@section('page-title', 'Templates Email')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Templates Email</h1>
        <p class="text-sm text-gray-400 mt-1">Personnalisez le contenu et l'objet de chaque email envoyé par la plateforme.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0">
        <path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
    </svg>
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-2 gap-5">
    @foreach(App\Models\EmailTemplate::TEMPLATES as $key => $meta)
    @php $dbTemplate = $templates->get($key); @endphp

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-1">{{ $key }}</p>
                <h3 class="text-base font-bold text-gray-900">{{ $meta['name'] }}</h3>
                <p class="text-xs text-gray-400 mt-1">
                    Variables : {{ implode(', ', array_map(fn($v) => '{{' . $v . '}}', $meta['variables'])) }}
                </p>
            </div>

            @if($dbTemplate)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold {{ $dbTemplate->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $dbTemplate->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                {{ $dbTemplate->is_active ? 'Actif' : 'Désactivé' }}
            </span>
            @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                Défaut
            </span>
            @endif
        </div>

        @if($dbTemplate)
        <div class="text-xs text-gray-500 bg-gray-50 rounded-xl px-4 py-2.5 font-mono truncate">
            {{ $dbTemplate->subject }}
        </div>
        @endif

        <div class="flex items-center gap-2 mt-auto pt-2 border-t border-gray-50">
            <a href="{{ route('admin.super.email-templates.edit', $key) }}"
               class="flex-1 flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
               style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Éditer
            </a>

            @if($dbTemplate)
            <form method="POST" action="{{ route('admin.super.email-templates.reset', $key) }}"
                  onsubmit="return confirm('Remettre ce template aux valeurs par défaut ?')">
                @csrf
                <button type="submit"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                        <path d="M3 3v5h5"/>
                    </svg>
                    Réinitialiser
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>

@endsection
