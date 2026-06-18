<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::withCount('activeSubscriptions')->orderBy('sort_order')->get();
        $stats = [
            'total'        => $plans->count(),
            'active_plans' => $plans->where('is_active', true)->count(),
            'subscribers'  => $plans->sum('active_subscriptions_count'),
            'mrr'          => $plans->sum(fn($p) => $p->active_subscriptions_count * (float) $p->price),
        ];
        return view('admin.super_admin.plans.index', compact('plans', 'stats'));
    }

    public function create(): View
    {
        return view('admin.super_admin.plans.form', ['plan' => new Plan()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:50', 'unique:plans,name'],
            'label'          => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string', 'max:500'],
            'price'          => ['required', 'numeric', 'min:0'],
            'annual_price'   => ['nullable', 'numeric', 'min:0'],
            'max_leads'      => ['nullable', 'integer', 'min:0'],
            'max_groups'     => ['nullable', 'integer', 'min:0'],
            'initial_points' => ['nullable', 'integer', 'min:0'],
            'sort_order'     => ['required', 'integer', 'min:0'],
            'features'       => ['nullable', 'string'],
        ]);

        Plan::create([
            'name'           => $validated['name'],
            'label'          => $validated['label'],
            'description'    => $validated['description'] ?? null,
            'price'          => $validated['price'],
            'annual_price'   => $validated['annual_price'] ?? null,
            'max_leads'      => $validated['max_leads'] ?? null,
            'max_groups'     => $validated['max_groups'] ?? null,
            'initial_points' => $validated['initial_points'] ?? null,
            'billing_period' => $validated['price'] == 0 ? 'free' : 'monthly',
            'sort_order'     => $validated['sort_order'],
            'features'       => $validated['features'] ? json_decode($validated['features'], true) : [],
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.super.plans.index')
            ->with('success', 'Plan créé avec succès.');
    }

    public function edit(Plan $plan): View
    {
        return view('admin.super_admin.plans.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'label'          => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string', 'max:500'],
            'price'          => ['required', 'numeric', 'min:0'],
            'annual_price'   => ['nullable', 'numeric', 'min:0'],
            'max_leads'      => ['nullable', 'integer', 'min:0'],
            'max_groups'     => ['nullable', 'integer', 'min:0'],
            'initial_points' => ['nullable', 'integer', 'min:0'],
            'sort_order'     => ['required', 'integer', 'min:0'],
            'features'       => ['nullable', 'string'],
        ]);

        $plan->update([
            'label'          => $validated['label'],
            'description'    => $validated['description'] ?? null,
            'price'          => $validated['price'],
            'annual_price'   => $validated['annual_price'] ?? null,
            'max_leads'      => $validated['max_leads'] ?? null,
            'max_groups'     => $validated['max_groups'] ?? null,
            'initial_points' => $validated['initial_points'] ?? null,
            'billing_period' => $validated['price'] == 0 ? 'free' : 'monthly',
            'sort_order'     => $validated['sort_order'],
            'features'       => $validated['features'] ? json_decode($validated['features'], true) : $plan->features,
            'is_active'      => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.super.plans.index')
            ->with('success', 'Plan mis à jour.');
    }

    /** All canonical feature definitions used across plans. */
    public const FEATURES = [
        // -- Connexions ---------------------------------------------------------
        'max_connections_per_month' => ['label' => 'Connexions / mois',              'type' => 'number'],
        'view_profile_info'         => ['label' => 'Voir les infos profil',           'type' => 'bool'],
        'advanced_search'           => ['label' => 'Recherche avancée',               'type' => 'bool'],
        // -- Leads --------------------------------------------------------------
        'send_leads'                => ['label' => 'Envoyer des leads',               'type' => 'bool'],
        // -- Groupes ------------------------------------------------------------
        'join_groups'               => ['label' => 'Rejoindre des groupes',           'type' => 'bool'],
        'create_groups'             => ['label' => 'Créer des groupes',               'type' => 'bool'],
        // -- Événements ---------------------------------------------------------
        'attend_events'             => ['label' => 'Participer aux événements',       'type' => 'bool'],
        'create_events'             => ['label' => 'Créer des événements',            'type' => 'bool'],
        // -- Profil & extras ----------------------------------------------------
        'chat'                      => ['label' => 'Messagerie',                      'type' => 'bool'],
        'profile_video'             => ['label' => 'Vidéo de présentation',           'type' => 'bool'],
        'priority_support'          => ['label' => 'Support prioritaire',             'type' => 'bool'],
        'requires_approval'         => ['label' => 'Sur approbation',                 'type' => 'bool'],
    ];

    public function permissions(): View
    {
        $plans = Plan::orderBy('sort_order')->get();
        $features = self::FEATURES;
        return view('admin.super_admin.plans.permissions', compact('plans', 'features'));
    }

    public function updatePermissions(Request $request): RedirectResponse
    {
        $plans = Plan::orderBy('sort_order')->get();

        foreach ($plans as $plan) {
            $features = [];
            foreach (self::FEATURES as $key => $def) {
                $fieldKey = "features_{$plan->id}_{$key}";

                if ($def['type'] === 'number') {
                    $raw = $request->input($fieldKey);
                    // empty string / "unlimited" checkbox → null, numeric → int
                    $features[$key] = ($raw === '' || $raw === null) ? null : (int) $raw;
                } else {
                    // checkbox: present = true, absent = false
                    $features[$key] = $request->boolean($fieldKey);
                }
            }
            $plan->update(['features' => $features]);
        }

        return back()->with('success', 'Permissions des plans mises à jour.');
    }

    public function toggleStatus(Plan $plan): RedirectResponse
    {
        $plan->update(['is_active' => !$plan->is_active]);
        $label = $plan->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Plan « {$plan->label} » {$label}.");
    }
}
