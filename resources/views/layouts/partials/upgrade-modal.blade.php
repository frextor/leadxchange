    @php
        // Build a JS-accessible map: feature key → {label, plan_label, plan_price}
        $lxPermsMap = [];
        $allPlans = \App\Models\Plan::where('is_active', true)->orderBy('price')->get();
        foreach (\App\Http\Controllers\Admin\SuperAdmin\PlanController::PERMISSIONS as $pKey => $pDef) {
            $label = \App\Models\PermissionDefinition::labelFor($pKey)
                   ?? ucfirst(str_replace('_', ' ', $pKey));
            $upgradePlan = $allPlans->first(function ($p) use ($pKey) {
                $perms = is_array($p->permissions) ? $p->permissions : [];
                if (! array_key_exists($pKey, $perms)) return false;
                $val = $perms[$pKey];
                return $val === true || (is_int($val) && $val > 0);
            });
            $lxPermsMap[$pKey] = [
                'label'      => $label,
                'plan_label' => $upgradePlan?->label ?? null,
                'plan_price' => $upgradePlan ? ($upgradePlan->price > 0 ? number_format($upgradePlan->price, 2, ',', ' ') . ' €/mois' : 'Basic') : null,
            ];
        }
    @endphp

    {{-- ── Global Upgrade Modal ──────────────────────────────────── --}}
    <div id="lx-upgrade-modal"
         style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:16px;">
        <div id="lx-upgrade-box"
             style="background:#fff;border-radius:24px;max-width:420px;width:100%;box-shadow:0 25px 60px rgba(0,0,0,.18);overflow:hidden;transform:scale(.95);opacity:0;transition:transform .2s ease,opacity .2s ease;">

            {{-- Header gradient --}}
            <div style="background:linear-gradient(135deg,#6366F1,#4338CA);padding:28px 28px 20px;text-align:center;">
                <div style="width:52px;height:52px;border-radius:16px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <h2 style="color:#fff;font-size:17px;font-weight:700;margin:0 0 4px;">Fonctionnalité Premium</h2>
                <p id="lx-upgrade-label" style="color:rgba(255,255,255,.75);font-size:13px;margin:0;"></p>
            </div>

            {{-- Body --}}
            <div style="padding:24px 28px;">
                <p id="lx-upgrade-desc" style="color:#475569;font-size:14px;line-height:1.6;margin:0 0 16px;text-align:center;"></p>

                <div id="lx-upgrade-plan-wrap" style="display:none;background:linear-gradient(135deg,#EEF2FF,#E0E7FF);border-radius:14px;padding:14px 18px;margin-bottom:20px;text-align:center;">
                    <p style="font-size:11px;color:#6366F1;font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin:0 0 4px;">Disponible à partir du plan</p>
                    <p id="lx-upgrade-plan-name" style="font-size:15px;font-weight:700;color:#4338CA;margin:0 0 2px;"></p>
                    <p id="lx-upgrade-plan-price" style="font-size:12px;color:#6366F1;margin:0;"></p>
                </div>

                <div style="display:flex;flex-direction:column;gap:10px;">
                    <a href="{{ route('upgrade') }}"
                       style="display:flex;align-items:center;justify-content:center;gap:8px;padding:13px 20px;border-radius:14px;background:linear-gradient(135deg,#6366F1,#4338CA);color:#fff;font-size:14px;font-weight:600;text-decoration:none;transition:opacity .15s;"
                       onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        Voir les plans & upgrader
                    </a>
                    <button onclick="lxCloseUpgradeModal()"
                            style="padding:11px 20px;border-radius:14px;border:1.5px solid #E2E8F0;background:transparent;color:#64748B;font-size:13px;font-weight:500;cursor:pointer;transition:background .15s;"
                            onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                        Continuer avec mon plan actuel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.LX_PERMS = @json($lxPermsMap);

        window.openUpgradeModal = function(feature) {
            const perm  = window.LX_PERMS[feature] || {};
            const label = perm.label || feature.replace(/_/g,' ');
            const modal = document.getElementById('lx-upgrade-modal');
            const box   = document.getElementById('lx-upgrade-box');

            document.getElementById('lx-upgrade-label').textContent = '« ' + label + ' »';
            document.getElementById('lx-upgrade-desc').textContent  =
                'Cette fonctionnalité n\'est pas incluse dans votre plan actuel. Passez à un plan supérieur pour y accéder.';

            const planWrap = document.getElementById('lx-upgrade-plan-wrap');
            if (perm.plan_label) {
                document.getElementById('lx-upgrade-plan-name').textContent  = perm.plan_label;
                document.getElementById('lx-upgrade-plan-price').textContent = perm.plan_price || '';
                planWrap.style.display = 'block';
            } else {
                planWrap.style.display = 'none';
            }

            modal.style.display = 'flex';
            requestAnimationFrame(() => {
                box.style.transform = 'scale(1)';
                box.style.opacity   = '1';
            });

            document.addEventListener('keydown', lxUpgradeKeydown);
        };

        window.lxCloseUpgradeModal = function() {
            const modal = document.getElementById('lx-upgrade-modal');
            const box   = document.getElementById('lx-upgrade-box');
            box.style.transform = 'scale(.95)';
            box.style.opacity   = '0';
            setTimeout(() => modal.style.display = 'none', 200);
            document.removeEventListener('keydown', lxUpgradeKeydown);
        };

        function lxUpgradeKeydown(e) {
            if (e.key === 'Escape') lxCloseUpgradeModal();
        }

        document.getElementById('lx-upgrade-modal').addEventListener('click', function(e) {
            if (e.target === this) lxCloseUpgradeModal();
        });

        @if(session('upgrade_feature'))
        window.addEventListener('DOMContentLoaded', function() {
            openUpgradeModal('{{ session("upgrade_feature") }}');
        });
        @endif
    </script>
