@extends('admin.layouts.admin')
@section('title', 'Éditer — ' . $meta['name'])
@section('page-title', 'Templates Email')

@section('content')

{{-- ── Header ── --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.super.email-templates.index') }}"
       class="w-8 h-8 flex items-center justify-center rounded-xl border border-gray-200 text-gray-400 hover:bg-gray-50 transition flex-shrink-0">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div>
        <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest">{{ $key }}</p>
        <h1 class="text-xl font-bold text-gray-900 leading-tight">{{ $meta['name'] }}</h1>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0">
        <path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
    </svg>
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-5 bg-red-50 border border-red-200 rounded-2xl px-5 py-4 text-sm text-red-800">
    <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

{{-- ── Layout: editor + sidebar ── --}}
<div class="grid grid-cols-3 gap-6">

    {{-- ── Editor (left 2/3) ── --}}
    <div class="col-span-2">
        <form id="template-form" method="POST" action="{{ route('admin.super.email-templates.update', $key) }}">
            @csrf @method('PUT')

            {{-- Subject --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Objet de l'email</label>
                <input type="text" name="subject"
                       value="{{ old('subject', $template->subject ?? $meta['default_subject']) }}"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition font-mono"
                       placeholder="{{ $meta['default_subject'] }}" required>
                <p class="text-[11px] text-gray-400 mt-1.5">Vous pouvez utiliser des variables comme <code class="bg-gray-100 px-1 rounded">{{'{{'}}name{{'}}'}}</code> dans l'objet.</p>
            </div>

            {{-- Body editor --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-4">
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Corps de l'email (HTML)</label>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="formatWrap('strong')"
                                class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 text-xs font-bold transition" title="Gras">B</button>
                        <button type="button" onclick="formatWrap('em')"
                                class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 text-xs italic transition" title="Italique">I</button>
                        <button type="button" onclick="insertSnippet('btn')"
                                class="px-2 h-7 flex items-center rounded-lg border border-gray-200 text-[10px] font-semibold text-indigo-600 hover:bg-indigo-50 transition">+ Bouton</button>
                        <button type="button" onclick="insertSnippet('divider')"
                                class="px-2 h-7 flex items-center rounded-lg border border-gray-200 text-[10px] font-semibold text-gray-500 hover:bg-gray-50 transition">+ Séparateur</button>
                    </div>
                </div>

                <textarea id="body-editor" name="body" rows="20"
                          class="w-full border border-gray-200 rounded-xl px-4 py-3 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition resize-y"
                          placeholder="Saisissez le HTML du corps de l'email..."
                          required>{{ old('body', $template->body ?? '') }}</textarea>

                <p class="text-[11px] text-gray-400 mt-1.5">
                    Classes CSS disponibles : <code class="bg-gray-100 px-1 rounded">.greeting</code> <code class="bg-gray-100 px-1 rounded">.text</code> <code class="bg-gray-100 px-1 rounded">.btn</code> <code class="bg-gray-100 px-1 rounded">.divider</code>
                </p>
            </div>

            {{-- Active toggle --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-4 mb-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-700">Utiliser ce template</p>
                    <p class="text-xs text-gray-400">Désactivez pour revenir au template Blade par défaut.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                           {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
                    <div class="w-10 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-500"></div>
                </label>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 active:scale-[.98]"
                        style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    Enregistrer
                </button>

                <button type="button" onclick="openPreview()"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                    Prévisualiser
                </button>

                <a href="{{ route('admin.super.email-templates.index') }}"
                   class="ml-auto text-sm text-gray-400 hover:text-gray-600 transition">Annuler</a>
            </div>
        </form>
    </div>

    {{-- ── Sidebar (right 1/3) ── --}}
    <div class="space-y-4">

        {{-- Variables reference --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Variables disponibles</p>
            <div class="space-y-2">
                @foreach($meta['variables'] as $var)
                <button type="button" onclick="insertVariable('{{ $var }}')"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl bg-gray-50 hover:bg-indigo-50 hover:border-indigo-200 border border-transparent text-left transition group">
                    <code class="text-xs font-mono text-indigo-600 font-semibold">{{'{{'}}{{ $var }}{{'}}'}}</code>
                    <span class="text-[10px] text-gray-400 group-hover:text-indigo-400 transition">Insérer</span>
                </button>
                @endforeach
            </div>
            <p class="text-[10px] text-gray-400 mt-3">Cliquez sur une variable pour l'insérer à la position du curseur dans l'éditeur.</p>
        </div>

        {{-- Sample data for preview --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Données de prévisualisation</p>
            <div class="space-y-1.5">
                @foreach($meta['sample'] as $var => $val)
                <div class="flex items-start gap-2 text-xs">
                    <span class="font-mono text-indigo-600 shrink-0">{{'{{'}}{{ $var }}{{'}}'}}</span>
                    <span class="text-gray-400">→</span>
                    <span class="text-gray-600 truncate">{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- CSS classes cheatsheet --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Classes CSS</p>
            <div class="space-y-2 text-xs">
                @foreach([
                    '.greeting' => 'Titre de salutation (18px, bold)',
                    '.text'     => 'Paragraphe standard (15px, 1.7 line-height)',
                    '.btn'      => 'Bouton indigo centré (à placer dans <div style="text-align:center;">)',
                    '.divider'  => 'Ligne de séparation horizontale',
                ] as $cls => $desc)
                <div>
                    <code class="bg-gray-100 px-1.5 py-0.5 rounded text-indigo-600 font-semibold">{{ $cls }}</code>
                    <p class="text-gray-400 mt-0.5">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ── Preview modal ── --}}
<div id="preview-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closePreview()"></div>
    <div class="absolute inset-4 md:inset-8 bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 flex-shrink-0">
            <p class="text-sm font-bold text-gray-700">Prévisualisation — <span id="preview-subject" class="font-normal text-gray-500"></span></p>
            <button onclick="closePreview()" class="text-gray-400 hover:text-gray-600 transition">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <iframe id="preview-frame" class="flex-1 w-full border-0" srcdoc=""></iframe>
    </div>
</div>

@endsection

@push('scripts')
<script>
const editor = document.getElementById('body-editor');

// Insert a variable at cursor position
function insertVariable(varName) {
    const val = '{{' + varName + '}}';
    const start = editor.selectionStart;
    const end   = editor.selectionEnd;
    editor.value = editor.value.substring(0, start) + val + editor.value.substring(end);
    editor.selectionStart = editor.selectionEnd = start + val.length;
    editor.focus();
}

// Wrap selection with a tag
function formatWrap(tag) {
    const start = editor.selectionStart;
    const end   = editor.selectionEnd;
    const sel   = editor.value.substring(start, end) || 'Texte';
    const wrapped = `<${tag}>${sel}</${tag}>`;
    editor.value = editor.value.substring(0, start) + wrapped + editor.value.substring(end);
    editor.focus();
}

// Insert HTML snippets
const snippets = {
    btn:     `<div style="text-align:center;">\n    <a href="{{action_url}}" class="btn">Cliquez ici</a>\n</div>`,
    divider: `<div class="divider"></div>`,
};
function insertSnippet(name) {
    const start = editor.selectionStart;
    editor.value = editor.value.substring(0, start) + '\n' + snippets[name] + '\n' + editor.value.substring(start);
    editor.focus();
}

// Preview
function openPreview() {
    const subject = document.querySelector('input[name="subject"]').value;
    const body    = editor.value;

    fetch('{{ route('admin.super.email-templates.preview', $key) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ subject, body }),
    })
    .then(r => r.text())
    .then(html => {
        document.getElementById('preview-subject').textContent = subject;
        document.getElementById('preview-frame').srcdoc = html;
        document.getElementById('preview-modal').classList.remove('hidden');
    });
}
function closePreview() {
    document.getElementById('preview-modal').classList.add('hidden');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closePreview(); });
</script>
@endpush
