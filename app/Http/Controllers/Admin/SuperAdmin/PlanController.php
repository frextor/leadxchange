<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PermissionDefinition;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\ActivityLogger;
use Stripe\StripeClient;

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

        ActivityLogger::log('admin.plan.created', "Plan \"{$validated['label']}\" créé");
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

        ActivityLogger::log('admin.plan.updated', "Plan \"{$plan->label}\" mis à jour");
        return redirect()->route('admin.super.plans.index')
            ->with('success', 'Plan mis à jour.');
    }

    /** All canonical permission definitions grouped by category. */
    public const PERMISSIONS = [
        'Profil membres' => [
            'can_view_member_name'          => ['label' => 'Voir le nom de famille',       'type' => 'bool'],
            'can_view_member_firstname'     => ['label' => 'Voir le prénom',               'type' => 'bool'],
            'can_view_member_photo'         => ['label' => 'Voir la photo',                'type' => 'bool'],
            'can_view_member_region'        => ['label' => 'Voir la région',               'type' => 'bool'],
            'can_view_member_pitch'         => ['label' => 'Voir le pitch',                'type' => 'bool'],
            'can_view_member_video'         => ['label' => 'Voir la vidéo',                'type' => 'bool'],
            'can_view_member_contact'       => ['label' => 'Voir email / téléphone',       'type' => 'bool'],
        ],
        'Connexions' => [
            'can_send_invitations'          => ['label' => 'Envoyer des invitations',      'type' => 'bool'],
            'can_receive_invitations'       => ['label' => 'Recevoir des invitations',     'type' => 'bool'],
        ],
        'Chat / Messages' => [
            'can_send_mail'                 => ['label' => 'Envoyer des messages',         'type' => 'bool'],
            'can_receive_mail'              => ['label' => 'Recevoir des messages',        'type' => 'bool'],
            'can_reply_mail'                => ['label' => 'Répondre aux messages',        'type' => 'bool'],
            'mail_reply_weekly_limit'       => ['label' => 'Limite réponses / semaine',    'type' => 'number', 'null_label' => 'Illimité'],
        ],
        'Leads' => [
            'can_send_leads'                => ['label' => 'Envoyer des leads',            'type' => 'bool'],
            'can_receive_leads'             => ['label' => 'Recevoir des leads',           'type' => 'bool'],
            'can_view_leads'                => ['label' => 'Voir la liste des leads',      'type' => 'bool'],
            'max_leads_per_month'           => ['label' => 'Max leads envoyés / mois',     'type' => 'number', 'null_label' => 'Illimité'],
            'max_received_leads_per_month'  => ['label' => 'Max leads reçus / mois',       'type' => 'number', 'null_label' => 'Illimité'],
            'can_send_mql'                  => ['label' => 'Envoyer leads MQL',            'type' => 'bool'],
            'can_send_sql'                  => ['label' => 'Envoyer leads SQL',            'type' => 'bool'],
            'can_send_sp'                   => ['label' => 'Envoyer leads SP',             'type' => 'bool'],
        ],
        'Groupes / Pôles' => [
            'can_join_pole'                 => ['label' => 'Rejoindre un groupe',          'type' => 'bool'],
            'max_groups_joined'             => ['label' => 'Max groupes rejoints',         'type' => 'number', 'null_label' => 'Illimité'],
            'can_create_pole'               => ['label' => 'Créer un groupe',              'type' => 'bool'],
            'can_invite_to_group'           => ['label' => 'Inviter dans un groupe',       'type' => 'bool'],
            'can_organize_group_events'     => ['label' => 'Organiser événements groupe',  'type' => 'bool'],
        ],
        'Événements' => [
            'can_participate_events'        => ['label' => 'Participer aux événements',    'type' => 'bool'],
            'can_receive_event_invitations' => ['label' => 'Recevoir invitations événements', 'type' => 'bool'],
            'can_create_events'             => ['label' => 'Créer des événements',         'type' => 'bool'],
            'can_organize_regional_events'  => ['label' => 'Organiser événements régionaux', 'type' => 'bool'],
        ],
        'Spécial' => [
            'can_nominate_consul'           => ['label' => 'Nommer un consul',             'type' => 'bool'],
            'can_add_member'                => ['label' => 'Ajouter un membre (Enterprise)', 'type' => 'bool'],
        ],
    ];

    // Flatten for backward compat with upgrade-gate component
    public const FEATURES = [];

    public function permissions(): View
    {
        $plans = Plan::orderBy('sort_order')->get();

        // Override PHP-constant labels with any DB-saved labels
        $dbDefs = PermissionDefinition::orderBy('sort_order')->get()->keyBy('key');
        $permissions = [];
        foreach (self::PERMISSIONS as $category => $perms) {
            foreach ($perms as $key => $def) {
                $db = $dbDefs->get($key);
                $effectiveCategory = $db?->category ?? $category;
                $permissions[$effectiveCategory][$key] = array_merge($def, [
                    'label' => $db?->label ?? $def['label'],
                ]);
            }
        }

        return view('admin.super_admin.plans.permissions', compact('plans', 'permissions'));
    }

    public function permissionLabels(): View
    {
        $grouped = PermissionDefinition::grouped();

        // If table is empty, seed defaults on the fly
        if ($grouped->isEmpty()) {
            (new \Database\Seeders\PermissionDefinitionSeeder())->run();
            $grouped = PermissionDefinition::grouped();
        }

        return view('admin.super_admin.plans.permission-labels', compact('grouped'));
    }

    public function updatePermissionLabels(Request $request): RedirectResponse
    {
        $defs = PermissionDefinition::all();
        foreach ($defs as $def) {
            $label    = $request->input("label_{$def->id}");
            $category = $request->input("category_{$def->id}");
            if ($label !== null) {
                $def->update([
                    'label'    => trim($label) ?: $def->label,
                    'category' => trim($category) ?: $def->category,
                ]);
            }
        }

        return back()->with('success', 'Libellés des permissions mis à jour.');
    }

    public function updatePermissions(Request $request): RedirectResponse
    {
        $plans = Plan::orderBy('sort_order')->get();

        foreach ($plans as $plan) {
            $perms = is_array($plan->permissions) ? $plan->permissions : [];

            foreach (self::PERMISSIONS as $group) {
                foreach ($group as $key => $def) {
                    $fieldKey = "perm_{$plan->id}_{$key}";

                    if ($def['type'] === 'number') {
                        $unlimited = $request->boolean("unlimited_{$plan->id}_{$key}");
                        $raw       = $request->input($fieldKey);
                        $perms[$key] = $unlimited ? null : (($raw === '' || $raw === null) ? null : (int) $raw);
                    } else {
                        $perms[$key] = $request->boolean($fieldKey);
                    }
                }
            }

            $plan->update(['permissions' => $perms]);
        }

        ActivityLogger::log('admin.plan.permissions_updated', 'Permissions des plans mises à jour');
        return back()->with('success', 'Permissions des plans mises à jour.');
    }

    public function toggleStatus(Plan $plan): RedirectResponse
    {
        $plan->update(['is_active' => !$plan->is_active]);
        $label = $plan->is_active ? 'activé' : 'désactivé';
        ActivityLogger::log('admin.plan.toggled', "Plan \"{$plan->label}\" {$label}");
        return back()->with('success', "Plan « {$plan->label} » {$label}.");
    }

    public function toggleVisible(Plan $plan): RedirectResponse
    {
        $plan->update(['is_visible' => !$plan->is_visible]);
        $label = $plan->is_visible ? 'affiché' : 'masqué';
        ActivityLogger::log('admin.plan.visibility_toggled', "Plan \"{$plan->label}\" {$label} sur la page d'accueil");
        return back()->with('success', "Plan « {$plan->label} » {$label} sur la page d'accueil.");
    }

    // ── Stripe integration ────────────────────────────────────────────────────

    private function stripe(): StripeClient
    {
        return new StripeClient(config('services.stripe.secret'));
    }

    public function stripeIndex(): View
    {
        $plans        = Plan::orderBy('sort_order')->get();
        $stripeKey    = config('services.stripe.secret');
        $isConfigured = ! empty($stripeKey);
        $stripeProducts = [];

        if ($isConfigured) {
            try {
                $stripe = $this->stripe();
                // Fetch existing Stripe products to show their status
                foreach ($plans->whereNotNull('stripe_product_id') as $plan) {
                    try {
                        $product = $stripe->products->retrieve($plan->stripe_product_id);
                        $stripeProducts[$plan->id] = [
                            'product' => $product,
                            'price'   => $plan->stripe_price_id
                                ? $stripe->prices->retrieve($plan->stripe_price_id)
                                : null,
                        ];
                    } catch (\Exception) {
                        $stripeProducts[$plan->id] = null;
                    }
                }
            } catch (\Exception $e) {
                $isConfigured = false;
            }
        }

        return view('admin.super_admin.plans.stripe', compact('plans', 'isConfigured', 'stripeProducts'));
    }

    public function stripeSyncPlan(Request $request, Plan $plan): JsonResponse
    {
        if (! config('services.stripe.secret')) {
            return response()->json(['error' => 'Stripe non configuré.'], 400);
        }

        if ((float) $plan->price <= 0) {
            return response()->json(['error' => 'Le plan Basic (gratuit) ne nécessite pas de prix Stripe.'], 400);
        }

        try {
            $stripe   = $this->stripe();
            $currency = config('services.stripe.currency', 'eur');

            // Create or update Stripe Product
            if ($plan->stripe_product_id) {
                $product = $stripe->products->update($plan->stripe_product_id, [
                    'name'        => $plan->label . ' — LeadXchange',
                    'description' => $plan->description ?? '',
                    'active'      => (bool) $plan->is_active,
                ]);
            } else {
                $product = $stripe->products->create([
                    'name'        => $plan->label . ' — LeadXchange',
                    'description' => $plan->description ?? '',
                    'metadata'    => ['plan_id' => (string) $plan->id, 'plan_name' => $plan->name],
                ]);
            }

            // Create new monthly Price (Stripe prices are immutable — always create new)
            $amountInCents = (int) round((float) $plan->price * 100);
            $price = $stripe->prices->create([
                'product'     => $product->id,
                'unit_amount' => $amountInCents,
                'currency'    => $currency,
                'recurring'   => ['interval' => 'month'],
                'metadata'    => ['plan_id' => (string) $plan->id, 'billing' => 'monthly'],
            ]);

            // Archive old monthly price if it changed
            if ($plan->stripe_price_id && $plan->stripe_price_id !== $price->id) {
                try { $stripe->prices->update($plan->stripe_price_id, ['active' => false]); } catch (\Exception) {}
            }

            $updateData = [
                'stripe_product_id' => $product->id,
                'stripe_price_id'   => $price->id,
            ];

            // Create annual price if annual_price is set
            $annualPriceId = null;
            if ($plan->annual_price && (float) $plan->annual_price > 0) {
                $annualAmountInCents = (int) round((float) $plan->annual_price * 100);
                $annualPrice = $stripe->prices->create([
                    'product'     => $product->id,
                    'unit_amount' => $annualAmountInCents,
                    'currency'    => $currency,
                    'recurring'   => ['interval' => 'year'],
                    'metadata'    => ['plan_id' => (string) $plan->id, 'billing' => 'annual'],
                ]);
                if ($plan->stripe_annual_price_id && $plan->stripe_annual_price_id !== $annualPrice->id) {
                    try { $stripe->prices->update($plan->stripe_annual_price_id, ['active' => false]); } catch (\Exception) {}
                }
                $annualPriceId = $annualPrice->id;
                $updateData['stripe_annual_price_id'] = $annualPriceId;
            }

            $plan->update($updateData);

            return response()->json([
                'success'                => true,
                'stripe_product_id'      => $product->id,
                'stripe_price_id'        => $price->id,
                'stripe_annual_price_id' => $annualPriceId,
                'amount'                 => number_format($plan->price, 2) . ' ' . strtoupper($currency),
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function stripeSyncAll(): RedirectResponse
    {
        if (! config('services.stripe.secret')) {
            return back()->with('error', 'Clé Stripe non configurée dans .env.');
        }

        $synced = 0;
        $errors = [];

        foreach (Plan::where('is_active', true)->where('price', '>', 0)->get() as $plan) {
            try {
                $stripe   = $this->stripe();
                $currency = config('services.stripe.currency', 'eur');

                $product = $plan->stripe_product_id
                    ? $stripe->products->update($plan->stripe_product_id, ['name' => $plan->label . ' — LeadXchange'])
                    : $stripe->products->create(['name' => $plan->label . ' — LeadXchange', 'metadata' => ['plan_id' => (string) $plan->id]]);

                $price = $stripe->prices->create([
                    'product'     => $product->id,
                    'unit_amount' => (int) round((float) $plan->price * 100),
                    'currency'    => $currency,
                    'recurring'   => ['interval' => 'month'],
                ]);

                if ($plan->stripe_price_id && $plan->stripe_price_id !== $price->id) {
                    try { $stripe->prices->update($plan->stripe_price_id, ['active' => false]); } catch (\Exception) {}
                }

                $plan->update(['stripe_product_id' => $product->id, 'stripe_price_id' => $price->id]);
                $synced++;
            } catch (\Exception $e) {
                $errors[] = "{$plan->label} : {$e->getMessage()}";
            }
        }

        $msg = "{$synced} plan(s) synchronisé(s) avec Stripe.";
        if ($errors) $msg .= ' Erreurs : ' . implode(', ', $errors);

        return back()->with($errors ? 'error' : 'success', $msg);
    }
}
