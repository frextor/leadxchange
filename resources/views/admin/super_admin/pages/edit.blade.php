@extends('admin.layouts.admin')
@section('title', 'Modifier — ' . $page->title)

@section('content')
<div class="p-6 max-w-5xl mx-auto">

    <div class="mb-5 flex items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.super.pages.index') }}" class="text-sm text-gray-400 hover:text-gray-600 transition">← Pages légales</a>
            <h1 class="text-xl font-bold text-gray-900 mt-1">Modifier : {{ $page->title }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ url('/legal/' . $page->slug) }}" target="_blank"
               class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Voir en ligne
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-5 py-3 text-sm flex items-center gap-2">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-3 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.super.pages.update', $page) }}" id="page-form">
        @csrf @method('PUT')

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

            {{-- Title --}}
            <div class="px-6 py-4 border-b border-gray-100">
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Titre de la page</label>
                <input type="text" name="title" value="{{ old('title', $page->title) }}" required
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                       style="--tw-ring-color:#6366F1;">
            </div>

            {{-- Toolbar --}}
            <div class="px-6 py-3 border-b border-gray-100 bg-gray-50 flex flex-wrap gap-1" id="editor-toolbar">
                <button type="button" onclick="fmt('bold')" title="Gras" class="toolbar-btn font-bold">B</button>
                <button type="button" onclick="fmt('italic')" title="Italique" class="toolbar-btn italic">I</button>
                <button type="button" onclick="wrapTag('h2')" title="Titre H2" class="toolbar-btn text-xs">H2</button>
                <button type="button" onclick="wrapTag('h3')" title="Titre H3" class="toolbar-btn text-xs">H3</button>
                <button type="button" onclick="wrapTag('p')" title="Paragraphe" class="toolbar-btn text-xs">¶</button>
                <div class="w-px bg-gray-200 mx-1 self-stretch"></div>
                <button type="button" onclick="wrapTag('ul', 'li')" title="Liste à puces" class="toolbar-btn text-xs">• Liste</button>
                <button type="button" onclick="wrapTag('ol', 'li')" title="Liste numérotée" class="toolbar-btn text-xs">1. Liste</button>
                <div class="w-px bg-gray-200 mx-1 self-stretch"></div>
                <button type="button" onclick="insertLink()" title="Lien" class="toolbar-btn text-xs">🔗 Lien</button>
                <div class="w-px bg-gray-200 mx-1 self-stretch"></div>
                <button type="button" onclick="togglePreview()" id="preview-btn"
                        class="toolbar-btn text-xs font-semibold ml-auto" style="color:#6366F1;">
                    👁 Prévisualiser
                </button>
            </div>

            {{-- Editor --}}
            <div class="relative">
                <textarea name="content" id="editor" rows="30"
                          class="w-full px-6 py-4 text-sm font-mono border-0 focus:outline-none focus:ring-0 resize-none"
                          style="min-height:500px;line-height:1.7;">{{ old('content', $page->content) }}</textarea>

                {{-- Preview panel (hidden by default) --}}
                <div id="preview-panel" class="hidden absolute inset-0 bg-white px-8 py-6 overflow-auto">
                    <div class="lx-content max-w-3xl mx-auto prose"></div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
                <p class="text-xs text-gray-400 flex items-center gap-1.5">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                    Le contenu supporte le HTML. La modification est publiée immédiatement sur <strong>/legal/{{ $page->slug }}</strong>.
                </p>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.super.pages.index') }}"
                       class="px-4 py-2 rounded-xl text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                        Annuler
                    </a>
                    <button type="submit"
                            class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white hover:opacity-90 transition"
                            style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 7l-11 11-5-5"/></svg>
                        Enregistrer et publier
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.toolbar-btn {
    padding: 4px 10px;
    border-radius: 8px;
    border: 1px solid #E5E7EB;
    background: white;
    font-size: 12px;
    color: #374151;
    cursor: pointer;
    transition: all .15s;
    line-height: 1.6;
}
.toolbar-btn:hover { background:#F9FAFB; border-color:#D1D5DB; }
.toolbar-btn.active { background:#EEF2FF; border-color:#6366F1; color:#4338CA; }

/* Preview typography matching PageController */
#preview-panel .lx-content h2 { font-size:17px;font-weight:700;color:#0F1623;margin:24px 0 10px;padding-top:16px;border-top:1px solid #EEF0F4; }
#preview-panel .lx-content h2:first-child { margin-top:0;padding-top:0;border-top:none; }
#preview-panel .lx-content h3 { font-size:14.5px;font-weight:600;color:#14A98C;margin:16px 0 8px; }
#preview-panel .lx-content p { font-size:14px;color:#2E3850;margin-bottom:14px; }
#preview-panel .lx-content ul, #preview-panel .lx-content ol { padding-left:20px;margin-bottom:14px; }
#preview-panel .lx-content li { font-size:14px;color:#2E3850;margin-bottom:6px; }
#preview-panel .lx-content strong { color:#0F1623;font-weight:600; }
#preview-panel .lx-content a { color:#14A98C; }
#preview-panel .lx-content table { width:100%;border-collapse:collapse;margin:16px 0;font-size:13px; }
#preview-panel .lx-content th { background:#F4F5F8;padding:10px 14px;text-align:left;font-weight:600;border:1px solid #E5E7EE; }
#preview-panel .lx-content td { padding:10px 14px;border:1px solid #E5E7EE; }
</style>

<script>
let previewing = false;

function fmt(cmd) {
    document.execCommand(cmd, false, null);
}

function wrapTag(tag, innerTag) {
    const ta = document.getElementById('editor');
    const start = ta.selectionStart;
    const end   = ta.selectionEnd;
    const sel   = ta.value.substring(start, end);
    let wrapped;
    if (innerTag) {
        const lines = sel ? sel.split('\n').map(l => `  <${innerTag}>${l.trim()}</${innerTag}>`).join('\n') : `  <${innerTag}></${innerTag}>`;
        wrapped = `<${tag}>\n${lines}\n</${tag}>`;
    } else {
        wrapped = `<${tag}>${sel}</${tag}>`;
    }
    ta.setRangeText(wrapped, start, end, 'end');
    ta.focus();
}

function insertLink() {
    const url  = prompt('URL du lien :', 'https://');
    if (!url) return;
    const ta   = document.getElementById('editor');
    const start = ta.selectionStart;
    const end   = ta.selectionEnd;
    const sel   = ta.value.substring(start, end) || 'Lien';
    ta.setRangeText(`<a href="${url}">${sel}</a>`, start, end, 'end');
    ta.focus();
}

function togglePreview() {
    previewing = !previewing;
    const panel = document.getElementById('preview-panel');
    const btn   = document.getElementById('preview-btn');
    if (previewing) {
        panel.querySelector('.lx-content').innerHTML = document.getElementById('editor').value;
        panel.classList.remove('hidden');
        btn.classList.add('active');
        btn.textContent = '✏️ Éditer';
    } else {
        panel.classList.add('hidden');
        btn.classList.remove('active');
        btn.textContent = '👁 Prévisualiser';
    }
}
</script>

@endsection
