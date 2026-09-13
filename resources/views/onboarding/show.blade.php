{{-- resources/views/onboarding/show.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bienvenue — LeadXchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background:#F9FAFB; }
        [x-cloak] { display: none !important; }

        .ob-bg-ellipse {
            position: fixed; top: -210px; left: -109px;
            width: 440px; height: 449px; pointer-events: none; z-index: 0;
        }
        .ob-shell {
            position: relative; z-index: 1;
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 40px 20px;
        }
        .ob-card {
            width: 100%; max-width: 900px; min-height: 588px;
            background: #fff; border: 1px solid #E5E5E5; border-radius: 12px;
            display: flex; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,.06);
        }
        .ob-left { flex: 1 1 0; display: flex; flex-direction: column; justify-content: space-between; padding: 32px; min-width: 0; }
        .ob-right {
            flex: 1 1 0; background: #F5F5F5; display: none;
            align-items: center; justify-content: center; padding: 20px;
            position: relative;
        }
        @media (min-width: 860px) { .ob-right { display: flex; } }
        .ob-right-photo {
            position: absolute; inset: 20px; border-radius: 18px; overflow: hidden;
            background-image: url('{{ asset('images/auth/trust-photo.png') }}');
            background-size: cover; background-position: center;
        }
        .ob-right-photo::after {
            content: ''; position: absolute; inset: 0; background: rgba(60,85,253,.75);
        }
        .ob-right-logo {
            position: relative; z-index: 1; color: #fff; font-size: 26px; font-weight: 600;
            display: flex; align-items: center; gap: 8px;
        }

        .lx-input {
            width: 100%; background: #fff; border: 1px solid #E5E5E5; border-radius: 8px;
            font-size: 14px; padding: 8px 12px; height: 36px; outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .lx-input:focus { border-color: transparent; box-shadow: 0 0 0 2px #3C55FD; }
        textarea.lx-input { height: auto; padding: 10px 12px; }
        .lx-label { display:block; font-size: 13px; font-weight: 500; color:#525252; margin-bottom: 6px; }
        .lx-multiselect { min-height: 88px; }

        .ob-step-title { font-size: 24px; font-weight: 600; color: #0A0A0A; margin-bottom: 4px; }
        .ob-step-sub { font-size: 14px; color: #737373; }

        .ob-btn { height: 36px; padding: 0 16px; border-radius: 8px; font-size: 14px; font-weight: 500; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; border:none; }
        .ob-btn-ghost { background:#F5F5F5; color:#0A0A0A; }
        .ob-btn-ghost:hover { background:#EDEDED; }
        .ob-btn-primary { background:#3C55FD; color:#fff; }
        .ob-btn-primary:hover { background:#2F44E0; }
        .ob-btn-primary:disabled { opacity:.5; cursor:not-allowed; }
    </style>
</head>
<body>

<img src="{{ asset('images/auth/bg-ellipse.svg') }}" alt="" class="ob-bg-ellipse" aria-hidden="true">

<div class="ob-shell" x-data="onboarding()">

    <form action="{{ route('onboarding.update') }}" method="POST" enctype="multipart/form-data" class="ob-card" @submit="submitting = true">
        @csrf

        {{-- ─────────── LEFT — steps ─────────── --}}
        <div class="ob-left">

            {{-- Step 1 — Bienvenue / Photo --}}
            <div x-show="step === 1" x-cloak>
                <div class="mb-7">
                    <p class="ob-step-title">Bonjour {{ $user->first_name }},<br>Bienvenue sur LeadXchange</p>
                    <p class="ob-step-sub">Configurons votre profil en quelques étapes.</p>
                </div>

                <div class="flex flex-col items-center gap-2">
                    <label class="text-sm text-gray-500 self-start">Ajoutez une photo pour que les autres membres puissent vous reconnaître</label>
                    <div class="relative w-[100px] h-[100px] mt-2">
                        <div class="w-[100px] h-[100px] rounded-full overflow-hidden flex items-center justify-center"
                             style="background:#3C55FD;">
                            <template x-if="!avatarPreview">
                                <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
                            </template>
                            <img x-show="avatarPreview" :src="avatarPreview" x-cloak class="w-full h-full object-cover">
                        </div>
                        <label class="absolute bottom-0 right-0 w-[30px] h-[30px] rounded-full bg-white border border-gray-200 shadow flex items-center justify-center cursor-pointer hover:bg-gray-50">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0A0A0A" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2Z"/><circle cx="12" cy="13" r="4"/></svg>
                            <input type="file" name="avatar" accept="image/*" class="hidden" @change="previewAvatar($event)">
                        </label>
                    </div>
                    <p class="text-xs font-medium mt-1">{{ $user->first_name }} {{ $user->last_name }}</p>
                </div>
            </div>

            {{-- Step 2 — Entreprise --}}
            <div x-show="step === 2" x-cloak>
                <div class="mb-7">
                    <p class="ob-step-title">Votre Entreprise</p>
                    <p class="ob-step-sub">Rattachez-vous à votre société pour échanger des leads en confiance.</p>
                </div>

                <div class="flex flex-col gap-3">
                    <div class="relative">
                        <label class="lx-label">Rechercher votre entreprise</label>
                        <input type="text" class="lx-input" placeholder="Nom de l'entreprise…"
                               x-model="companySearch" @input.debounce.300ms="searchCompany()">
                        <input type="hidden" name="company_id" x-model="companyId">
                        <div x-show="companyResults.length" x-cloak
                             class="absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10 max-h-40 overflow-y-auto">
                            <template x-for="c in companyResults" :key="c.id">
                                <button type="button" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50"
                                        @click="selectCompany(c)" x-text="c.name"></button>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-xs text-gray-400">
                        <span class="flex-1 h-px bg-gray-200"></span> OU <span class="flex-1 h-px bg-gray-200"></span>
                    </div>

                    <div>
                        <label class="lx-label">Le nom de votre entreprise n'existe pas encore ?</label>
                        <input type="text" name="company_name" x-model="companyName" class="lx-input" placeholder="Nom de l'entreprise">
                    </div>
                </div>
            </div>

            {{-- Step 3 — Préférences --}}
            <div x-show="step === 3" x-cloak>
                <div class="mb-6">
                    <p class="ob-step-title">Vos préférences</p>
                    <p class="ob-step-sub">Indiquez ce que vous cherchez et ce que vous pouvez offrir.</p>
                </div>

                <div class="flex flex-col gap-4 max-h-[360px] overflow-y-auto pr-1">
                    <div>
                        <label class="lx-label">Où opérez-vous ?</label>
                        <select name="region_id" class="lx-input">
                            <option value="">— Région d'activité —</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}" {{ $user->region_id == $region->id ? 'selected' : '' }}>{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="lx-label">Dans quels secteurs recherchez vous des leads ?</label>
                        <select name="looking_for[]" multiple class="lx-input lx-multiselect">
                            @foreach($sectors as $sector)
                                <option value="{{ $sector->id }}" {{ in_array($sector->id, $user->profile?->looking_for ?? []) ? 'selected' : '' }}>{{ $sector->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="lx-label">Dans quels secteurs pouvez vous fournir vous des leads ?</label>
                        <select name="services_offered[]" multiple class="lx-input lx-multiselect">
                            @foreach($sectors as $sector)
                                <option value="{{ $sector->id }}" {{ in_array($sector->id, $user->profile?->services_offered ?? []) ? 'selected' : '' }}>{{ $sector->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="lx-label">Marché visé #1</label>
                            <select name="market_addressed_id" class="lx-input">
                                <option value="">—</option>
                                @foreach($markets as $market)
                                    <option value="{{ $market->id }}" {{ $user->profile?->market_addressed_id == $market->id ? 'selected' : '' }}>{{ $market->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="lx-label">Marché visé #2</label>
                            <select name="market_target_id" class="lx-input">
                                <option value="">—</option>
                                @foreach($markets as $market)
                                    <option value="{{ $market->id }}" {{ $user->profile?->market_target_id == $market->id ? 'selected' : '' }}>{{ $market->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 4 — Présentation --}}
            <div x-show="step === 4" x-cloak>
                <div class="mb-6">
                    <p class="ob-step-title">Votre présentation</p>
                    <p class="ob-step-sub">Aidez les autres à mieux vous connaître.</p>
                </div>

                <div class="flex flex-col gap-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="lx-label mb-0">Comment vous présentez-vous ?</label>
                            <span class="text-xs text-gray-400" x-text="bio.length + '/500'"></span>
                        </div>
                        <textarea name="bio" x-model="bio" maxlength="500" rows="3" class="lx-input" placeholder="Bio / description (500 caractères max)">{{ $user->profile?->bio }}</textarea>
                    </div>

                    <div>
                        <label class="lx-label">Votre profil LinkedIn ?</label>
                        <input type="url" name="linkedin" class="lx-input" placeholder="URL du profil LinkedIn (optionnel)" value="{{ $user->profile?->linkedin }}">
                    </div>

                    <div>
                        <label class="lx-label">Une vidéo pour vous présenter ?</label>
                        <p class="text-xs text-gray-400 mb-2">1 min max. Vérifiée avant affichage. (optionnel)</p>
                        <label class="flex items-center justify-center gap-2 h-[72px] border border-dashed border-gray-300 rounded-lg cursor-pointer text-sm font-medium hover:bg-gray-50" style="color:#3C55FD;">
                            <span>+ <span x-text="videoName || 'Ajouter une vidéo de présentation'"></span></span>
                            <input type="file" name="presentation_video" accept="video/*" class="hidden" @change="videoName = $event.target.files[0]?.name">
                        </label>
                    </div>
                </div>
            </div>

            {{-- Footer nav --}}
            <div class="flex items-center justify-between mt-6 pt-4">
                <span class="text-sm text-gray-500" x-text="step + '/4'"></span>
                <div class="flex items-center gap-3">
                    <button type="button" class="ob-btn ob-btn-ghost" x-show="step > 1" x-cloak @click="step--">Précédent</button>
                    <button type="button" class="ob-btn ob-btn-ghost" @click="skip()">Passer</button>
                    <button type="button" class="ob-btn ob-btn-primary" x-show="step < 4" x-cloak @click="step++">Suivant</button>
                    <button type="submit" class="ob-btn ob-btn-primary" x-show="step === 4" x-cloak :disabled="submitting">
                        <span x-text="submitting ? 'Enregistrement…' : 'Terminer'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ─────────── RIGHT — visual panel ─────────── --}}
        <div class="ob-right">
            <div class="ob-right-photo"></div>
            <div class="ob-right-logo">
                <svg width="24" height="24" viewBox="0 0 14 14" fill="none"><path d="M3.6 10.84c0-.19-.15-.34-.34-.34H.34C.15 10.5 0 10.35 0 10.16V7.34C0 7.15.15 7 .34 7h6.32c.19 0 .34.15.34.34v6.32c0 .19-.15.34-.34.34H3.95c-.19 0-.34-.15-.34-.34v-2.83Z" fill="#fff"/><path d="M10.39 3.16c0 .19.15.34.34.34h2.93c.19 0 .34.15.34.34v2.83c0 .19-.15.34-.34.34H7.34C7.15 7 7 6.85 7 6.66V.34C7 .15 7.15 0 7.34 0h2.72c.19 0 .34.15.34.34v2.83Z" fill="#fff"/></svg>
                LeadXchange
            </div>
        </div>
    </form>
</div>

<script>
function onboarding() {
    return {
        step: 1,
        submitting: false,
        avatarPreview: null,
        bio: {{ Js::from($user->profile?->bio ?? '') }},
        videoName: null,
        companySearch: {{ Js::from($user->company?->name ?? '') }},
        companyName: '',
        companyId: {{ $user->company_id ?? 'null' }},
        companyResults: [],

        previewAvatar(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.avatarPreview = URL.createObjectURL(file);
        },

        searchCompany() {
            if (this.companySearch.length < 3) { this.companyResults = []; return; }
            fetch('{{ route('company.search') }}?q=' + encodeURIComponent(this.companySearch))
                .then(r => r.json())
                .then(data => { this.companyResults = data; })
                .catch(() => { this.companyResults = []; });
        },

        selectCompany(c) {
            this.companyId = c.id;
            this.companySearch = c.name;
            this.companyResults = [];
        },

        skip() {
            if (this.step < 4) { this.step++; return; }
            if (confirm('Terminer l\'assistant sans compléter cette dernière étape ?')) {
                fetch('{{ route('onboarding.skip') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                }).then(() => window.location.href = '{{ route('dashboard') }}');
            }
        },
    };
}
</script>

</body>
</html>
